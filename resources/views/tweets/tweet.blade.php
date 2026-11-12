{{-- Template Individual Tweet --}}
{{-- File ini menampilkan satu tweet dengan semua interaksi (like, comment, edit, delete) --}}

<div class="flex space-x-3">
    <!-- Avatar User -->
    <!-- Menampilkan avatar user yang membuat tweet -->
    <div class="flex-shrink-0">
        <img src="{{ $tweet->user->avatar_url }}" 
             alt="{{ $tweet->user->name ?? 'User' }}" 
             class="h-12 w-12 rounded-full">
    </div>

    <!-- Konten Tweet -->
    <div class="flex-1">
        <!-- Info User (Nama, Username, Waktu) -->
        <div class="flex items-center space-x-1">
            <a href="{{ route('profile.show', $tweet->user) }}" class="font-bold text-gray-900 hover:underline {{ $tweet->user->is_admin ? 'system-name' : '' }}">
                {{ $tweet->user->name ?? 'User' }}
            </a>
            @if($tweet->user->is_admin)<span class="system-badge">BOT · ADMIN</span>@endif
            <span class="text-gray-500">·</span>
            <span class="text-gray-500 text-sm">{{ $tweet->created_at->diffForHumans() }}</span>
        </div>

        <!-- Teks Tweet dengan Tags -->
        <!-- Menampilkan konten tweet dengan hashtag yang sudah di-parse -->
        <div id="tweet-content-{{ $tweet->id }}" class="mt-2 text-gray-900">{!! $tweet->getContentWithParsedTags() !!}</div>

        <!-- Gambar Tweet (jika ada) -->
        @if($tweet->image)
            <div id="tweet-image-{{ $tweet->id }}" class="mt-3 rounded-xl overflow-hidden max-w-full">
                <img src="{{ $tweet->image }}" alt="Tweet image" class="w-full h-auto max-h-48 object-cover rounded-xl">
            </div>
        @endif

        @include('partials.post-media')
        <!-- Tombol Aksi Tweet -->
        <!-- Like, Comment, Edit, Delete -->
        <div class="flex items-center gap-6 text-gray-500 mt-3">
            <!-- Tombol Like -->
            <button onclick="toggleLike({{ $tweet->id }}, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5 like-icon-{{ $tweet->id }} {{ $tweet->likes->where('user_id', auth()->id())->isNotEmpty() ? 'text-red-500 fill-current' : '' }}" fill="{{ $tweet->likes->where('user_id', auth()->id())->isNotEmpty() ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <span class="like-count-{{ $tweet->id }}">{{ $tweet->likes_count ?? 0 }}</span>
            </button>

            <!-- Tombol Comment -->
            <button onclick="window.location.href='{{ route('tweets.comments', $tweet->id) }}'" class="flex items-center gap-1 hover:text-blue-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <span>{{ $tweet->quotes()->whereNull('parent_comment_id')->count() }}</span>
            </button>

            @auth
            @php($saved = \Illuminate\Support\Facades\DB::table('bookmarks')->where('user_id',auth()->id())->where('tweet_id',$tweet->id)->exists())
            <button class="bookmark" data-bookmark="{{ route('bookmarks.store',$tweet) }}" aria-label="Simpan postingan" aria-pressed="{{ $saved ? 'true' : 'false' }}"><x-icon name="bookmark"/></button>
            @endauth
            <button type="button" data-share="{{ route('tweets.comments',$tweet) }}" aria-label="Bagikan postingan"><x-icon name="share"/></button>
            <!-- Tombol Edit (hanya untuk tweet sendiri) -->
            @if($tweet->user_id === auth()->id())
                <button onclick="toggleEditForm({{ $tweet->id }})" class="text-gray-500 hover:text-blue-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
            @endif

            <!-- Tombol Delete (hanya untuk tweet sendiri) -->
            @if($tweet->user_id === auth()->id())
                <form action="{{ route('tweets.destroy', $tweet) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-500 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            @elseif(auth()->user()?->is_admin)
                <form action="{{ route('tweets.destroy', $tweet) }}" method="POST" class="inline moderation-form" onsubmit="return prepareTakedown(this)">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="moderation_reason">
                    <button type="submit" class="text-gray-500 hover:text-red-500 transition-colors" aria-label="Turunkan postingan sebagai admin" title="Takedown">
                        <x-icon name="shield"/>
                    </button>
                </form>
            @endif
        </div>

        <!-- Form Edit Tweet -->
        @if($tweet->user_id === auth()->id())
            <div id="editForm_{{ $tweet->id }}" class="hidden mt-4 border-t border-gray-200 pt-4">
                <form action="{{ route('tweets.update', $tweet) }}" method="POST" enctype="multipart/form-data" class="space-y-3" onsubmit="submitEditForm(event, {{ $tweet->id }})">
                    @csrf
                    @method('PUT')
                    <div class="flex space-x-3">
                        <img src="{{ auth()->user()->avatar_url }}" 
                             alt="{{ auth()->user()->name }}" 
                             class="h-8 w-8 rounded-full">
                        <div class="flex-1">
                            <textarea name="content" 
                                      id="editContent_{{ $tweet->id }}"
                                      rows="3" 
                                      maxlength="280"
                                      oninput="document.getElementById('editCharCount_{{ $tweet->id }}').textContent = this.value.length"
                                      class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm">{{ $tweet->content }}</textarea>
                            
                            <!-- Section Upload Gambar -->
                            <div class="mt-2 flex items-center space-x-3">
                                <input type="file" 
                                       id="editImage_{{ $tweet->id }}"
                                       name="image" 
                                       accept="image/*" 
                                       class="hidden"
                                       onchange="previewEditImage({{ $tweet->id }})">
                                <input type="hidden" 
                                       id="editRemoveImage_{{ $tweet->id }}"
                                       name="remove_image" 
                                       value="0">
                                <label for="editImage_{{ $tweet->id }}" class="flex items-center space-x-2 px-3 py-2 text-blue-500 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors text-sm">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $tweet->image ? 'Ganti Gambar' : 'Tambah Gambar' }}</span>
                                </label>
                                
                                <!-- Preview Gambar -->
                                <div id="editImagePreview_{{ $tweet->id }}" class="{{ $tweet->image ? '' : 'hidden' }}">
                                    <div class="relative inline-block">
                                        <img id="editPreviewImg_{{ $tweet->id }}" 
                                             src="{{ $tweet->image ?? '' }}" 
                                             alt="Preview" 
                                             class="max-w-full h-32 rounded-lg object-cover">
                                        <button type="button" 
                                                onclick="removeEditImage({{ $tweet->id }})"
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tombol Aksi Form Edit -->
                            <div class="flex items-center justify-between mt-3">
                                <!-- Counter Karakter -->
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-500">
                                        <span id="editCharCount_{{ $tweet->id }}">{{ strlen($tweet->content) }}</span>/280
                                    </span>
                                </div>
                                <!-- Tombol Cancel dan Save -->
                                <div class="flex items-center space-x-2">
                                    <!-- Tombol Cancel -->
                                    <button type="button" 
                                            onclick="document.getElementById('editForm_{{ $tweet->id }}').classList.add('hidden')"
                                            class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm font-medium transition-colors">
                                        Batal
                                    </button>
                                    <!-- Tombol Save -->
                                    <button type="submit" 
                                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm font-medium transition-colors">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- Quote Tweet Form -->
        @auth
        <div id="quoteForm_{{ $tweet->id }}" class="hidden mt-4 border-t border-gray-200 pt-4">
            <div class="bg-gray-50 rounded-lg p-3">
                <!-- Preview Tweet yang di-quotekan -->
                <!-- Quoted Tweet Preview -->
                <div class="mb-3 border border-gray-200 rounded-lg p-2 bg-white">
                    <div class="flex items-start space-x-2">
                        <img src="{{ $tweet->user->avatar_url }}" alt="{{ $tweet->user->name ?? 'User' }}" class="w-5 h-5 rounded-full">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-1">
                                <span class="font-bold text-gray-900 text-xs">{{ $tweet->user->name ?? 'User' }}</span>
                                <span class="text-gray-500 text-xs">@{{ $tweet->user->username ?? 'user' }}</span>
                                <span class="text-gray-500 text-xs">·</span>
                                <span class="text-gray-500 text-xs">{{ $tweet->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-gray-800 text-xs line-clamp-2">
                                {{ $tweet->content }}
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('tweets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="quote_tweet_id" value="{{ $tweet->id }}">
                    <div class="flex space-x-3">
                        <img src="{{ auth()->user()?->avatar_url ?? 'https://ui-avatars.com/api/?name=Guest&color=7F9CF5&background=EBF4FF' }}" 
                             alt="{{ auth()->user()?->name ?? 'User' }}" 
                             class="h-8 w-8 rounded-full">
                        <div class="flex-1">
                            <textarea name="content" 
                                      rows="3" 
                                      placeholder="Add a comment"
                                      class="w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"
                                      maxlength="280"></textarea>
                            
                            <!-- Image Preview for Quote Form -->
                            <div id="quote-image-preview-{{ $tweet->id }}" class="mt-2 hidden">
                                <div class="relative inline-block">
                                    <img id="quote-preview-img-{{ $tweet->id }}" src="" alt="Preview" class="max-w-full h-32 rounded-lg object-cover">
                                    <button type="button" onclick="removeQuoteImage({{ $tweet->id }})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-2 flex justify-between items-center">
                                <!-- Image Upload -->
                                <div class="flex items-center space-x-2">
                                    <input type="file" 
                                           id="quote_image_{{ $tweet->id }}"
                                           name="image" 
                                           accept="image/*" 
                                           class="hidden"
                                           onchange="previewQuoteImage({{ $tweet->id }})">
                                    <label for="quote_image_{{ $tweet->id }}" class="flex items-center space-x-1 px-2 py-1 text-blue-500 hover:bg-blue-50 rounded cursor-pointer transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-xs">Image</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="hideQuoteForm({{ $tweet->id }})" class="px-3 py-1 text-gray-500 hover:text-gray-700 text-sm">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="px-4 py-2 bg-blue-500 text-white rounded-full text-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                                        Quote
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endauth

        <!-- Comments Section -->
        <!-- Menampilkan semua komentar dan replies -->
        <div class="inline-comments mt-4 border-t border-gray-200">
            @if($tweet->replies && $tweet->replies->count() > 0)
                <div class="space-y-4">
                    @foreach($tweet->replies as $reply)
                        <!-- Single Comment -->
                        <div class="flex space-x-3 py-4 border-b border-gray-100 last:border-b-0">
                            <!-- Avatar User -->
                            <div class="flex-shrink-0">
                                <img src="{{ $reply->user->avatar_url }}" 
                                     alt="{{ $reply->user->name ?? 'User' }}" 
                                     class="h-10 w-10 rounded-full">
                            </div>

                            <!-- Konten Comment -->
                            <div class="flex-1 min-w-0">
                                <!-- Info User (Nama, Username, Waktu) -->
                                <div class="flex items-center space-x-1">
                                    <a href="{{ route('profile.show', $reply->user) }}" class="font-bold text-gray-900 hover:underline">
                                        {{ $reply->user->name ?? 'User' }}
                                    </a>
                                    <span class="text-gray-500">·</span>
                                    <span class="text-gray-500 text-sm">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>

                                <!-- Teks Comment dengan Tags -->
                                <div class="mt-1 text-gray-900 break-words">
                                    {!! $reply->getContentWithParsedTags() !!}
                                </div>

                                <!-- Gambar Comment (jika ada) -->
                                @if($reply->image)
                                    <div class="mt-2 rounded-xl overflow-hidden max-w-full">
                                        <img src="{{ $reply->image }}" alt="Comment image" class="w-full h-auto max-h-48 object-cover rounded-xl">
                                    </div>
                                @endif

                                <!-- Tombol Aksi Comment -->
                                <div class="flex items-center gap-6 text-gray-500 mt-2">
                                    <!-- Tombol Like -->
                                    <button onclick="toggleLike({{ $reply->id }}, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4 like-icon-{{ $reply->id }} {{ $reply->likes->where('user_id', auth()->id())->isNotEmpty() ? 'text-red-500 fill-current' : '' }}" fill="{{ $reply->likes->where('user_id', auth()->id())->isNotEmpty() ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="like-count-{{ $reply->id }}">{{ $reply->likes_count ?? 0 }}</span>
                                    </button>

                                    <!-- Tombol Edit (hanya untuk comment sendiri) -->
                                    @if($reply->user_id === auth()->id())
                                        <button onclick="toggleEditForm({{ $reply->id }})" class="text-gray-500 hover:text-blue-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Tombol Delete (hanya untuk comment sendiri) -->
                                    @if($reply->user_id === auth()->id())
                                        <form action="{{ route('tweets.destroy', $reply) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-500 hover:text-red-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Test if JavaScript is loading
console.log('Tweet JavaScript loaded successfully!');

function showCommentsSidebar(tweetId) {
    const sidebar = document.getElementById('comments-sidebar-' + tweetId);
    if (sidebar) {
        sidebar.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeCommentsSidebar(tweetId) {
    const sidebar = document.getElementById('comments-sidebar-' + tweetId);
    if (sidebar) {
        sidebar.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

function showReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.classList.remove('hidden');
        form.querySelector('textarea').focus();
    }
}

function hideReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.classList.add('hidden');
    }
}

// AJAX Like functionality
function toggleLike(tweetId, button) {
    // Prevent double clicks
    if (button.disabled) return;
    button.disabled = true;
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        console.error('CSRF token not found');
        button.disabled = false;
        return;
    }
    
    fetch(`/tweets/${tweetId}/like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Like response:', data);
        
        // Update like count
        const countElement = document.querySelector(`.like-count-${tweetId}`);
        if (countElement) {
            countElement.textContent = data.likes_count || 0;
        }
        
        // Update like icon
        const iconElement = document.querySelector(`.like-icon-${tweetId}`);
        if (iconElement) {
            if (data.liked) {
                iconElement.classList.add('text-red-500', 'fill-current');
                iconElement.setAttribute('fill', 'currentColor');
            } else {
                iconElement.classList.remove('text-red-500', 'fill-current');
                iconElement.setAttribute('fill', 'none');
            }
        }
        
        button.disabled = false;
    })
    .catch(error => {
        console.error('Error toggling like:', error);
        button.disabled = false;
        
        // Fallback to regular form submission
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/tweets/${tweetId}/like`;
        form.style.display = 'none';
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = token;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    });
}

// AJAX Reply Form Submission
function submitReplyForm(event, tweetId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Disable submit button to prevent double submission
    submitButton.disabled = true;
    submitButton.textContent = 'Posting...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            // Add new comment to the comments section
            addNewComment(tweetId, data);
            
            // Reset and hide reply form
            form.reset();
            hideReplyForm(tweetId);
            
            // Clear image preview
            const imagePreview = document.getElementById('reply-image-preview-' + tweetId);
            if (imagePreview) {
                imagePreview.classList.add('hidden');
            }
        } else {
            // Handle errors silently - no notifications
            console.error('Reply failed:', data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle errors silently - no notifications
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = 'Reply';
    });
}

