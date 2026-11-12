<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:show-database')]
#[Description('Command description')]
class ShowDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== USERS TABLE ===');
        $users = \App\Models\User::all(['id', 'name', 'username', 'email', 'created_at']);
        foreach ($users as $user) {
            $this->line("ID: {$user->id} | Name: {$user->name} | @{$user->username} | Email: {$user->email}");
        }
        
        $this->info("\n=== TWEETS TABLE ===");
        $tweets = \App\Models\Tweet::with('user')->take(10)->get(['id', 'user_id', 'content', 'created_at']);
        foreach ($tweets as $tweet) {
            $content = substr($tweet->content, 0, 50) . (strlen($tweet->content) > 50 ? '...' : '');
            $this->line("ID: {$tweet->id} | User: {$tweet->user->name} | Content: {$content}");
        }
        
        $this->info("\n=== FOLLOWS TABLE ===");
        $follows = \App\Models\Follow::with(['follower', 'following'])->take(5)->get();
        foreach ($follows as $follow) {
            $this->line("{$follow->follower->name} follows {$follow->following->name}");
        }
        
        $this->info("\n=== LIKES TABLE ===");
        $likes = \App\Models\Like::with(['user', 'tweet'])->take(5)->get();
        foreach ($likes as $like) {
            $content = substr($like->tweet->content, 0, 30) . '...';
            $this->line("{$like->user->name} likes tweet by {$like->tweet->user->name}: {$content}");
        }
        
        return 0;
    }
}
