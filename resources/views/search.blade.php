@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Search Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-3">
            <h1 class="text-xl font-bold text-gray-900">Search Results</h1>
            @if($query)
                <p class="text-sm text-gray-500 mt-1">Results for "{{ $query }}"</p>
            @endif
        </div>
    </div>
    
    <!-- Search Results -->
    <div class="divide-y divide-gray-200">
        <!-- Tweets Section -->
        @if($tweets->count() > 0)
            <div class="bg-white">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Tweets</h2>
                </div>
                @foreach($tweets as $tweet)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex space-x-3">
                            <img src="{{ $tweet->user->avatar_url }}" alt="{{ $tweet->user->name }}" class="w-12 h-12 rounded-full">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-1">
                                    @php
                                        $userName = $tweet->user->name ?? 'User';
                                        $userUsername = $tweet->user->username ?? 'user';
                                        $usernameDisplay = '@' . $userUsername;
                                    @endphp
                                    <a href="{{ route('profile.show', $tweet->user) }}" class="font-bold text-gray-900 hover:underline">
                                        {{ $userName }}
                                    </a>
                                    <span class="text-gray-500">{{ $usernameDisplay }}</span>
                                    <span class="text-gray-500">·</span>
                                    <span class="text-gray-500 text-sm">{{ $tweet->created_at->diffForHumans() }}</span>
                                </div>
                                
                                <div class="mt-2 text-gray-900">
                                    {{ $tweet->content }}
                                </div>
                                
                                @if($tweet->image)
                                    <div class="mt-3 rounded-xl overflow-hidden max-w-full">
                                        <img src="{{ $tweet->image }}" alt="Tweet image" class="w-full h-auto max-h-48 object-cover">
                                    </div>
                                @endif
                                
                                <div class="flex items-center gap-6 text-gray-500 mt-3">
                                    <!-- Like -->
                                    <form action="{{ route('tweets.like', $tweet) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-1 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5 like-icon-{{ $tweet->id }} {{ $tweet->likes->where('user_id', auth()->id())->isNotEmpty() ? 'text-red-500 fill-current' : '' }}" fill="{{ $tweet->likes->where('user_id', auth()->id())->isNotEmpty() ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <span>{{ $tweet->likes_count ?? 0 }}</span>
                                        </button>
                                    </form>
                                    <!-- Comment -->
                                    <button onclick="window.location.href='{{ route('tweets.comments', $tweet->id) }}'" class="flex items-center gap-1 hover:text-blue-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        <span>{{ $tweet->replies_count ?? 0 }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <!-- Comments Section -->
        @if(isset($comments) && $comments->count() > 0)
            <div class="bg-white">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Comments</h2>
                </div>
                @foreach($comments as $comment)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex space-x-3">
                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="w-12 h-12 rounded-full">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-1">
                                    @php
                                        $userName = $comment->user->name ?? 'User';
                                        $userUsername = $comment->user->username ?? 'user';
                                        $usernameDisplay = '@' . $userUsername;
                                    @endphp
                                    <a href="{{ route('profile.show', $comment->user) }}" class="font-bold text-gray-900 hover:underline">
                                        {{ $userName }}
                                    </a>
                                    <span class="text-gray-500">{{ $usernameDisplay }}</span>
                                    <span class="text-gray-500">·</span>
                                    <span class="text-gray-500 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                
                                <div class="mt-2 text-gray-900">
                                    {{ $comment->content }}
                                </div>
                                
                                @if($comment->image)
                                    <div class="mt-3 rounded-xl overflow-hidden max-w-full">
                                        <img src="{{ $comment->image }}" alt="Comment image" class="w-full h-auto max-h-48 object-cover">
                                    </div>
                                @endif
                                
                                <!-- Replying to tweet indicator -->
                                <div class="mt-2 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                        @php
                                            $quotedUsername = '';
                                            if(isset($comment->quotedTweet) && isset($comment->quotedTweet->user)) {
                                                $quotedUsername = '@' . $comment->quotedTweet->user->username;
                                            }
                                        @endphp
                                        Replying to 
                                        @if(!empty($quotedUsername))
                                        <a href="{{ route('tweets.comments', $comment->quotedTweet->id) }}" class="hover:underline">
                                            {{ $quotedUsername }}
                                        </a>
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-6 text-gray-500 mt-3">
                                    <!-- Like -->
                                    <form action="{{ route('tweets.like', $comment) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-1 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5 like-icon-{{ $comment->id }} {{ $comment->likes->where('user_id', auth()->id())->isNotEmpty() ? 'text-red-500 fill-current' : '' }}" fill="{{ $comment->likes->where('user_id', auth()->id())->isNotEmpty() ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <span>{{ $comment->likes_count ?? 0 }}</span>
                                        </button>
                                    </form>
                                    <!-- View Tweet -->
                                    <a href="{{ route('tweets.comments', $comment->quotedTweet->id) }}" class="flex items-center gap-1 hover:text-blue-500 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span>View Tweet</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        
        <!-- Tags Section -->
        @if($tags->count() > 0)
            <div class="bg-white">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Tags</h2>
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <a href="{{ route('tags.show', $tag->slug) }}" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                #{{ $tag->name }}
                                <span class="ml-2 text-xs text-blue-600">{{ $tag->usage_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        
        <!-- No Results -->
        @if((isset($tweets) && $tweets->count() == 0) && (isset($comments) && $comments->count() == 0) && $tags->count() == 0)
            <div class="bg-white text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                <p class="text-gray-500">Try searching for something else</p>
            </div>
        @endif
    </div>
</div>
@endsection
