@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" pageSubtitle="Kelola kategori untuk mengelompokkan berita website." />

<div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>
    <form method="POST" action="{{ $kategori->exists ? route('website.kategori-berita.update', $kategori->id) : route('website.kategori-berita.store') }}">
        @csrf
        @if($kategori->exists)
            @method('PUT')
        @endif
        <div>
            <div class="mb-4.5">
                <label for="nama" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Kategori <span class="text-red-500">*</span></label>
                <input id="nama" name="nama" type="text" maxlength="255" value="{{ old('nama', $kategori->nama) }}" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('nama') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="mb-6">
                <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                @error('deskripsi') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('website.kategori-berita.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">Batal</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">{{ $kategori->exists ? 'Update Data' : 'Simpan Data' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
