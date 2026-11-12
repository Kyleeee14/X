@extends('layouts.app')
@section('title','Pengaturan · Vibe')
@section('content')
<div class="page-heading"><h1>Pengaturan</h1><a class="secondary" href="{{ route('profile.show',$user) }}">Lihat profil <x-icon name="arrow"/></a></div>
<div class="settings-layout"><nav class="settings-nav" aria-label="Bagian pengaturan"><a href="#profile-info"><x-icon name="profile"/>Profil</a><a href="#password"><x-icon name="lock"/>Kata sandi</a><a href="#delete-account"><x-icon name="settings"/>Akun</a></nav>
<div class="settings-content"><section class="settings-section" id="profile-info">@include('profile.partials.update-profile-information-form')</section><section class="settings-section" id="password">@include('profile.partials.update-password-form')</section><section class="settings-section danger-section" id="delete-account">@include('profile.partials.delete-user-form')</section></div></div>
@endsection