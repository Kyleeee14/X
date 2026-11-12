@extends('layouts.app')

@section('content')
{{-- Halaman Register Vibe --}}
{{-- Halaman ini digunakan untuk user membuat akun baru Vibe --}}

<div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-sm p-4">
        <!-- Logo dan Judul Halaman -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Daftar</h2>
            <p class="text-gray-600 text-sm mt-1">buat akun Vibe Anda</p>
        </div>

        <!-- Form Register -->
        <!-- Form untuk mendaftar akun baru -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Field Nama Lengkap -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required autofocus
                           value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan nama lengkap Anda">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username publik untuk URL/profile dan mention -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" aria-hidden="true">@</span>
                        <input id="username" type="text" name="username" required
                               value="{{ old('username') }}"
                               maxlength="30"
                               pattern="[A-Za-z0-9_]+"
                               autocomplete="username"
                               autocapitalize="none"
                               spellcheck="false"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Username"
                               aria-describedby="username-help">
                    </div>
                    <p id="username-help" class="text-gray-500 text-xs mt-1">Huruf, angka, dan garis bawah. Maksimal 30 karakter.</p>
                    @error('username')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Field Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required
                           value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan email Anda">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Field Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Buat password">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Field Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Konfirmasi password Anda">
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Register -->
                <!-- Tombol untuk submit form register -->
                <button type="submit"
                        class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    Buat akun
                </button>
            </form>
        </div>

        <!-- Link ke Halaman Login -->
        <!-- Untuk user yang sudah punya akun -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Masuk
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
