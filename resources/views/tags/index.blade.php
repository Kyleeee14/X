@extends('layouts.app')
@section('title','Jelajahi · Vibe')
@section('content')
<div class="page-heading"><h1>Jelajahi</h1><x-icon name="explore"/></div>
<div class="explore-layout"><section class="explore-topics"><div class="section-heading"><h2>Topik populer</h2><span class="muted">{{ $popularTags->count() }} topik</span></div>
@forelse($popularTags as $tag)<a class="explore-topic" href="{{ route('tags.show',$tag) }}"><span class="topic-number">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><span><strong>#{{ $tag->name }}</strong><small>{{ $tag->usage_count }} penggunaan</small></span><x-icon name="arrow"/></a>@empty<div class="empty-state"><x-icon name="hash"/><h2>Belum ada topik</h2><p>Tambahkan hashtag pada postingan untuk memulai topik.</p></div>@endforelse</section>
<aside class="explore-trending"><h2>Sedang ramai</h2><div class="tag-cloud">@forelse($trendingTags as $tag)<a href="{{ route('tags.show',$tag) }}">#{{ $tag->name }}</a>@empty<p class="muted">Belum ada topik baru.</p>@endforelse</div></aside></div>
@endsection