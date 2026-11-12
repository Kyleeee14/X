<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function follow(User $user)
    {
        if ($user->id === Auth::id()) {
            abort(403);
        }

        $existingFollow = Follow::where('follower_id', Auth::id())
                               ->where('following_id', $user->id)
                               ->first();

        if (!$existingFollow) {
            Follow::create([
                'follower_id' => Auth::id(),
                'following_id' => $user->id
            ]);
            
            $user->increment('followers_count');
            Auth::user()->increment('following_count');
        }

        return redirect()->back();
    }

    public function unfollow(User $user)
    {
        $follow = Follow::where('follower_id', Auth::id())
                       ->where('following_id', $user->id)
                       ->first();

        if ($follow) {
            $follow->delete();
            $user->decrement('followers_count');
            Auth::user()->decrement('following_count');
        }

        return redirect()->back();
    }
}
