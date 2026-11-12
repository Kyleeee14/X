<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tweet extends Model
{
    protected $casts = ['media' => 'array'];

    protected $fillable = [
        'media',
        'user_id',
        'content',
        'image',
        'original_tweet_id',
        'quote_tweet_id',
        'parent_comment_id',
        'likes_count',
        'retweets_count',
        'replies_count'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originalTweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class, 'original_tweet_id');
    }

    public function retweets(): HasMany
    {
        return $this->hasMany(Tweet::class, 'original_tweet_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Tweet::class, 'parent_comment_id')->orderBy('created_at', 'asc');
    }

    public function quotedTweet(): BelongsTo
    {
        return $this->belongsTo(Tweet::class, 'quote_tweet_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Tweet::class, 'quote_tweet_id');
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(Tweet::class, 'parent_comment_id');
    }

    // Tag relationships
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tweet_tags');
    }

    public function commentTags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'comment_tags');
    }

    public function replyTags(): BelongsToMany
    {
        // Nested replies are Tweet records; comment_tags references tweets.
        return $this->belongsToMany(Tag::class, 'comment_tags');
    }

    // Tag parsing and management
    public function extractTags(): array
    {
        preg_match_all('/#(\w+)/', $this->content, $matches);
        return array_unique($matches[1]);
    }

    public function syncTags()
    {
        $tags = $this->extractTags();
        
        if ($this->isTopLevelComment()) {
            $this->commentTags()->sync([]);
            foreach ($tags as $tagName) {
                $tag = Tag::findOrCreateByName($tagName);
                $this->commentTags()->attach($tag->id);
                $tag->incrementUsage();
            }
        } elseif ($this->isNestedReply()) {
            $this->replyTags()->sync([]);
            foreach ($tags as $tagName) {
                $tag = Tag::findOrCreateByName($tagName);
                $this->replyTags()->attach($tag->id);
                $tag->incrementUsage();
            }
        } else {
            // Regular tweet
            $this->tags()->sync([]);
            foreach ($tags as $tagName) {
                $tag = Tag::findOrCreateByName($tagName);
                $this->tags()->attach($tag->id);
                $tag->incrementUsage();
            }
        }
    }

    public function getRelevantTags()
    {
        if ($this->isTopLevelComment()) {
            return $this->commentTags;
        } elseif ($this->isNestedReply()) {
            return $this->replyTags;
        } else {
            return $this->tags;
        }
    }

    public function getContentWithParsedTags(): string
    {
        $content = e($this->content ?? "");
        $tags = $this->extractTags();
        
        foreach ($tags as $tagName) {
            $tag = Tag::findOrCreateByName($tagName);
            $content = preg_replace(
                "/#{$tagName}/i",
                "<a href='/tags/{$tag->slug}' class='text-blue-500 hover:text-blue-600 font-medium'>#{$tagName}</a>",
                $content
            );
        }
        
        return $content;
    }

    // Accessors for counts
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getRetweetsCountAttribute(): int
    {
        return $this->retweets()->count();
    }

    public function getQuotesCountAttribute(): int
    {
        return $this->quotes()->count();
    }

    public function getRepliesCountAttribute(): int
    {
        return $this->replies()->count();
    }

    public function getTotalCommentsCountAttribute(): int
    {
        $total = $this->quotes()->count();
        
        // Count all nested replies recursively
        foreach ($this->quotes as $comment) {
            $total += $this->countNestedReplies($comment);
        }
        
        return $total;
    }

    private function countNestedReplies($comment): int
    {
        $count = $comment->replies()->count();
        
        foreach ($comment->replies as $reply) {
            $count += $this->countNestedReplies($reply);
        }
        
        return $count;
    }

    // Helper methods
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isRetweetedBy(User $user): bool
    {
        return $this->retweets()->where('user_id', $user->id)->exists();
    }

    public function isQuotedBy(User $user): bool
    {
        return $this->quotes()->where('user_id', $user->id)->exists();
    }

    public function isReply(): bool
    {
        return !is_null($this->parent_comment_id);
    }

    public function isTopLevelComment(): bool
    {
        return $this->isQuote() && is_null($this->parent_comment_id);
    }

    public function isNestedReply(): bool
    {
        return $this->isQuote() && !is_null($this->parent_comment_id);
    }

    public function isQuote(): bool
    {
        return !is_null($this->quote_tweet_id);
    }

    public function isRetweet(): bool
    {
        return !is_null($this->original_tweet_id) && is_null($this->content);
    }

    // Model events for automatic tag syncing
    protected static function booted()
    {
        static::created(function ($tweet) {
            $tweet->syncTags();
        });

        static::updated(function ($tweet) {
            if ($tweet->wasChanged('content')) {
                $tweet->syncTags();
            }
        });

        static::deleted(function ($tweet) {
            // Decrement tag usage counts
            $tags = $tweet->getRelevantTags();
            foreach ($tags as $tag) {
                $tag->decrementUsage();
            }
        });
    }
}
