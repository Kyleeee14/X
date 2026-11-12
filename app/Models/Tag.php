<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'usage_count'
    ];

    protected $casts = [
        'usage_count' => 'integer'
    ];

    public function tweets()
    {
        return $this->belongsToMany(Tweet::class, 'tweet_tags');
    }

    public function comments()
    {
        return $this->belongsToMany(Tweet::class, 'comment_tags', 'tag_id', 'tweet_id');
    }

    public function replies()
    {
        return $this->belongsToMany(Tweet::class, 'comment_tags', 'tag_id', 'tweet_id');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function findOrCreateByName($name)
    {
        $slug = Str::slug($name);
        
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'usage_count' => 0
            ]
        );
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function decrementUsage()
    {
        $this->decrement('usage_count');
    }

    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    public function scopeTrending($query)
    {
        return $query->where('usage_count', '>', 0)
                     ->orderBy('usage_count', 'desc')
                     ->limit(10);
    }
}
