@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb pageTitle="Manajemen Presensi Harian" pageSubtitle="Kelola kehadiran harian siswa di sini." />

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
            <form action="{{ route('presensi.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end" id="filterForm">
                <div>
                    <label for="tanggal" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran</label>
                    <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                        <select id="tahun_ajaran_id" name="tahun_ajaran_id" 
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 text-gray-800 dark:text-white/90" 
                            onchange="document.getElementById('rombel_id').value=''; this.form.submit()" @change="isOptionSelected = true">
                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Tahun Ajaran</option>
                            @foreach($tahun_ajarans as $ta)
                                <option value="{{ $ta->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ $tahun_ajaran_id == $ta->id ? 'selected' : '' }}>
                                    {{ $ta->tahun_mulai }}/{{ $ta->tahun_selesai }} - {{ ucfirst($ta->semester) }}
                                    @if($ta->status == 'aktif') (Aktif) @endif
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

                <div>
                    <label for="rombel_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rombel</label>
                    <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                        <select id="rombel_id" name="rombel_id" 
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 text-gray-800 dark:text-white/90" 
                            onchange="this.form.submit()" {{ !$tahun_ajaran_id ? 'disabled' : '' }} @change="isOptionSelected = true">
                            <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Rombel</option>
                            @foreach($rombels as $rombel)
                                <option value="{{ $rombel->id }}" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ $rombel_id == $rombel->id ? 'selected' : '' }}>
                                    Kelas {{ $rombel->tingkat }} - {{ $rombel->nama_rombel }}
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
            <form action="{{ route('presensi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="rombel_id" value="{{ $rombel_id }}">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <!-- Header -->
                    <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                Presensi Kelas: {{ $rombels->where('id', $rombel_id)->first()->nama_rombel ?? '' }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Tanggal: {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</p>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan Kehadiran
                            </button>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    @php
                        $hadir = $presensi_data->where('status', 'hadir')->count();
                        $terlambat = $presensi_data->where('status', 'terlambat')->count();
                        $izin = $presensi_data->where('status', 'izin')->count();
                        $sakit = $presensi_data->where('status', 'sakit')->count();
                        $alpa = $presensi_data->where('status', 'alpa')->count();
                        $belum = $presensi_data->whereNull('status')->count();
                    @endphp
                    
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-0 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                        <div class="p-4 border-r border-b md:border-b-0 border-gray-200 dark:border-gray-800 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Hadir</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $hadir }}</p>
                        </div>
                        <div class="p-4 border-r border-b md:border-b-0 border-gray-200 dark:border-gray-800 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Terlambat</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $terlambat }}</p>
                        </div>
                        <div class="p-4 border-r border-b md:border-b-0 border-gray-200 dark:border-gray-800 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Izin</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $izin }}</p>
                        </div>
                        <div class="p-4 border-r border-b md:border-b-0 border-gray-200 dark:border-gray-800 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Sakit</p>
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $sakit }}</p>
                        </div>
                        <div class="p-4 border-r border-gray-200 dark:border-gray-800 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Alpa</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $alpa }}</p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Belum</p>
                            <p class="text-2xl font-bold text-gray-400">{{ $belum }}</p>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto p-5">
                        <table class="w-full text-left border-collapse data-table-presensi">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-900/50">
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-16">No</th>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Siswa</th>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 text-center" data-orderable="false">Status Kehadiran</th>
                                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-48 text-right" data-orderable="false">Info Scan RFID</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @forelse($presensi_data as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0">
                                                    @if($item->siswa->foto)
                                                        <img src="{{ Storage::url($item->siswa->foto) }}" alt="{{ $item->siswa->nama_lengkap }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-gray-500">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->siswa->nama_lengkap }}</p>
                                                    <p class="text-xs text-gray-500">{{ $item->siswa->nisn }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap justify-center gap-2">
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="presensi[{{ $item->siswa->id }}]" value="hadir" class="peer sr-only" {{ $item->status == 'hadir' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 peer-checked:bg-green-100 peer-checked:text-green-800 peer-checked:border-green-300 dark:peer-checked:bg-green-900/30 dark:peer-checked:text-green-400 dark:peer-checked:border-green-800 transition-colors">
                                                        Hadir
                                                    </div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="presensi[{{ $item->siswa->id }}]" value="terlambat" class="peer sr-only" {{ $item->status == 'terlambat' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 peer-checked:bg-yellow-100 peer-checked:text-yellow-800 peer-checked:border-yellow-300 dark:peer-checked:bg-yellow-900/30 dark:peer-checked:text-yellow-400 dark:peer-checked:border-yellow-800 transition-colors">
                                                        Telat
                                                    </div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="presensi[{{ $item->siswa->id }}]" value="izin" class="peer sr-only" {{ $item->status == 'izin' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 peer-checked:bg-blue-100 peer-checked:text-blue-800 peer-checked:border-blue-300 dark:peer-checked:bg-blue-900/30 dark:peer-checked:text-blue-400 dark:peer-checked:border-blue-800 transition-colors">
                                                        Izin
                                                    </div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="presensi[{{ $item->siswa->id }}]" value="sakit" class="peer sr-only" {{ $item->status == 'sakit' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 peer-checked:bg-orange-100 peer-checked:text-orange-800 peer-checked:border-orange-300 dark:peer-checked:bg-orange-900/30 dark:peer-checked:text-orange-400 dark:peer-checked:border-orange-800 transition-colors">
                                                        Sakit
                                                    </div>
                                                </label>
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="presensi[{{ $item->siswa->id }}]" value="alpa" class="peer sr-only" {{ $item->status == 'alpa' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 peer-checked:bg-red-100 peer-checked:text-red-800 peer-checked:border-red-300 dark:peer-checked:bg-red-900/30 dark:peer-checked:text-red-400 dark:peer-checked:border-red-800 transition-colors">
                                                        Alpa
                                                    </div>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 text-right text-sm">
                                            @if($item->metode == 'rfid')
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-brand-600 dark:text-brand-400">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    RFID: {{ $item->waktu_scan }}
                                                </span>
                                            @elseif($item->status)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Manual
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">
                                                    Belum direkap
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                                <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                </svg>
                                                <p class="text-base font-medium">Belum ada siswa di rombel ini</p>
                                                <p class="text-sm mt-1">Tambahkan siswa terlebih dahulu melalui menu Rombel Siswa.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        @else
            @if($tahun_ajaran_id)
                <div class="flex flex-col items-center justify-center p-12 bg-white border border-gray-200 border-dashed rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-base font-medium text-gray-900 dark:text-white">Pilih Rombel</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Silakan pilih rombel pada dropdown di atas untuk merekap presensi.</p>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-12 bg-white border border-gray-200 border-dashed rounded-2xl dark:bg-white/[0.03] dark:border-gray-800">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-base font-medium text-gray-900 dark:text-white">Pilih Tahun Ajaran</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mulai dengan memilih tahun ajaran dan rombel pada filter di atas.</p>
                </div>
            @endif
        @endif
    </div>
@endsection