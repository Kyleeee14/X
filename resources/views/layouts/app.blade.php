<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>@yield('title', 'Vibe')</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}"><link rel="stylesheet" href="{{ asset('css/vibe.css') }}?v=6"><link rel="stylesheet" href="{{ asset('css/vibe-mobile.css') }}?v=1"><link rel="stylesheet" href="{{ asset('css/admin-system.css') }}?v=1"><script defer src="{{ asset('js/vibe.js') }}?v=3"></script><script defer src="{{ asset('js/alpine.min.js') }}"></script></head>
@php($authScreen = request()->routeIs('login', 'register'))
<body class="{{ $authScreen ? 'auth-screen' : '' }}"><a class="skip-link" href="#main">Lewati navigasi</a>
<header class="topbar"><div class="topbar-inner"><a class="brand" href="{{ route('home') }}" aria-label="Vibe — beranda"><span class="brand-v">V</span><span>ibe</span></a>
@unless($authScreen)<form class="global-search" action="{{ route('search') }}"><x-icon name="search"/><input name="q" aria-label="Cari di Vibe" placeholder="Cari di Vibe" value="{{ request('q') }}"></form>@endunless
@auth
<div class="account-menu"><button id="account-toggle" class="account-toggle" aria-expanded="false" aria-controls="account-dropdown" aria-label="Menu akun"><img src="{{ auth()->user()->avatar_url }}" alt=""><span>{{ auth()->user()->name }}</span><x-icon name="down"/></button>
<div id="account-dropdown" class="account-dropdown" hidden><div class="account-summary"><strong class="{{ auth()->user()->is_admin ? 'system-name' : '' }}">{{ auth()->user()->name }} @if(auth()->user()->is_admin)<i class="system-badge">BOT</i>@endif</strong><small>{{ auth()->user()->is_admin ? 'Sistem Vibe' : '@'.auth()->user()->username }}</small></div>
<a href="{{ route('profile.show', auth()->user()) }}"><x-icon name="profile"/>Lihat profil</a><a href="{{ route('profile.edit') }}"><x-icon name="settings"/>Pengaturan</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icon name="logout"/>Keluar</button></form></div></div>
@else<div class="auth-links"><a href="{{ route('login') }}">Masuk</a><a class="primary" href="{{ route('register') }}">Daftar</a></div>@endauth
</div></header>
@php($hasAside = request()->routeIs('home', 'profile.show'))
<div class="app-shell {{ $hasAside ? 'with-aside' : 'wide-page' }} {{ request()->routeIs('messages.*') ? 'messaging-page' : '' }}">
@unless($authScreen) @include('layouts.navigation') @endunless
<main id="main" class="main-column">
@if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert">{{ $errors->first() }}</div>@endif
@yield('content')</main>
@if($hasAside)<aside class="discovery"><section class="side-section"><div class="section-heading"><h2>Topik populer</h2><a class="icon-button" href="{{ route('tags.index') }}" aria-label="Lihat semua topik"><x-icon name="arrow"/></a></div>
@forelse(\App\Models\Tag::orderByDesc('usage_count')->limit(5)->get() as $topic)<a class="topic" href="{{ route('tags.show',$topic) }}"><span class="topic-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span><strong>#{{ $topic->name }}</strong><small>{{ $topic->usage_count }} penggunaan</small></span></a>@empty<p class="muted">Belum ada topik.</p>@endforelse</section>
@auth<section class="side-section"><h2>Temukan orang</h2>@foreach(\App\Models\User::where('id','!=',auth()->id() ?? 0)->where('is_admin',false)->latest()->limit(3)->get() as $person)<a class="person" href="{{ route('profile.show',$person) }}"><img src="{{ $person->avatar_url }}" alt=""><span><strong>{{ $person->name }}</strong><small>{{ '@'.$person->username }}</small></span><x-icon name="chevron"/></a>@endforeach</section>@endauth</aside>@endif
</div><div id="toast" role="status" hidden></div>@yield('scripts')</body></html>
