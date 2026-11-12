@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Tag Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <div class="bg-blue-100 text-blue-600 rounded-full p-3">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">#{{ $tag->name }}</h1>
                <p class="text-gray-600">Used {{ $tag->usage_count }} times</p>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 text-sm">
                <strong>Tip:</strong> Use <code class="bg-blue-100 px-2 py-1 rounded">#{{ $tag->name }}</code> in your tweets, comments, and replies to add them to this tag!
            </p>
        </div>
    </div>

    <!-- Content Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex space-x-8" aria-label="Tabs">
            <button onclick="showTab('tweets')" id="tweets-tab" class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                Tweets ({{ $tweets->total() }})
            </button>
            <button onclick="showTab('comments')" id="comments-tab" class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Comments ({{ $comments->total() }})
            </button>
            <button onclick="showTab('replies')" id="replies-tab" class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                Replies ({{ $replies->total() }})
            </button>
        </nav>
    </div>

    <!-- Tweets Content -->
    <div id="tweets-content" class="tab-content">
        @if($tweets->count() > 0)
            <div class="space-y-4">
                @foreach($tweets as $tweet)
                    @include('tweets.tweet', ['tweet' => $tweet])
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $tweets->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tweets with this tag yet</h3>
                <p class="text-gray-600">Be the first to use #{{ $tag->name }} in a tweet!</p>
            </div>
        @endif
    </div>

    <!-- Comments Content -->
    <div id="comments-content" class="tab-content hidden">
        @if($comments->count() > 0)
            <div class="space-y-4">
                @foreach($comments as $comment)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="flex items-start space-x-3">
                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="w-10 h-10 rounded-full">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                    <span class="text-gray-500 text-sm">·</span>
                                    <span class="text-gray-500 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-gray-900 mb-3">
                                    {!! $comment->getContentWithParsedTags() !!}
                                </div>
                                @if($comment->image)
                                    <div class="mb-3 rounded-lg overflow-hidden max-w-full">
                                        <img src="{{ $comment->image }}" alt="Comment image" class="w-full h-auto max-h-64 object-cover">
                                    </div>
                                @endif
                                <div class="text-sm text-gray-500">
                                    <a href="{{ route('tweets.comments', $comment->quotedTweet->id) }}" class="hover:text-blue-500">
                                        View on @{{ $comment->quotedTweet->user->username }}'s tweet
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $comments->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No comments with this tag yet</h3>
                <p class="text-gray-600">Start using #{{ $tag->name }} in comments!</p>
            </div>
        @endif
    </div>

    <!-- Replies Content -->
    <div id="replies-content" class="tab-content hidden">
        @if($replies->count() > 0)
            <div class="space-y-4">
                @foreach($replies as $reply)
                    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start space-x-3">
                            <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" class="w-8 h-8 rounded-full">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="font-medium text-gray-900 text-sm">{{ $reply->user->name }}</span>
                                    <span class="text-gray-500 text-xs">·</span>
                                    <span class="text-gray-500 text-xs">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-gray-900 text-sm mb-2">
                                    {!! $reply->getContentWithParsedTags() !!}
                                </div>
                                @if($reply->image)
                                    <div class="mb-2 rounded-lg overflow-hidden max-w-full">
                                        <img src="{{ $reply->image }}" alt="Reply image" class="w-full h-auto max-h-48 object-cover">
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500">
                                    Reply to <a href="{{ route('tweets.comments', $reply->parentComment->quotedTweet->id) }}" class="hover:text-blue-500">
                                        @{{ $reply->parentComment->user->name }}'s comment
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-6">
                {{ $replies->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No replies with this tag yet</h3>
                <p class="text-gray-600">Start using #{{ $tag->name }} in replies!</p>
            </div>
        @endif
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}
</script>
@endsection
