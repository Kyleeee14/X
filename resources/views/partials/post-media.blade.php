@if($tweet->media)
<div class="media-grid media-count-{{ min(count($tweet->media), 4) }}">
@foreach($tweet->media as $item)
@if($item['type'] === 'video')
<video controls playsinline preload="metadata" src="{{ $item['url'] }}"></video>
@else
<a href="{{ $item['url'] }}" target="_blank" rel="noopener"><img loading="lazy" src="{{ $item['url'] }}" alt="Foto postingan {{ $loop->iteration }}"></a>
@endif
@endforeach
</div>
@endif
