@extends('layouts.app')

@section('content')
<div class="feed-page">
    <div class="page-heading"><h1>Beranda</h1></div>
    <div class="feed-tabs"><a class="{{ request('feed') !== 'following' ? 'selected' : '' }}" href="{{ route('home') }}">Untuk kamu</a><a class="{{ request('feed') === 'following' ? 'selected' : '' }}" href="{{ route('home', ['feed'=>'following']) }}">Mengikuti</a></div>

    @auth
    <div id="composer" class="composer">
        <form action="{{ route('tweets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="composer-main"><img src="{{ auth()->user()->avatar_url }}" alt=""><textarea name="content" rows="3" maxlength="280" placeholder="Apa yang lagi kamu pikirin?">{{ old('content') }}</textarea></div>
            @error('content')<p class="media-error">{{ $message }}</p>@enderror
            <div id="media-preview" class="media-preview" hidden></div>
            <div class="composer-footer">
                <label for="tweet_media" class="media-picker"><x-icon name="image"/><span>Foto, GIF, atau video</span><input type="file" id="tweet_media" name="media[]" multiple accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm"></label>
                <span id="media-count" class="muted"></span><span class="muted"><span id="char-count">{{ mb_strlen(old('content', '')) }}</span>/280</span><button type="submit" class="primary">Posting</button>
            </div>
            @error('media')<p class="media-error">{{ $message }}</p>@enderror
            @error('media.*')<p class="media-error">{{ $message }}</p>@enderror
        </form>
    </div>
    @endauth

    <div class="feed">
        @forelse($tweets as $tweet)<div class="post-card">@include('tweets.tweet', ['tweet' => $tweet])</div>
        @empty<div class="empty-state"><x-icon name="message"/><h2>Belum ada postingan</h2><p>Mulai percakapan pertama di Vibe.</p></div>@endforelse
        <div class="py-4">{{ $tweets->links() }}</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.querySelector('#composer textarea[name="content"]');
    const charCount = document.getElementById('char-count');
    const fileInput = document.getElementById('tweet_media');
    const preview = document.getElementById('media-preview');
    const count = document.getElementById('media-count');
    let files = [];
    textarea?.addEventListener('input', () => { charCount.textContent = textarea.value.length; charCount.classList.toggle('text-red-500', textarea.value.length > 260); });
    function sync() { const transfer = new DataTransfer(); files.forEach(file => transfer.items.add(file)); fileInput.files = transfer.files; }
    function draw() {
        preview.innerHTML = ''; preview.hidden = files.length === 0; count.textContent = files.length ? `${files.length}/8 file` : '';
        files.forEach((file, index) => {
            const item = document.createElement('div'); item.className = 'media-preview-item';
            const url = URL.createObjectURL(file); const media = document.createElement(file.type.startsWith('video/') ? 'video' : 'img'); media.src = url;
            if (media.tagName === 'VIDEO') { media.muted = true; media.playsInline = true; }
            media.addEventListener(media.tagName === 'VIDEO' ? 'loadedmetadata' : 'load', () => URL.revokeObjectURL(url), {once:true});
            const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'media-preview-remove'; remove.textContent = '×'; remove.setAttribute('aria-label', `Hapus ${file.name}`);
            remove.addEventListener('click', () => { files.splice(index, 1); sync(); draw(); }); item.append(media, remove); preview.append(item);
        });
    }
    fileInput?.addEventListener('change', event => {
        files = [...files, ...event.target.files].filter((file, index, all) => all.findIndex(other => other.name === file.name && other.size === file.size && other.lastModified === file.lastModified) === index).slice(0, 8);
        sync(); draw();
    });
});
</script>
@endsection
