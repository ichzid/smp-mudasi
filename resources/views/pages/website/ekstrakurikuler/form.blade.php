@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" pageSubtitle="Kelola informasi kegiatan ekstrakurikuler sekolah." />

<div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <form method="POST" enctype="multipart/form-data" action="{{ $ekstrakurikuler->exists ? route('website.ekstrakurikuler.update', $ekstrakurikuler) : route('website.ekstrakurikuler.store') }}">
        @csrf
        @if($ekstrakurikuler->exists) @method('PUT') @endif
        <div>
            <div class="mb-4.5">
                <label for="nama" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Ekstrakurikuler <span class="text-red-500">*</span></label>
                <input id="nama" name="nama" type="text" maxlength="255" required value="{{ old('nama', $ekstrakurikuler->nama) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('nama') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4.5">
                <label for="pembina" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pembina</label>
                <input id="pembina" name="pembina" type="text" maxlength="255" value="{{ old('pembina', $ekstrakurikuler->pembina) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('pembina') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4.5">
                <label for="jadwal" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jadwal</label>
                <input id="jadwal" name="jadwal" type="text" maxlength="255" value="{{ old('jadwal', $ekstrakurikuler->jadwal) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('jadwal') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4.5">
                <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('deskripsi', $ekstrakurikuler->deskripsi) }}</textarea>
                @error('deskripsi') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="mb-4.5">
                <label for="gambar" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Gambar</label>
                <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" aria-label="Pilih gambar ekstrakurikuler">
                <input id="hapus_gambar" name="hapus_gambar" type="hidden" value="{{ old('hapus_gambar', 0) ? 1 : 0 }}">
                <div class="flex flex-wrap gap-3">
                    @if($ekstrakurikuler->gambar)
                        <div id="gambar-lama" class="relative h-28 w-28 {{ old('hapus_gambar') ? 'hidden' : '' }}">
                            <img src="{{ str_starts_with($ekstrakurikuler->gambar, 'website/ekstrakurikuler/') ? asset('storage/'.$ekstrakurikuler->gambar) : asset($ekstrakurikuler->gambar) }}" alt="{{ $ekstrakurikuler->nama }}" class="h-full w-full rounded-lg object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <span style="display:none" class="h-full w-full items-center justify-center rounded-lg bg-gray-100 px-2 text-center text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">Foto tidak tersedia</span>
                            <button id="hapus-gambar-lama" type="button" class="absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500" aria-label="Hapus gambar tersimpan">×</button>
                        </div>
                    @endif
                    <div id="gambar-baru" class="relative hidden h-28 w-28">
                        <img id="gambar-preview" alt="Gambar baru" class="h-full w-full rounded-lg object-cover">
                        <button id="hapus-gambar-baru" type="button" class="absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500" aria-label="Batalkan gambar baru">×</button>
                    </div>
                    <button id="tambah-gambar" type="button" class="{{ $ekstrakurikuler->gambar && ! old('hapus_gambar') ? 'hidden' : 'flex' }} h-28 w-28 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 text-4xl text-gray-500 hover:border-brand-500 hover:text-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-700" aria-label="Tambah gambar ekstrakurikuler" aria-controls="gambar">+</button>
                </div>
                <p id="gambar-status" class="mt-1 {{ old('hapus_gambar') ? '' : 'hidden' }} text-sm text-gray-500 dark:text-gray-400" role="status">Gambar tersimpan akan dihapus setelah Anda menyimpan perubahan.</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WebP; maksimum 2 MB. Klik X untuk menghapus gambar tersimpan sebelum memilih pengganti.</p>
                @error('gambar') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                @error('hapus_gambar') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="mb-6">
                <label for="aktif" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                <div class="relative z-20 bg-transparent">
                    <select id="aktif" name="aktif" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="1" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" @selected((string) old('aktif', $ekstrakurikuler->exists ? (int) $ekstrakurikuler->aktif : 1) === '1')>Aktif</option>
                        <option value="0" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" @selected((string) old('aktif', $ekstrakurikuler->exists ? (int) $ekstrakurikuler->aktif : 1) === '0')>Nonaktif</option>
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                @error('aktif') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('website.ekstrakurikuler.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">Batal</a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">{{ $ekstrakurikuler->exists ? 'Update Data' : 'Simpan Data' }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('gambar');
        const oldPreview = document.getElementById('gambar-lama');
        const newPreview = document.getElementById('gambar-baru');
        const image = document.getElementById('gambar-preview');
        const addButton = document.getElementById('tambah-gambar');
        const removeFlag = document.getElementById('hapus_gambar');
        const status = document.getElementById('gambar-status');
        let objectUrl;

        const render = () => {
            const hasNew = input.files.length > 0;
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = hasNew ? URL.createObjectURL(input.files[0]) : null;
            if (hasNew) image.src = objectUrl;
            else image.removeAttribute('src');
            newPreview.classList.toggle('hidden', !hasNew);
            oldPreview?.classList.toggle('hidden', hasNew || removeFlag.value === '1');
            addButton.classList.toggle('hidden', hasNew || Boolean(oldPreview && removeFlag.value !== '1'));
            addButton.classList.toggle('flex', !hasNew && (!oldPreview || removeFlag.value === '1'));
            status.classList.toggle('hidden', removeFlag.value !== '1' || hasNew);
        };

        addButton.addEventListener('click', () => input.click());
        input.addEventListener('change', render);
        document.getElementById('hapus-gambar-lama')?.addEventListener('click', () => {
            removeFlag.value = '1';
            render();
        });
        document.getElementById('hapus-gambar-baru').addEventListener('click', () => {
            input.value = '';
            render();
        });
        window.addEventListener('pagehide', () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
        });
    });
</script>
@endpush
