@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb 
            pageTitle="Edit Kartu RFID" 
            pageSubtitle="Ubah informasi kartu RFID siswa." 
            :breadcrumbs="[
                ['label' => 'Data Kartu RFID', 'url' => route('kartu-rfid.index')]
            ]"
        />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                    @if($kartu->siswa->foto_url)
                        <img src="{{ Storage::url($kartu->siswa->foto_url) }}" alt="{{ $kartu->siswa->nama_lengkap }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pemilik Kartu:</p>
                    <p class="text-base font-bold text-gray-900 dark:text-white">{{ $kartu->siswa->nama_lengkap }} (NISN: {{ $kartu->siswa->nisn }})</p>
                </div>
            </div>

            <form action="{{ route('kartu-rfid.update', $kartu->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode UID RFID -->
                    <div>
                        <label for="kode_uid" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode UID Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m8-2h3m-3 3h3m-4 3v6m4-3H8M19 4v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"/>
                                </svg>
                            </div>
                            <input type="text" id="kode_uid" name="kode_uid" value="{{ old('kode_uid', $kartu->kode_uid) }}" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-10 font-mono text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Gunakan scanner RFID untuk mengisi UID atau ketik manual.</p>
                        @error('kode_uid')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                            <select id="status" name="status" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 text-gray-800 dark:text-white/90"
                                @change="isOptionSelected = true">
                                <option value="aktif" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('status', $kartu->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('status', $kartu->status) == 'nonaktif' ? 'selected' : '' }}>Non-aktif</option>
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
                    <a href="{{ route('kartu-rfid.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-700 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                        Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection