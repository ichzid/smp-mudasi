@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb 
            pageTitle="Detail Data Kelas"
            pageSubtitle="Informasi lengkap data kelas."
        />

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-6 sm:p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $rombel->kelas_label }}</h3>
                        <p class="text-brand-500 font-medium mt-1">Tingkat {{ $rombel->tingkat }}</p>
                    </div>
                    <a href="{{ route('master.rombel.edit', $rombel->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Data
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tahun Ajaran</h4>
                        <div class="flex items-center gap-2">
                            <p class="text-base text-gray-900 dark:text-white font-medium">
                                {{ $rombel->tahunAjaran->nama }} - Semester {{ $rombel->tahunAjaran->semester == 1 ? 'Ganjil' : 'Genap' }}
                            </p>
                            @if($rombel->tahunAjaran->status == 'aktif')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    Aktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Wali Kelas</h4>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                                @if($rombel->waliGuru && $rombel->waliGuru->foto)
                                    <img src="{{ Storage::url($rombel->waliGuru->foto) }}" alt="{{ $rombel->waliGuru->nama_lengkap }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-500">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-base text-gray-900 dark:text-white font-medium">
                                    {{ optional($rombel->waliGuru)->nama_lengkap ?? 'Belum ada Wali Kelas' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    NIP. {{ optional($rombel->waliGuru)->nip ?: '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Statistik Kelas</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Placeholder untuk data statistik siswa, bisa diisi nanti saat relasi siswa rombel dibuat -->
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-xl text-center shadow-sm">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Siswa</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">0</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-xl text-center shadow-sm">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Laki-laki</p>
                            <p class="text-2xl font-bold text-brand-500">0</p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-xl text-center shadow-sm">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Perempuan</p>
                            <p class="text-2xl font-bold text-pink-500">0</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-b-2xl border-t border-gray-200 dark:border-gray-800 flex justify-between items-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Ditambahkan pada: {{ $rombel->created_at->format('d M Y, H:i') }}
                </div>
                <a href="{{ route('master.rombel.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-gray-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 dark:focus:ring-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection