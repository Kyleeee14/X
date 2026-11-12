@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-3">
            <h1 class="text-xl font-bold text-gray-900">Home</h1>
        </div>
    </div>

    <!-- Tweet Form -->
    @auth
    <div class="bg-white border-b border-gray-200">
        <form action="{{ route('tweets.store') }}" method="POST" enctype="multipart/form-data" class="p-4">
            @csrf
            <div class="flex space-x-3">
                <div class="flex-shrink-0">
                    <img src="{{ Auth::user()->avatar_url }}" 
                         alt="{{ Auth::user()->name }}" 
                         class="h-12 w-12 rounded-full">
                </div>
                <div class="flex-1">
                    <textarea name="content" 
                              rows="3" 
                              placeholder="What's happening?!"
                              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-lg"
                              maxlength="280">{{ old('content') }}</textarea>
                    @error('content')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <!-- Image Upload Section -->
            <div class="mt-3 ml-15">
                <div class="flex items-center justify-between">
                    <!-- File Upload -->
                    <div class="flex items-center space-x-2">
                        <input type="file" 
                               id="tweet_image" 
                               name="image" 
                               accept="image/*" 
                               class="hidden">
                        <label for="tweet_image" class="flex items-center space-x-2 px-3 py-2 text-blue-500 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-medium">Add Image</span>
                        </label>
                        
                        <!-- Image Preview -->
                        <div id="image-preview" class="hidden">
                            <div class="relative inline-block">
                                <img id="preview-img" src="" alt="Preview" class="max-w-full h-48 rounded-lg object-cover">
                                <button type="button" id="remove-image" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="flex justify-end mt-4">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium transition-colors">
                            Tweet
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @endauth

    <!-- Tweets Feed -->
    <div class="bg-white">
        @forelse($tweets as $tweet)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                <!-- Original Tweet (if retweet) -->
                @if($tweet->original_tweet_id)
                    <div class="flex items-center space-x-2 mb-2 text-gray-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/>
                        </svg>
                        <span class="text-sm">{{ $tweet->user->name }} retweeted</span>
                    </div>
                    <div class="ml-6">
                        @include('tweets.tweet', ['tweet' => $tweet->originalTweet])
                    </div>
                @else
                    @include('tweets.tweet', ['tweet' => $tweet])
                @endif
            </div>
        @empty
            <div class="p-12 text-center text-gray-500">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tweets yet</h3>
                <p class="text-gray-500">Be the first to tweet and start the conversation!</p>
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="content"]');
    const charCount = document.getElementById('char-count');
    const fileInput = document.getElementById('tweet_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeImage = document.getElementById('remove-image');
    
    // Character count
    if (textarea && charCount) {
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            if (count > 260) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
        });
    }
    
    // Image upload
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Remove image
    if (removeImage) {
        removeImage.addEventListener('click', function() {
            fileInput.value = '';
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        });
    }
});

// Share tweet
function shareTweet(tweetId) {
    const url = window.location.origin + '/tweets/' + tweetId;
    
    if (navigator.share) {
        navigator.share({
            title: 'Vibe Tweet',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url);
        alert('Tweet link copied to clipboard!');
    }
}

// Show quote form
function showQuoteForm(tweetId) {
    const quoteForm = document.getElementById('quoteForm_' + tweetId);
    quoteForm.classList.remove('hidden');
    
    // Focus on textarea when opened
    const textarea = quoteForm.querySelector('textarea');
    textarea.focus();
}

// Hide quote form
function hideQuoteForm(tweetId) {
    const quoteForm = document.getElementById('quoteForm_' + tweetId);
    quoteForm.classList.add('hidden');
}
</script>
@endsection
