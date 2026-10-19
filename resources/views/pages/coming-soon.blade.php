@extends('layouts.app')
@section('title', 'Coming Soon')
@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
    <div class="rounded-full bg-blue-50 dark:bg-blue-900/20 p-6 mb-6">
        <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-4">Fitur Sedang Dibangun</h1>
    <p class="text-gray-500 dark:text-gray-400 max-w-md">
        Halaman kelola CMS (Content Management System) ini masih dalam tahap pengembangan. Silakan kembali lagi nanti.
    </p>
    <a href="{{ url('/') }}" class="mt-8 px-6 py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-medium transition-colors">
        Kembali ke Dashboard
    </a>
</div>
@endsection