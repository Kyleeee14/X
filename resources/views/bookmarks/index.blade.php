@extends('layouts.app')

@section('title', 'Tersimpan · Vibe')

@section('content')
<div class="bookmarks-page">
    <div class="page-heading bookmarks-heading">
        <h1>Tersimpan</h1>
        @if($tweets->total() > 0)
            <span class="bookmarks-count">{{ $tweets->total() }} postingan</span>
        @endif
    </div>

    @if($tweets->isEmpty())
        <section class="bookmarks-empty" aria-labelledby="bookmarks-empty-title">
            <div class="bookmarks-empty-icon"><x-icon name="bookmark"/></div>
            <h2 id="bookmarks-empty-title">Belum ada postingan tersimpan</h2>
            <p>Simpan postingan yang ingin kamu baca lagi. Hanya kamu yang bisa melihat daftar ini.</p>
            <a class="primary" href="{{ route('home') }}">Lihat beranda <x-icon name="arrow"/></a>
        </section>
    @else
        <div class="feed bookmarks-feed">
            @foreach($tweets as $tweet)
                <article class="post-card">@include('tweets.tweet', ['tweet' => $tweet])</article>
            @endforeach
        </div>
        <div class="bookmarks-pagination">{{ $tweets->links() }}</div>
    @endif
</div>
@endsection
