@extends('layouts.app')
@section('title', 'Percakapan — Vibe')
@section('content')
<section class="comments-page">
    <header class="comments-heading"><a class="icon-button" href="{{ route('home') }}" aria-label="Kembali ke beranda"><x-icon name="back"/></a><div><h1>Percakapan</h1><p>{{ $tweet->quotes->count() }} komentar</p></div></header>
    <article class="thread-original">
        <div class="comment-author"><a href="{{ route('profile.show', $tweet->user) }}"><img src="{{ $tweet->user->avatar_url }}" alt=""></a><div><a href="{{ route('profile.show', $tweet->user) }}"><strong class="{{ $tweet->user->is_admin ? 'system-name' : '' }}">{{ $tweet->user->name }}</strong>@if($tweet->user->is_admin)<span class="system-badge">BOT</span>@endif</a><span>{{ $tweet->user->is_admin ? 'Sistem Vibe' : '@'.$tweet->user->username }} · {{ $tweet->created_at->diffForHumans() }}</span></div></div>
        <div class="thread-original-content">{!! $tweet->getContentWithParsedTags() !!}</div>
        @if($tweet->image)<img class="thread-media" src="{{ $tweet->image }}" alt="Gambar postingan">@endif
        @include('partials.post-media')
        <div class="thread-summary"><span><x-icon name="comment"/> {{ $tweet->quotes->count() }} komentar</span><span><x-icon name="heart"/> {{ $tweet->likes->count() }} suka</span></div>
    </article>
    @auth
    <form class="comment-composer" method="POST" action="{{ route('tweets.store') }}" enctype="multipart/form-data">@csrf<input type="hidden" name="quote_tweet_id" value="{{ $tweet->id }}"><img src="{{ auth()->user()->avatar_url }}" alt=""><div><textarea name="content" maxlength="280" rows="3" required placeholder="Tulis komentar…">{{ old('content') }}</textarea><div class="comment-form-footer"><label class="comment-file" title="Tambahkan gambar"><x-icon name="image"/><span>Gambar</span><input type="file" name="image" accept="image/jpeg,image/png,image/gif"></label><button class="primary" type="submit">Kirim</button></div></div></form>
    @else<div class="comment-login"><a href="{{ route('login') }}">Masuk</a> untuk ikut dalam percakapan.</div>@endauth
    <div class="comments-list" aria-label="Daftar komentar">
        @forelse($tweet->quotes as $comment) @include('partials.comment', ['comment' => $comment, 'rootTweet' => $tweet, 'depth' => 0])
        @empty<div class="comments-empty"><x-icon name="comment"/><h2>Belum ada komentar</h2><p>Jadilah orang pertama yang menanggapi postingan ini.</p></div>@endforelse
    </div>
</section>
@endsection
