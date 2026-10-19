@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Data Fasilitas" pageSubtitle="Kelola fasilitas dan foto untuk website sekolah.">
            <x-slot:action>
                <a href="{{ route('website.fasilitas.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Fasilitas
                </a>
            </x-slot:action>
        </x-common.page-breadcrumb>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto p-5">
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-16">#</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Gambar</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Nama Fasilitas</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Urutan</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 text-right w-32" data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($fasilitas as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">{{ $loop->iteration }}</td>
                                <td class="px-5 py-4">
                                    @if($item->gambar)
                                        <img src="{{ str_starts_with($item->gambar, 'website/fasilitas/') ? asset('storage/'.$item->gambar) : asset($item->gambar) }}" alt="{{ $item->nama }}" class="h-12 w-16 rounded-lg object-cover">
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->nama }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->foto_count }} foto</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">{{ $item->urutan }}</td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">{{ $item->aktif ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('website.fasilitas.edit', $item) }}" class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-500 dark:hover:bg-blue-500/10" title="Edit / Foto" aria-label="Edit / Foto {{ $item->nama }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('website.fasilitas.destroy', $item) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus fasilitas beserta seluruh fotonya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-500/10" title="Hapus" aria-label="Hapus {{ $item->nama }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
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
