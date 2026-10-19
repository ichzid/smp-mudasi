@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Data Berita & Info" pageSubtitle="Kelola pengumuman, berita, dan informasi untuk website sekolah.">
            <x-slot:action>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('website.kategori-berita.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Kelola Kategori</a>
                    <a href="{{ route('website.berita.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-all hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Berita
                    </a>
                </div>
            </x-slot:action>
        </x-common.page-breadcrumb>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto p-5">
                <table class="w-full text-left border-collapse data-table">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50">
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 w-16">#</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Thumbnail</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 min-w-[300px]">Judul Berita</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Kategori</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400 text-right w-32" data-orderable="false">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($berita as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">{{ $loop->iteration }}</td>
                                <td class="px-5 py-4">
                                    @if($item->gambar)
                                        <div class="h-12 w-15 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                            <img src="{{ str_starts_with($item->gambar, 'website/') ? asset('storage/'.$item->gambar) : asset($item->gambar) }}" alt="Thumbnail" class="h-full w-full object-cover" onerror="this.style.display='none'">
                                        </div>
                                    @else
                                        <div class="h-12 w-15 rounded-lg bg-gray-100 flex items-center justify-center dark:bg-gray-800 text-sm text-gray-500">No Img</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->judul }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->diterbitkan_pada ? $item->diterbitkan_pada->format('d M Y, H:i') : '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-900 dark:text-gray-300">{{ $item->kategori->nama ?? 'Tanpa Kategori' }}</td>
                                <td class="px-5 py-4">
                                    @if($item->status == 'published')
                                        <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">Published</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-50 px-3 py-1 text-sm font-medium text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400">Draft</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('website.berita.edit', $item->id) }}" class="p-2 text-gray-500 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <form action="{{ route('website.berita.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus berita ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-colors" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
