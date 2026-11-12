<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:fix-avatar-path')]
#[Description('Fix avatar paths in database')]
class FixAvatarPath extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::whereNotNull('avatar')->get();
        
        foreach ($users as $user) {
            // Fix paths that don't start with /storage/
            if ($user->avatar && !str_starts_with($user->avatar, 'http') && !str_starts_with($user->avatar, '/storage/')) {
                $user->avatar = '/storage/' . ltrim($user->avatar, 'storage/');
                $user->save();
                $this->info('Fixed avatar path for user: ' . $user->name);
            }
            // Fix typo: vatars -> avatars
            if ($user->avatar && str_contains($user->avatar, '/storage/vatars/')) {
                $user->avatar = str_replace('/storage/vatars/', '/storage/avatars/', $user->avatar);
                $user->save();
                $this->info('Fixed avatar typo for user: ' . $user->name);
            }
        }
        
        $this->info('Avatar paths fixed successfully!');
        return 0;
    }
}