// Add new comment to the comments section
function addNewComment(tweetId, data) {
    const commentsSection = document.querySelector(`#tweet-content-${tweetId}`).closest('.flex-1').querySelector('.mt-4.border-t');
    const commentsContainer = commentsSection.querySelector('.space-y-4');
    
    if (commentsContainer) {
        // Create new comment HTML
        const newCommentHtml = `
            <div class="flex space-x-3 py-4 border-b border-gray-100 last:border-b-0">
                <!-- Avatar User -->
                <div class="flex-shrink-0">
                    <img src="${data.user.avatar_url}" 
                         alt="${data.user.name}" 
                         class="h-10 w-10 rounded-full">
                </div>

                <!-- Konten Comment -->
                <div class="flex-1 min-w-0">
                    <!-- Info User (Nama, Username, Waktu) -->
                    <div class="flex items-center space-x-1">
                        <a href="${data.user.profile_url}" class="font-bold text-gray-900 hover:underline">
                            ${data.user.name}
                        </a>
                        <span class="text-gray-500">·</span>
                        <span class="text-gray-500 text-sm">just now</span>
                    </div>

                    <!-- Teks Comment dengan Tags -->
                    <div class="mt-1 text-gray-900 break-words">
                        ${data.content_with_tags}
                    </div>

                    <!-- Gambar Comment (jika ada) -->
                    ${data.image ? `
                        <div class="mt-2 rounded-xl overflow-hidden max-w-full">
                            <img src="${data.image}" alt="Comment image" class="w-full h-auto max-h-48 object-cover rounded-xl">
                        </div>
                    ` : ''}

                    <!-- Tombol Aksi Comment -->
                    <div class="flex items-center gap-6 text-gray-500 mt-2">
                        <!-- Tombol Like -->
                        <button onclick="toggleLike(${data.id}, this)" class="flex items-center gap-1 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4 like-icon-${data.id}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="like-count-${data.id}">0</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add new comment to the container
        commentsContainer.insertAdjacentHTML('beforeend', newCommentHtml);
        
        // Show comments section if it was hidden
        if (commentsSection.classList.contains('hidden')) {
            commentsSection.classList.remove('hidden');
        }
    }
}

// AJAX Edit Form Submission
function submitEditForm(event, tweetId) {
    event.preventDefault();
    
    const form = document.querySelector(`#editForm_${tweetId} form`);
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type=submit]');
    
    // Ensure _method field is included for PUT request
    if (!formData.has('_method')) {
        formData.append('_method', 'PUT');
    }
    
    // Disable submit button to prevent double submission
    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response:', text);
                throw new Error(`HTTP ${response.status}: ${text}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Success response:', data);
        if (data.success) {
            // Update tweet content and image in real-time
            updateTweetContent(tweetId, data.content, data.image);
            
            // Close edit form
            document.getElementById('editForm_' + tweetId).classList.add('hidden');
            
            // Show success message (optional)
            console.log(data.message);
        } else {
            // Handle errors
            console.error('Update failed:', data);
            alert('Failed to update tweet. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message + '. Please try again.');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.textContent = 'Save';
    });
}

// Toggle Edit Form
function toggleEditForm(tweetId) {
    const form = document.getElementById('editForm_' + tweetId);
    console.log('Form element:', form);
    
    if (form) {
        const wasHidden = form.classList.contains('hidden');
        
        if (!wasHidden) {
            // Closing form - reset state
            form.classList.add('hidden');
            console.log('Closing form');
        } else {
            // Opening form - ensure proper state
            form.classList.remove('hidden');
            console.log('Opening form - initializing components');
            initializeEditImageUpload(tweetId);
            initializeCharCounter(tweetId);
            
            // Reset form to current tweet data
            resetEditForm(tweetId);
        }
    } else {
        console.error('Form not found for tweet ID:', tweetId);
    }
}

// Reset Edit Form
function resetEditForm(tweetId) {
    console.log('Resetting edit form for tweet ID:', tweetId);
    
    // Get current tweet content from display
    const tweetContent = document.querySelector(`#tweet-content-${tweetId}`);
    const tweetImage = document.querySelector(`#tweet-image-${tweetId}`);
    
    if (tweetContent) {
        const textarea = document.getElementById(`editContent_${tweetId}`);
        if (textarea) {
            textarea.value = tweetContent.textContent.trim();
            console.log('Reset textarea content:', textarea.value);
        }
    }
    
    // Reset image preview
    const imagePreview = document.getElementById(`editImagePreview_${tweetId}`);
    const previewImg = document.getElementById(`editPreviewImg_${tweetId}`);
    const removeImageInput = document.getElementById(`editRemoveImage_${tweetId}`);
    
    if (tweetImage && !tweetImage.classList.contains('hidden')) {
        // Tweet has image
        if (imagePreview) imagePreview.classList.remove('hidden');
        if (previewImg && tweetImage.querySelector('img')) {
            previewImg.src = tweetImage.querySelector('img').src;
        }
        if (removeImageInput) removeImageInput.value = '0';
    } else {
        // Tweet has no image
        if (imagePreview) imagePreview.classList.add('hidden');
        if (previewImg) previewImg.src = '';
        if (removeImageInput) removeImageInput.value = '0';
    }
    
    // Reset character counter
    const charCount = document.getElementById(`editCharCount_${tweetId}`);
    const textarea = document.getElementById(`editContent_${tweetId}`);
    if (charCount && textarea) {
        charCount.textContent = textarea.value.length;
    }
}

function initializeEditImageUpload(tweetId) {
    const fileInput = document.getElementById('editImage_' + tweetId);
    const imagePreview = document.getElementById('editImagePreview_' + tweetId);
    const previewImg = document.getElementById('editPreviewImg_' + tweetId);
    const removeButton = document.getElementById('editRemoveImage_' + tweetId);
    
    if (fileInput && imagePreview && previewImg && removeButton) {
        // Remove existing listeners to prevent duplicates
        const newFileInput = fileInput.cloneNode(true);
        fileInput.parentNode.replaceChild(newFileInput, fileInput);
        
        newFileInput.addEventListener('change', function(e) {
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
        
        // Remove button listener
        const newRemoveButton = removeButton.cloneNode(true);
        removeButton.parentNode.replaceChild(newRemoveButton, removeButton);
        
        newRemoveButton.addEventListener('click', function() {
            newFileInput.value = '';
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        });
    }
}

function initializeCharCounter(tweetId) {
    const textarea = document.getElementById('editContent_' + tweetId);
    const charCount = document.getElementById('editCharCount_' + tweetId);
    
    if (textarea && charCount) {
        // Remove existing listeners to prevent duplicates
        const newTextarea = textarea.cloneNode(true);
        textarea.parentNode.replaceChild(newTextarea, textarea);
        
        newTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            if (count > 260) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
        });
    }
}

// Preview Edit Image Function
function previewEditImage(tweetId) {
    const fileInput = document.getElementById('editImage_' + tweetId);
    const imagePreview = document.getElementById('editImagePreview_' + tweetId);
    const previewImg = document.getElementById('editPreviewImg_' + tweetId);
    const removeImageInput = document.getElementById('editRemoveImage_' + tweetId);
    
    if (fileInput && imagePreview && previewImg) {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        } else {
            // Hide preview if no file selected
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        }
    }
}

