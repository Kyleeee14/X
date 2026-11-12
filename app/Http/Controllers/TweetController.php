<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tweet;
use App\Models\Like;
use App\Models\Reply;
use App\Models\ReplyLike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class TweetController extends Controller
{
    public function index(Request $request)
    {
        $tweets = Tweet::with(['user', 'likes', 'originalTweet.user', 'quotedTweet.user', 'quotes.user', 'quotes.likes', 'quotes.replies.user', 'quotes.replies.likes'])
            ->whereNull('quote_tweet_id')
            ->whereNull('original_tweet_id')
            ->when($request->query('feed') === 'following', function ($query) {
                $query->whereIn('user_id', \App\Models\Follow::where('follower_id', auth()->id())->select('following_id'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();
        
        return view('tweets.index', compact('tweets'));
    }

    public function comments(Tweet $tweet)
    {
        // Load tweet with all comments and unlimited nested replies
        $tweet->load([
            'user',
            'likes',
            'quotes' => function($query) {
                $query->whereNull('parent_comment_id')
                      ->with(['user', 'likes', 'replies' => function($query) {
                          $query->with(['user', 'likes', 'replies' => function($query) {
                              $query->with(['user', 'likes'])
                                    ->orderBy('created_at', 'asc');
                          }])
                                ->orderBy('created_at', 'asc');
                      }])
                      ->orderBy('created_at', 'asc');
            }
        ]);

        return view('tweets.comments', compact('tweet'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => ['nullable', 'string', 'max:280', 'required_without:media'],
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:4096',
            'quote_tweet_id' => 'nullable|exists:tweets,id',
            'parent_comment_id' => 'nullable|exists:tweets,id',
            'media' => ['nullable', 'array', 'max:8'],
            'media.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm', 'max:25600'],
        ]);

        $content = trim((string) $request->input('content', ''));
        $mediaSignature = collect($request->file('media', []))
            ->map(fn ($file) => [$file->getClientOriginalName(), $file->getSize()])
            ->values()
            ->all();
        $submissionKey = 'tweet-submit:'.hash('sha256', json_encode([
            Auth::id(),
            $content,
            $request->input('quote_tweet_id'),
            $request->input('parent_comment_id'),
            $mediaSignature,
        ]));

        if (! Cache::add($submissionKey, true, now()->addSeconds(10))) {
            throw ValidationException::withMessages([
                'content' => 'Postingan yang sama baru saja dikirim. Tunggu sebentar sebelum mencoba lagi.',
            ]);
        }

        if ($request->filled('parent_comment_id')) {
            $parent = Tweet::findOrFail($request->parent_comment_id);
            abort_unless($request->filled('quote_tweet_id') && (int) $parent->quote_tweet_id === (int) $request->quote_tweet_id, 422, 'Reply tidak sesuai dengan percakapan.');
        }

        // Handle different types of posts
        if ($request->has('parent_comment_id') && $request->parent_comment_id) {
            // This is a reply to a comment
            $tweet = Tweet::create([
                'user_id' => Auth::id(),
                'content' => $content,
                'quote_tweet_id' => $request->quote_tweet_id, // Original tweet ID
                'parent_comment_id' => $request->parent_comment_id // Parent comment ID
            ]);
        } elseif ($request->has('quote_tweet_id') && $request->quote_tweet_id) {
            // This is a comment on a tweet
            $tweet = Tweet::create([
                'user_id' => Auth::id(),
                'content' => $content,
                'quote_tweet_id' => $request->quote_tweet_id,
                'parent_comment_id' => null
            ]);
        } else {
            // This is a normal Tweet
            $tweet = Tweet::create([
                'user_id' => Auth::id(),
                'content' => $content,
                'quote_tweet_id' => null,
                'parent_comment_id' => null
            ]);
        }

        if ($request->hasFile('media')) {
            $media = [];
            foreach ($request->file('media') as $file) {
                $name = (string) \Illuminate\Support\Str::uuid().'.'.$file->extension();
                $type = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
                $file->move(public_path('images'), $name);
                $media[] = ['url' => '/images/'.$name, 'type' => $type];
            }
            $tweet->media = $media;
            $tweet->save();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $tweet->image = '/images/' . $imageName;
            $tweet->save();
        }

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            // Load relationships for response
            $tweet->load(['user', 'likes']);
            
            // Load relevant tags based on tweet type
            if ($tweet->isTopLevelComment()) {
                $tweet->load(['commentTags']);
            } else {
                $tweet->load(['tags']);
            }
            
            // Add likes_count from the tweet itself
            $tweetData = $tweet->toArray();
            $tweetData['likes_count'] = $tweet->likes_count;
            $tweetData['content_with_tags'] = $tweet->getContentWithParsedTags();
            
            // Ensure user data and avatar_url are included
            if ($tweet->user) {
                $tweetData['user']['avatar_url'] = $tweet->user->avatar_url;
                $tweetData['user']['name'] = $tweet->user->name;
                $tweetData['user']['username'] = $tweet->user->username;
            }
            
            // Debug logging
            \Log::info('TweetController AJAX Response:', [
                'tweet_id' => $tweet->id,
                'user_data' => $tweet->user,
                'user_name' => $tweet->user->name ?? 'NULL',
                'user_username' => $tweet->user->username ?? 'NULL',
                'user_avatar' => $tweet->user->avatar_url ?? 'NULL',
                'full_response' => $tweetData
            ]);
            
            return response()->json([
                'success' => true,
                'comment' => $tweetData,
                'message' => $request->has('parent_comment_id') ? 'Reply posted successfully!' : 'Comment posted successfully!'
            ]);
        }

        // Redirect based on context
        if ($request->has('quote_tweet_id') && $request->quote_tweet_id) {
            return redirect()->route('tweets.comments', $request->quote_tweet_id)->with('success', 'Comment posted successfully!');
        } elseif ($request->has('parent_comment_id') && $request->parent_comment_id) {
            $parentComment = Tweet::find($request->parent_comment_id);
            if ($parentComment && $parentComment->quote_tweet_id) {
                return redirect()->route('tweets.comments', $parentComment->quote_tweet_id)->with('success', 'Reply posted successfully!');
            }
        }

        return redirect()->back()->with('success', 'Tweet posted successfully!');
    }

    public function update(Request $request, Tweet $tweet)
    {
        // Authorization: Only owner can edit
        if ($tweet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'content' => 'required|max:280',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'remove_image' => 'nullable|boolean'
        ]);

        // Update content
        $tweet->content = $request->content;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $tweet->image = '/images/' . $imageName;
        } elseif ($request->has('remove_image') && $request->remove_image == '1') {
            // Remove existing image
            $tweet->image = null;
        }

        $tweet->save();

        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
            // Load relationships for response
            $tweet->load(['user', 'likes']);
            
            // Add likes_count from tweet itself
            $tweetData = $tweet->toArray();
            $tweetData['likes_count'] = $tweet->likes_count;
            
            // Ensure user data and avatar_url are included
            if ($tweet->user) {
                $tweetData['user']['avatar_url'] = $tweet->user->avatar_url;
                $tweetData['user']['name'] = $tweet->user->name;
                $tweetData['user']['username'] = $tweet->user->username;
            }
            
            return response()->json([
                'success' => true,
                'content' => $tweet->content,
                'image' => $tweet->image,
                'message' => 'Comment updated successfully!'
            ]);
        }

        // Redirect back
        if ($tweet->quote_tweet_id) {
            return redirect()->route('tweets.comments', $tweet->quote_tweet_id)->with('success', 'Comment updated successfully!');
        }

        return redirect()->back()->with('success', 'Comment updated successfully!');
    }

    public function like(Tweet $tweet)
    {
        $existingLike = Like::where('user_id', Auth::id())
                            ->where('tweet_id', $tweet->id)
                            ->first();

        $isLiked = false;
        if ($existingLike) {
            Like::where('user_id', Auth::id())
               ->where('tweet_id', $tweet->id)
               ->delete();
            $tweet->decrement('likes_count');
        } else {
            Like::create([
                'user_id' => Auth::id(),
                'tweet_id' => $tweet->id
            ]);
            $tweet->increment('likes_count');
            $isLiked = true;
        }

        // Check if this is an AJAX request
        if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'liked' => $isLiked,
                'likes_count' => $tweet->fresh()->likes_count
            ]);
        }

        // Redirect to comment page if this is a comment
        if ($tweet->quote_tweet_id) {
            return redirect()->route('tweets.comments', $tweet->quote_tweet_id);
        }

        return redirect()->back();
    }

    public function likeReply(Tweet $reply)
    {
        $existingLike = ReplyLike::where('user_id', Auth::id())
                              ->where('reply_id', $reply->id)
                              ->first();

        if ($existingLike) {
            ReplyLike::where('user_id', Auth::id())
                     ->where('reply_id', $reply->id)
                     ->delete();
            $reply->decrement('likes_count');
        } else {
            ReplyLike::create([
                'user_id' => Auth::id(),
                'reply_id' => $reply->id
            ]);
            $reply->increment('likes_count');
        }

        return redirect()->back();
    }

    public function destroy(Tweet $tweet)
    {
        // Check if user owns this tweet
        if ($tweet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $tweet->replies()->get()->each->delete();
        $tweet->delete();

        // Redirect back
        return redirect()->back()->with('success', 'Tweet deleted successfully!');
    }
}
