@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb pageTitle="Tambah Tahun Ajaran" pageSubtitle="Tambahkan periode tahun ajaran baru." />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('master.tahun-ajaran.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tahun Mulai -->
                    <div>
                        <label for="tahun_mulai" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Mulai <span class="text-red-500">*</span></label>
                        <input type="number" id="tahun_mulai" name="tahun_mulai" value="{{ old('tahun_mulai', date('Y')) }}" required min="2020" max="2100"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('tahun_mulai')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tahun Selesai -->
                    <div>
                        <label for="tahun_selesai" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Selesai <span class="text-red-500">*</span></label>
                        <input type="number" id="tahun_selesai" name="tahun_selesai" value="{{ old('tahun_selesai', date('Y') + 1) }}" required min="2020" max="2100"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('tahun_selesai')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Semester -->
                    <div>
                        <label for="semester" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Semester <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                            <select id="semester" name="semester" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 text-gray-800 dark:text-white/90"
                                @change="isOptionSelected = true">
                                <option value="ganjil" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('semester') == 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="genap" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('semester') == 'genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('semester')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                            <select id="status" name="status" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 text-gray-800 dark:text-white/90"
                                @change="isOptionSelected = true">
                                <option value="aktif" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif (Set Aktif & Nonaktifkan yang lama)</option>
                                <option value="nonaktif" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('status', 'nonaktif') == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('status')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('master.tahun-ajaran.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
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