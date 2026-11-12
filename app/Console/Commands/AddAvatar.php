<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:add-avatar')]
#[Description('Command description')]
class AddAvatar extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = \App\Models\User::find(1);
        
        if ($user) {
            // Add a nice avatar URL for User 1
            $user->avatar = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=256&background=0D8ABC&color=fff&bold=true';
            $user->save();
            
            $this->info('Avatar added for user: ' . $user->name);
            $this->info('Avatar URL: ' . $user->avatar);
        } else {
            $this->error('User not found');
        }
        
        return 0;
    }
}
