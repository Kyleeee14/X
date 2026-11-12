@extends('layouts.app')

@section('content')
{{-- Halaman Profile User --}}
{{-- Menampilkan profil user lengkap dengan avatar, header, bio, dan tweet-tweetnya --}}

<div class="max-w-2xl mx-auto">
    <!-- Header Profile -->
    <!-- Bagian atas dengan cover image, avatar, dan info user -->
    <div class="bg-white border border-gray-200">
        <!-- Cover Image -->
        @if($user->header_image)
            <!-- Tampilkan cover image jika user upload -->
            <div class="h-48">
                <img src="{{ $user->header_image_url }}" alt="Cover" class="w-full h-full object-cover">
            </div>
        @else
            <!-- Default gradient jika tidak ada cover image -->
            <div class="h-48 bg-gradient-to-r from-blue-400 to-blue-600"></div>
        @endif
        
        <div class="px-4 pb-4">
            <div class="flex items-end -mt-12 mb-4">
                <!-- Avatar User -->
                <!-- Avatar dengan border putih dan shadow -->
                <div class="flex-shrink-0">
                    <img src="{{ $user->avatar_url }}" 
                         alt="{{ $user->name }}" 
                         class="h-24 w-24 rounded-full border-4 border-white object-cover">
                </div>
                
                <!-- Tombol Follow/Unfollow -->
                <!-- Hanya tampil untuk user yang sudah login dan bukan profile sendiri -->
                @auth
                    @if($user->id !== auth()->id() && ! $user->is_admin)
                        <div class="ml-auto mb-2"><a class="primary" href="{{ route('messages.show', $user) }}">Kirim pesan</a>
                            @if($isFollowing)
                                <!-- Tombol Unfollow jika sudah following -->
                                <form action="{{ route('unfollow', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                        Following
                                    </button>
                                </form>
                            @else
                                <!-- Tombol Follow jika belum following -->
                                <form action="{{ route('follow', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Follow
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
            
            <!-- Info User -->
            <!-- Nama dan bio user -->
            <div class="mb-4">
                <h1 class="text-xl font-bold text-gray-900 {{ $user->is_admin ? 'system-name' : '' }}">{{ $user->name }} @if($user->is_admin)<span class="system-badge">BOT · ADMIN</span>@endif</h1>
                @if($user->is_admin)<p class="system-label">Sistem moderasi resmi Vibe</p>@endif
            </div>
            
            <!-- Bio User -->
            <!-- Tampilkan bio jika ada -->
            @if($user->bio)
                <div class="mb-4">
                    <p class="text-gray-900">{{ $user->bio }}</p>
                </div>
            @endif
            
            <!-- Statistik User -->
            <!-- Jumlah tweet, following, dan followers -->
            <div class="flex space-x-4 text-gray-500">
                <div>
                    <span class="font-bold text-gray-900">{{ $user->tweets_count ?? 0 }}</span>
                    <span class="ml-1">Tweets</span>
                </div>
                <div>
                    <span class="font-bold text-gray-900">{{ $user->following_count ?? 0 }}</span>
                    <span class="ml-1">Following</span>
                </div>
                <div>
                    <span class="font-bold text-gray-900">{{ $user->followers_count ?? 0 }}</span>
                    <span class="ml-1">Followers</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Tweets -->
    <!-- Tab navigasi untuk menampilkan tweet user -->
    <div class="bg-white border border-gray-200">
        <div class="flex">
            <div class="flex-1 text-center py-3 border-b-2 border-blue-500">
                <span class="font-bold text-gray-900">Tweets</span>
            </div>
        </div>
    </div>

    <!-- Daftar Tweet User -->
    <!-- Menampilkan semua tweet dari user ini -->
    <div class="bg-white">
        @forelse($tweets as $tweet)
            <div class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                @include('tweets.tweet', ['tweet' => $tweet])
            </div>
        @empty
            <!-- Pesan jika user belum ada tweet -->
            <div class="p-8 text-center text-gray-500">
                <p>Belum ada tweet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