// Remove Edit Image Function
function removeEditImage(tweetId) {
    const fileInput = document.getElementById('editImage_' + tweetId);
    const imagePreview = document.getElementById('editImagePreview_' + tweetId);
    const previewImg = document.getElementById('editPreviewImg_' + tweetId);
    const removeImageInput = document.getElementById('editRemoveImage_' + tweetId);
    
    if (fileInput && imagePreview && previewImg && removeImageInput) {
        fileInput.value = '';
        imagePreview.classList.add('hidden');
        previewImg.src = '';
        removeImageInput.value = '1'; // Set to 1 to indicate image should be removed
    }
}

// Preview Quote Image Function
function previewQuoteImage(tweetId) {
    const fileInput = document.getElementById('quote_image_' + tweetId);
    const imagePreview = document.getElementById('quote-image-preview-' + tweetId);
    const previewImg = document.getElementById('quote-preview-img-' + tweetId);
    
    if (fileInput && imagePreview && previewImg) {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        } else {
            // Hide preview if no file selected
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        }
    }
}

// Remove Quote Image Function
function removeQuoteImage(tweetId) {
    const fileInput = document.getElementById('quote_image_' + tweetId);
    const imagePreview = document.getElementById('quote-image-preview-' + tweetId);
    const previewImg = document.getElementById('quote-preview-img-' + tweetId);
    
    if (fileInput && imagePreview && previewImg) {
        fileInput.value = '';
        imagePreview.classList.add('hidden');
        previewImg.src = '';
    }
}

