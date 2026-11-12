<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationAction extends Model
{
    protected $fillable = [
        'moderator_id',
        'author_id',
        'tweet_id',
        'action',
        'reason',
        'content_snapshot',
        'media_snapshot',
    ];

    protected function casts(): array
    {
        return ['media_snapshot' => 'array'];
    }
}
