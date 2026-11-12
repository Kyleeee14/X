<aside class="sidebar"><div class="sidebar-inner"><nav class="main-nav" aria-label="Navigasi utama">
@php($items = [['home','home','Beranda'],['tags.*','explore','Jelajahi'],['messages.*','message','Pesan'],['bookmarks.*','bookmark','Tersimpan']])
@foreach($items as [$pattern,$icon,$label])
@php($name = match($icon) { 'explore'=>'tags.index', 'message'=>'messages.index', 'bookmark'=>'bookmarks.index', default=>'home' })
<a class="{{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($name) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif title="{{ $label }}"><x-icon :name="$icon"/><span>{{ $label }}</span></a>
@endforeach</nav>
<a class="compose-link" href="{{ auth()->check() ? route('home').'#composer' : route('login') }}">
<span class="compose-pattern" aria-hidden="true"><x-icon name="message"/><x-icon name="hash"/><x-icon name="image"/><x-icon name="plus"/><x-icon name="heart"/><x-icon name="comment"/></span>
<span class="compose-label"><x-icon name="edit"/><span>Tulis</span></span>
</a>
</div></aside>