// Preview Reply Image Function
function previewReplyImage(tweetId) {
    const fileInput = document.getElementById('reply_image_' + tweetId);
    const imagePreview = document.getElementById('reply-image-preview-' + tweetId);
    const previewImg = document.getElementById('reply-preview-img-' + tweetId);
    
    if (fileInput && imagePreview && previewImg) {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        } else {
            // Hide preview if no file selected
            imagePreview.classList.add('hidden');
            previewImg.src = '';
        }
    }
}

// Remove Reply Image Function
function removeReplyImage(tweetId) {
    const fileInput = document.getElementById('reply_image_' + tweetId);
    const imagePreview = document.getElementById('reply-image-preview-' + tweetId);
    const previewImg = document.getElementById('reply-preview-img-' + tweetId);
    
    if (fileInput && imagePreview && previewImg) {
        fileInput.value = '';
        imagePreview.classList.add('hidden');
        previewImg.src = '';
    }
}

// Show Reply Form Function
function showReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.classList.remove('hidden');
        form.querySelector('textarea').focus();
    }
}

// Hide Reply Form Function
function hideReplyForm(commentId) {
    const form = document.getElementById('reply-form-' + commentId);
    if (form) {
        form.classList.add('hidden');
    }
}

// Hide Quote Form Function
function hideQuoteForm(tweetId) {
    const form = document.getElementById('quoteForm_' + tweetId);
    if (form) {
        form.classList.add('hidden');
    }
}

