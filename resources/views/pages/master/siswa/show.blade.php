@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb pageTitle="Detail Data Siswa" pageSubtitle="Informasi lengkap profil dan kontak wali siswa." />

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row gap-8">
                    <!-- Foto Profil -->
                    <div class="flex-shrink-0 flex flex-col items-center">
                        <div class="w-48 h-48 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 border-4 border-white shadow-lg dark:border-gray-700">
                            @if($siswa->foto)
                                <img src="{{ Storage::url($siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('master.siswa.edit', $siswa->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Data
                            </a>
                        </div>
                    </div>

                    <!-- Informasi Detail -->
                    <div class="flex-1 min-w-0">
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $siswa->nama_lengkap }}</h3>
                            <p class="text-brand-500 font-medium mt-1">NISN: {{ $siswa->nisn }} | NIS: {{ $siswa->nis }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Jenis Kelamin</h4>
                                <p class="text-base text-gray-900 dark:text-white font-medium">
                                    {{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </p>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tempat, Tanggal Lahir</h4>
                                <p class="text-base text-gray-900 dark:text-white font-medium">
                                    {{ $siswa->tempat_lahir ?: '-' }}, 
                                    {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d F Y') : '-' }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Alamat Lengkap</h4>
                                <p class="text-base text-gray-900 dark:text-white font-medium">
                                    {{ $siswa->alamat ?: '-' }}
                                </p>
                            </div>

                            <div class="pt-6 border-t border-gray-100 dark:border-gray-800 md:col-span-2">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Informasi Wali</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nama Wali</h4>
                                        <p class="text-base text-gray-900 dark:text-white font-medium">
                                            {{ $siswa->nama_wali ?: '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Nomor HP Wali</h4>
                                        <p class="text-base text-gray-900 dark:text-white font-medium">
                                            {{ $siswa->no_hp_wali ?: '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-b-2xl border-t border-gray-200 dark:border-gray-800 flex justify-between items-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Ditambahkan pada: {{ $siswa->created_at->format('d M Y, H:i') }}
                </div>
                <a href="{{ route('master.siswa.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-gray-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 dark:focus:ring-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection