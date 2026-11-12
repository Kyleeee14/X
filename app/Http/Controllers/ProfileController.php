<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Models\Tweet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display a user's profile.
     */
    public function show(User $user)
    {
        $tweets = Tweet::where('user_id', $user->id)
            ->whereNull('original_tweet_id')
            ->with(['user', 'likes', 'originalTweet.user'])
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        $isFollowing = false;
        if (Auth::check()) {
            $isFollowing = $user->followers()->where('follower_id', Auth::id())->exists();
        }

        return view('profile.show', compact('user', 'tweets', 'isFollowing'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $directory = public_path('images/profiles');
            File::ensureDirectoryExists($directory);
            $filename = Str::uuid().'.'.$avatar->extension();
            $avatar->move($directory, $filename);
            $user->avatar = '/images/profiles/'.$filename;
        } elseif ($request->filled('avatar_url')) {
            $user->avatar = $request->avatar_url;
        }

        // Handle header image upload
        if ($request->hasFile('header_image')) {
            $header = $request->file('header_image');
            $directory = public_path('images/headers');
            File::ensureDirectoryExists($directory);
            $filename = Str::uuid().'.'.$header->extension();
            $header->move($directory, $filename);
            $user->header_image = '/images/headers/'.$filename;
        } elseif ($request->filled('header_image_url')) {
            $user->header_image = $request->header_image_url;
        }

        // Update other fields
        $user->name = $request->name;
        $user->bio = $request->bio;
        $user->email = $request->email;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
