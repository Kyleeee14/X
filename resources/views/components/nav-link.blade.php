{{-- Component Nav Link untuk Navigasi --}}
{{-- Component ini digunakan untuk membuat link navigasi dengan styling konsisten --}}
{{-- Props: $active (boolean) - untuk menandai link yang aktif --}}

@props(['active'])

{{-- Dynamic class berdasarkan status active --}}
{{-- Jika active true: gunakan style indigo (biru) untuk link aktif --}}
{{-- Jika active false: gunakan style transparent untuk link tidak aktif --}}
@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

{{-- Render link dengan class yang sudah diatur --}}
{{-- $attributes->merge() untuk menggabungkan class default dengan class tambahan --}}
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }} {{-- Slot untuk isi link --}}
</a>
