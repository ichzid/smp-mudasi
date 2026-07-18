@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb pageTitle="Manajemen Rombel Siswa" pageSubtitle="Atur penempatan siswa ke dalam rombongan belajar." />

        @if(session('success'))
            <div class="flex p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                <span class="sr-only">Success</span>
                <div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="flex p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/>
                </svg>
                <span class="sr-only">Error</span>
                <div>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('rombel-siswa.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end" id="filterForm">
                <div class="w-full sm:w-1/3">
                    <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran</label>
                    <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                        <select id="tahun_ajaran_id" name="tahun_ajaran_id" 
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 text-gray-800 dark:text-white/90" 
                            onchange="document.getElementById('rombel_id').value=''; this.form.submit()" @change="isOptionSelected = true">
                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Tahun Ajaran</option>
                            @foreach($tahun_ajarans as $ta)
                                <option value="{{ $ta->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ (string) $tahun_ajaran_id === (string) $ta->id ? 'selected' : '' }}>
                                    {{ $ta->nama }} - Semester {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}
                                    {{ $ta->is_aktif ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="w-full sm:w-1/3">
                    <label for="rombel_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rombel</label>
                    <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                        <select id="rombel_id" name="rombel_id" 
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 text-gray-800 dark:text-white/90 {{ !$tahun_ajaran_id ? 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-800' : '' }}" 
                            onchange="this.form.submit()" {{ !$tahun_ajaran_id ? 'disabled' : '' }} @change="isOptionSelected = true">
                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Rombel</option>
                            @foreach($rombels as $rombel)
                                <option value="{{ $rombel->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ $rombel_id == $rombel->id ? 'selected' : '' }}>
                                    Kelas {{ $rombel->tingkat }} - {{ $rombel->nama }}
                                </option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>
            </form>
        </div>

        @if($rombel_id)
            <!-- Table Container -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <!-- Header -->
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            Anggota Rombel: {{ $rombels->where('id', $rombel_id)->first()->nama ?? '' }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Anggota aktif: {{ $rombel_siswas->whereNull('tanggal_keluar')->count() }} siswa. Histori tetap ditampilkan.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('rombel-siswa.create', ['rombel_id' => $rombel_id]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Siswa ke Rombel
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto p-5">
                    <table class="w-full text-left border-collapse {{ $rombel_siswas->isNotEmpty() ? 'data-table' : '' }}">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-16">No</th>
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">NISN / NIS</th>
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Nama Lengkap</th>
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">L/P</th>
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Tgl. Masuk</th>
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Tgl. Keluar</th>
                                <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 text-right w-24" data-orderable="false">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($rombel_siswas as $index => $rs)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $rs->siswa->nisn }}<br>
                                        <span class="text-xs text-gray-500">{{ $rs->siswa->nis }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                                @if($rs->siswa->foto_url)
                                                    <img src="{{ Storage::url($rs->siswa->foto_url) }}" alt="{{ $rs->siswa->nama_lengkap }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $rs->siswa->nama_lengkap }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $rs->siswa->jenis_kelamin }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $rs->tanggal_masuk->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                        {{ $rs->tanggal_keluar?->format('d M Y') ?? 'Aktif' }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if($rs->tanggal_keluar === null)
                                            <form action="{{ route('rombel-siswa.destroy', $rs->id) }}" method="POST" class="inline-flex items-center gap-2" onsubmit="return confirm('Tutup keanggotaan siswa ini? Histori tidak akan dihapus.');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="date" name="tanggal_keluar" value="{{ date('Y-m-d') }}" min="{{ $rs->tanggal_masuk->format('Y-m-d') }}" required class="h-9 rounded-lg border border-gray-300 bg-transparent px-2 text-xs text-gray-800 dark:border-gray-700 dark:text-white/90">
                                                <button type="submit" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors" title="Tutup Keanggotaan">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-500">Histori</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                            <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            <p class="text-base font-medium">Belum ada siswa di rombel ini</p>
                                            <p class="text-sm mt-1">Silakan klik "Tambah Siswa ke Rombel" untuk memulai.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @if($tahun_ajaran_id)
                <div class="flex flex-col items-center justify-center p-12 bg-white border border-gray-200 border-dashed rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-base font-medium text-gray-900 dark:text-white">Pilih Rombel</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Silakan pilih rombel pada dropdown di atas untuk melihat atau mengelola anggotanya.</p>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-12 bg-white border border-gray-200 border-dashed rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-base font-medium text-gray-900 dark:text-white">Pilih Tahun Ajaran</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mulai dengan memilih tahun ajaran aktif pada dropdown filter di atas.</p>
                </div>
            @endif
        @endif
    </div>
@endsection