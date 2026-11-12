@php($depth = $depth ?? 0)
<div class="comment-node" id="comment-{{ $comment->id }}" data-depth="{{ $depth }}">
<article class="comment-card">
    <div class="comment-author"><a href="{{ route('profile.show', $comment->user) }}"><img src="{{ $comment->user->avatar_url }}" alt=""></a><div><a href="{{ route('profile.show', $comment->user) }}"><strong class="{{ $comment->user->is_admin ? 'system-name' : '' }}">{{ $comment->user->name }}</strong>@if($comment->user->is_admin)<span class="system-badge">BOT · ADMIN</span>@endif</a><span>{{ $comment->user->is_admin ? 'Sistem Vibe' : '@'.$comment->user->username }} · {{ $comment->created_at->diffForHumans() }}</span></div></div>
    <div class="comment-body">{!! $comment->getContentWithParsedTags() !!}</div>
    @if($comment->image)<img class="comment-media" src="{{ $comment->image }}" alt="Gambar komentar">@endif
    <div class="comment-actions">
    @auth
        <form method="POST" action="{{ route('tweets.like', $comment) }}">@csrf<button type="submit" class="{{ $comment->likes->contains('user_id', auth()->id()) ? 'liked' : '' }}" aria-label="Sukai komentar"><x-icon name="heart"/><span>{{ $comment->likes->count() }}</span></button></form>
        <details class="comment-disclosure"><summary><x-icon name="reply"/><span>Balas</span>@if($comment->replies->count())<b>{{ $comment->replies->count() }}</b>@endif</summary><form class="inline-comment-form" method="POST" action="{{ route('tweets.store') }}" enctype="multipart/form-data">@csrf<input type="hidden" name="quote_tweet_id" value="{{ $rootTweet->id }}"><input type="hidden" name="parent_comment_id" value="{{ $comment->id }}"><textarea name="content" maxlength="280" rows="2" required placeholder="Balas {{ $comment->user->name }}…"></textarea><div class="comment-form-footer"><label class="comment-file" title="Tambahkan gambar"><x-icon name="image"/><input type="file" name="image" accept="image/jpeg,image/png,image/gif"></label><button class="primary" type="submit">Balas</button></div></form></details>
        @if($comment->user_id === auth()->id())
        <details class="comment-disclosure comment-edit"><summary aria-label="Edit komentar" title="Edit"><x-icon name="edit"/></summary><form class="inline-comment-form" method="POST" action="{{ route('tweets.update', $comment) }}" enctype="multipart/form-data">@csrf @method('PUT')<textarea name="content" maxlength="280" rows="3" required>{{ $comment->content }}</textarea><div class="comment-form-footer"><label class="comment-file"><x-icon name="image"/><input type="file" name="image" accept="image/jpeg,image/png,image/gif"></label><button class="primary" type="submit">Simpan</button></div></form></details>
        <form method="POST" action="{{ route('tweets.destroy', $comment) }}" onsubmit="return confirm('Hapus komentar ini?')">@csrf @method('DELETE')<button type="submit" aria-label="Hapus komentar" title="Hapus"><x-icon name="trash"/></button></form>
        @endif
    @else<a href="{{ route('login') }}"><x-icon name="reply"/><span>Balas</span></a>@endauth
    </div>
</article>
@if($comment->replies->isNotEmpty())<div class="comment-children">@foreach($comment->replies as $reply) @include('partials.comment', ['comment' => $reply, 'rootTweet' => $rootTweet, 'depth' => $depth + 1]) @endforeach</div>@endif
</div>
