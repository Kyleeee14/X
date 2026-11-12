<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Tweet;

class TagController extends Controller
{
    public function index()
    {
        $trendingTags = Tag::trending()->get();
        $popularTags = Tag::popular()->limit(20)->get();
        
        return view('tags.index', compact('trendingTags', 'popularTags'));
    }

    public function show(Tag $tag)
    {
        // Get all content with this tag
        $tweets = $tag->tweets()->with(['user', 'likes'])->latest()->paginate(10);
        $comments = $tag->comments()->with(['user', 'quotedTweet'])->latest()->paginate(10);
        $replies = $tag->replies()->with(['user', 'parentComment'])->latest()->paginate(10);
        
        return view('tags.show', compact('tag', 'tweets', 'comments', 'replies'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return view('search', [
                'tweets' => collect(),
                'comments' => collect(),
                'tags' => collect(),
                'query' => ''
            ]);
        }

        // Search tweets by content
        $tweets = Tweet::where('content', 'like', "%{$query}%")
            ->whereNull('original_tweet_id') // Exclude retweets
            ->whereNull('quote_tweet_id') // Exclude quotes/comments
            ->whereNull('parent_comment_id') // Exclude replies
            ->with(['user', 'tags'])
            ->latest()
            ->paginate(10);

        // Search comments by content (include replies to tweets)
        $comments = Tweet::where('content', 'like', "%{$query}%")
            ->whereNotNull('quote_tweet_id') // Only comments (replies to tweets)
            ->whereNull('parent_comment_id') // Exclude nested replies
            ->with(['user', 'quotedTweet.user', 'likes'])
            ->latest()
            ->paginate(10);

        // Search tags by name
        $tags = Tag::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->popular()
            ->limit(5)
            ->get();

        return view('search', [
            'tweets' => $tweets,
            'comments' => $comments,
            'tags' => $tags,
            'query' => $query
        ]);
    }

    public function searchPage(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return view('search', [
                'tweets' => collect(),
                'comments' => collect(),
                'tags' => collect()
            ]);
        }

        // Search tweets by content (with pagination)
        $tweets = Tweet::where('content', 'like', "%{$query}%")
            ->whereNull('original_tweet_id') // Exclude retweets
            ->whereNull('quote_tweet_id') // Exclude quotes/comments
            ->whereNull('parent_comment_id') // Exclude replies
            ->with(['user', 'tags', 'likes'])
            ->latest()
            ->paginate(20);

        // Search comments by content (include replies to tweets)
        $comments = Tweet::where('content', 'like', "%{$query}%")
            ->whereNotNull('quote_tweet_id') // Only comments (replies to tweets)
            ->whereNull('parent_comment_id') // Exclude nested replies
            ->with(['user', 'quotedTweet.user', 'likes'])
            ->latest()
            ->paginate(20);

        // Search tags by name
        $tags = Tag::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->popular()
            ->limit(20)
            ->get();

        return view('search', compact('tweets', 'comments', 'tags'));
    }

    public function autocomplete(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }

        $tags = Tag::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orderBy('usage_count', 'desc')
            ->limit(8)
            ->get();

        return response()->json($tags->map(function ($tag) {
            return [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'usage_count' => $tag->usage_count,
                'display_name' => "#{$tag->name} ({$tag->usage_count})"
            ];
        }));
    }

    public function trending()
    {
        $trendingTags = Tag::trending()->get();
        
        return response()->json($trendingTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'usage_count' => $tag->usage_count,
                'url' => route('tags.show', $tag->slug)
            ];
        }));
    }

    public function popular()
    {
        $popularTags = Tag::popular()->limit(15)->get();
        
        return response()->json($popularTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'usage_count' => $tag->usage_count,
                'url' => route('tags.show', $tag->slug)
            ];
        }));
    }
}