// Real-time update after edit
function updateTweetContent(tweetId, newContent, newImage) {
    const tweetContent = document.querySelector(`#tweet-content-${tweetId}`);
    const tweetImage = document.querySelector(`#tweet-image-${tweetId}`);
    
    // Update content with animation
    if (tweetContent) {
        tweetContent.style.opacity = '0.5';
        setTimeout(() => {
            tweetContent.textContent = newContent;
            tweetContent.style.opacity = '1';
        }, 200);
    }
    
    // Update image with animation
    if (tweetImage) {
        tweetImage.style.opacity = '0.5';
        setTimeout(() => {
            if (newImage && newImage !== '') {
                // If there's a new image, update or show the image
                const img = tweetImage.querySelector('img');
                if (img) {
                    img.src = newImage;
                }
                tweetImage.classList.remove('hidden');
                tweetImage.style.opacity = '1';
            } else {
                // If image was removed, hide the image container
                tweetImage.classList.add('hidden');
            }
        }, 200);
    }
    
    // Show success feedback
    showEditSuccessFeedback();
}

// Show success feedback after edit
function showEditSuccessFeedback() {
    const feedback = document.createElement('div');
    feedback.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
    feedback.textContent = 'Tweet updated successfully!';
    document.body.appendChild(feedback);
    
    setTimeout(() => {
        feedback.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(feedback);
        }, 300);
    }, 2000);
}
</script>
