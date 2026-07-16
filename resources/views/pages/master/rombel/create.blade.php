@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb pageTitle="Tambah Data Rombel" pageSubtitle="Tambahkan rombongan belajar baru ke dalam sistem." />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('master.rombel.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tahun Ajaran -->
                    <div>
                        <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select id="tahun_ajaran_id" name="tahun_ajaran_id" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Tahun Ajaran</option>
                                @foreach($tahun_ajarans as $ta)
                                    <option value="{{ $ta->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('tahun_ajaran_id') == $ta->id || ($ta->status == 'aktif' && !old('tahun_ajaran_id')) ? 'selected' : '' }}>
                                        {{ $ta->tahun_mulai }}/{{ $ta->tahun_selesai }} - {{ ucfirst($ta->semester) }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('tahun_ajaran_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tingkat -->
                    <div>
                        <label for="tingkat" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tingkat Kelas <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select id="tingkat" name="tingkat" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Tingkat</option>
                                <option value="7" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('tingkat') == '7' ? 'selected' : '' }}>Kelas 7</option>
                                <option value="8" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('tingkat') == '8' ? 'selected' : '' }}>Kelas 8</option>
                                <option value="9" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('tingkat') == '9' ? 'selected' : '' }}>Kelas 9</option>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('tingkat')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Rombel -->
                    <div>
                        <label for="nama_rombel" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Rombel <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_rombel" name="nama_rombel" value="{{ old('nama_rombel') }}" required placeholder="Contoh: 7A, 8B, 9-Alpha"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('nama_rombel')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Wali Kelas -->
                    <div>
                        <label for="wali_kelas_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Wali Kelas <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                            <select id="wali_kelas_id" name="wali_kelas_id" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Wali Kelas</option>
                                @foreach($gurus as $guru)
                                    <option value="{{ $guru->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama_lengkap }} ({{ $guru->nip ?: 'Non-NIP' }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('wali_kelas_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('master.rombel.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection