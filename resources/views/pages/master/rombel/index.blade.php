@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Data Kelas" pageSubtitle="Pengelolaan Data Kelas (Rombongan Belajar) dan pengaturan Wali Kelas per Tahun Ajaran.">
            <x-slot:action>
                <a href="{{ route('master.rombel.salin.create') }}" class="mr-2 inline-flex items-center justify-center rounded-lg border border-brand-500 px-4 py-2.5 text-sm font-medium text-brand-500 transition-all hover:bg-brand-50 dark:hover:bg-brand-500/10">
                    Salin Struktur Tahun Sebelumnya
                </a>
                @if($tahun_ajarans->firstWhere('id', $tahun_ajaran_id)?->is_aktif)
                    <a href="{{ route('master.rombel.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Kelas
                    </a>
                @endif
            </x-slot:action>
        </x-common.page-breadcrumb>

        <form action="{{ route('master.rombel.index') }}" method="GET" class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <label for="tahun_ajaran_id" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran</label>
            <div class="relative mt-2 w-full sm:w-80">
                <select id="tahun_ajaran_id" name="tahun_ajaran_id" onchange="this.form.submit()" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white bg-none px-4 pr-11 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($tahun_ajarans as $ta)
                        <option value="{{ $ta->id }}" {{ (string) $tahun_ajaran_id === (string) $ta->id ? 'selected' : '' }}>{{ $ta->nama }} ({{ $ta->is_aktif ? 'Aktif - ' . ($ta->semester == 1 ? 'Ganjil' : 'Genap') : 'Arsip' }})</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </div>
        </form>

        <!-- Table Container -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <!-- Table -->
            <div class="overflow-x-auto p-5">
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-16">#</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Tingkat</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Nama Kelas</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Wali Kelas</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 text-right w-24" data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($rombels as $index => $rombel)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $rombel->tahunAjaran->nama }} - {{ $rombel->semester === null ? 'Tahunan' : 'Snapshot ' . ($rombel->semester == 1 ? 'Ganjil' : 'Genap') }}
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $rombel->tahunAjaran->is_aktif ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                        {{ $rombel->tahunAjaran->is_aktif ? 'Periode aktif' : 'Arsip' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    Tingkat {{ $rombel->tingkat }}
                                </td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $rombel->kelas_label }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    {{ optional($rombel->waliGuru)->nama_lengkap }}
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('master.rombel.show', $rombel->id) }}" class="p-2 text-gray-500 hover:text-blue-500 rounded-lg" title="Detail">Detail</a>
                                        @if($rombel->tahunAjaran->is_aktif && $rombel->semester === null)
                                        <a href="{{ route('master.rombel.edit', $rombel->id) }}" class="p-2 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ route('master.rombel.destroy', $rombel->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kelas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection