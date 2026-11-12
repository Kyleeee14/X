<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'bio', 'avatar', 'header_image', 'followers_count', 'following_count', 'tweets_count'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function tweets()
    {
        return $this->hasMany(Tweet::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function likedTweets()
    {
        return $this->belongsToMany(Tweet::class, 'likes');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the avatar URL attribute.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // Check if it's already a full URL
            if (str_starts_with($this->avatar, 'http')) {
                return $this->avatar;
            }
            if (str_starts_with($this->avatar, '/')) {
                return asset(ltrim($this->avatar, '/'));
            }
            // If it's a relative path, convert to full storage URL
            return asset('storage/' . $this->avatar);
        }
        
        // Default avatar with user's name
        $initial = htmlspecialchars(mb_strtoupper(mb_substr($this->name ?? 'V', 0, 1)), ENT_XML1, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"><rect width="96" height="96" rx="48" fill="#e5edda"/><text x="48" y="61" text-anchor="middle" font-family="sans-serif" font-size="38" fill="#31694d">'.$initial.'</text></svg>';
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Get the header image URL attribute.
     */
    public function getHeaderImageUrlAttribute(): string
    {
        if ($this->header_image) {
            // Check if it's already a full URL
            if (str_starts_with($this->header_image, 'http')) {
                return $this->header_image;
            }
            if (str_starts_with($this->header_image, '/')) {
                return asset(ltrim($this->header_image, '/'));
            }
            // If it's a relative path, convert to full storage URL
            return asset('storage/' . $this->header_image);
        }
        
        // Default header image
        return 'https://images.unsplash.com/photo-1557683316-e97467b19a4a?w=1200&h=400&fit=crop';
    }
}
