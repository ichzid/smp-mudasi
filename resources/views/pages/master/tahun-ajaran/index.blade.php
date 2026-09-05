@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Data Tahun Ajaran" pageSubtitle="Kelola daftar periode tahun ajaran dan status semester aktif.">
            <x-slot:action>
                <a href="{{ route('master.tahun-ajaran.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Tahun Ajaran
                </a>
            </x-slot:action>
        </x-common.page-breadcrumb>

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

        <!-- Table Container -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <!-- Table -->
            <div class="overflow-x-auto p-5">
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-16">#</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Tahun Ajaran</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Semester</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 text-right w-24" data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($tahunAjarans as $index => $ta)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $ta->nama }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    {{ $ta->semester == 1 ? 'Ganjil' : 'Genap' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">
                                    @if($ta->is_aktif)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400">
                                            Non-aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('master.tahun-ajaran.edit', $ta->id) }}" class="p-2 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        @if(!$ta->is_aktif)
                                            <form action="{{ route('master.tahun-ajaran.destroy', $ta->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini? Pastikan tidak ada data yang masih terhubung.');">
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