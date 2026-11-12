<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TweetController::class, 'index'])->name('home');
Route::get('/tweets/{tweet}/comments', [TweetController::class, 'comments'])->name('tweets.comments');

// Tag routes
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');

// Search and API routes
Route::get('/search', [TagController::class, 'search'])->name('search');
Route::get('/api/tags/autocomplete', [TagController::class, 'autocomplete'])->name('api.tags.autocomplete');
Route::get('/api/tags/trending', [TagController::class, 'trending'])->name('api.tags.trending');
Route::get('/api/tags/popular', [TagController::class, 'popular'])->name('api.tags.popular');

Route::middleware('auth')->group(function () {
    Route::post('/tweets', [TweetController::class, 'store'])->name('tweets.store');
    Route::put('/tweets/{tweet}', [TweetController::class, 'update'])->name('tweets.update');
    Route::delete('/tweets/{tweet}', [TweetController::class, 'destroy'])->name('tweets.destroy');
    Route::post('/tweets/{tweet}/like', [TweetController::class, 'like'])->name('tweets.like');
    Route::post('/tweets/{tweet}/reply', [TweetController::class, 'reply'])->name('tweets.reply');
    Route::post('/replies/{reply}/like', [TweetController::class, 'likeReply'])->name('tweets.like-reply');
    
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::post('/follow/{user}', [FollowController::class, 'follow'])->name('follow');
    Route::delete('/unfollow/{user}', [FollowController::class, 'unfollow'])->name('unfollow');
});


Route::middleware('auth')->group(function () {
    Route::get('/messages', [\App\Http\Controllers\MessageController::class,'index'])->name('messages.index');
    Route::get('/messages/{user}', [\App\Http\Controllers\MessageController::class,'index'])->name('messages.show');
    Route::get('/messages/{user}/poll', [\App\Http\Controllers\MessageController::class,'poll'])->name('messages.poll');
    Route::post('/messages/{user}', [\App\Http\Controllers\MessageController::class,'store'])->middleware('throttle:30,1')->name('messages.store');
    Route::get('/bookmarks', [\App\Http\Controllers\BookmarkController::class,'index'])->name('bookmarks.index');
    Route::post('/bookmarks/{tweet}', [\App\Http\Controllers\BookmarkController::class,'store'])->name('bookmarks.store');
    Route::delete('/bookmarks/{tweet}', [\App\Http\Controllers\BookmarkController::class,'destroy'])->name('bookmarks.destroy');
});
require __DIR__.'/auth.php';

