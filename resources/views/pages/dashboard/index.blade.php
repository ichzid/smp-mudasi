@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Greetings -->
        <x-common.page-breadcrumb pageTitle="Selamat Datang di Sistem Informasi Sekolah" pageSubtitle="Ringkasan data dan aktivitas hari ini: {{ \Carbon\Carbon::parse($today)->format('l, d F Y') }}" />
        
        <div class="flex justify-end mb-4">
            <a href="{{ route('presensi.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                Rekap Presensi
            </a>
        </div>

        <!-- Master Data Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Siswa -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Siswa</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSiswa) }}</h3>
                </div>
            </div>

            <!-- Guru -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Guru</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalGuru) }}</h3>
                </div>
            </div>

            <!-- Rombel -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Rombel</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRombel) }}</h3>
                </div>
            </div>
        </div>

        <!-- Today Presensi Overview -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Rekap Kehadiran Hari Ini</h3>
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium {{ $persentaseHadir >= 90 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                    {{ $persentaseHadir }}% Hadir
                </span>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-5 divide-x divide-y md:divide-y-0 divide-gray-200 dark:divide-gray-800">
                <div class="p-6 text-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Hadir Tepat Waktu</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $hadir }}</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Terlambat</p>
                    <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $terlambat }}</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Izin</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $izin }}</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Sakit</p>
                    <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ $sakit }}</p>
                </div>
                <div class="p-6 text-center">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Alpa / Tanpa Keterangan</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $alpa }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection