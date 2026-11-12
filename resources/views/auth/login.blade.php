@extends('layouts.app')

@section('content')
{{-- Halaman Login Vibe --}}
{{-- Halaman ini digunakan untuk user masuk ke akun Vibe mereka --}}

<div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-sm p-4">
        <!-- Logo dan Judul Halaman -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center mb-3">
                <!-- Logo Vibe dengan icon chat -->
                
                <span class="text-xl font-bold text-gray-900 ml-2">Vibe</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Masuk</h2>
            <p class="text-gray-600 text-sm mt-1">ke akun Vibe Anda</p>
        </div>

        <!-- Form Login -->
        <!-- Form untuk memasukkan email dan password -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Field Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required autofocus
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
                           placeholder="Masukkan password Anda">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Opsi Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                    
                    <!-- Link Lupa Password -->
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-500">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <!-- Tombol Login -->
                <!-- Tombol untuk submit form login -->
                <button type="submit"
                        class="w-full py-2 px-4 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    Masuk
                </button>
            </form>
        </div>

        <!-- Link ke Halaman Register -->
        <!-- Untuk user yang belum punya akun -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Belum punya akun? 
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Daftar sekarang
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
