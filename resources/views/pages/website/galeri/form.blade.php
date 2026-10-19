@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb :pageTitle="$title" pageSubtitle="Kelola album dan foto galeri sekolah." />

<div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 font-medium text-black dark:text-white">{{ $title }}</h3>
    <form id="form-galeri" method="POST" enctype="multipart/form-data" action="{{ $galeri->exists ? route('website.galeri.update', $galeri) : route('website.galeri.store') }}">
        @csrf
        @if($galeri->exists) @method('PUT') @endif
        <div class="mb-4.5">
            <label for="judul" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Judul Album <span class="text-red-500">*</span></label>
            <input id="judul" name="judul" type="text" maxlength="255" required value="{{ old('judul', $galeri->judul) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
            @error('judul') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4.5">
            <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">{{ old('deskripsi', $galeri->deskripsi) }}</textarea>
            @error('deskripsi') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4.5">
            <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Galeri</span>
            <input id="gambar_baru" name="gambar_baru[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="sr-only" aria-label="Pilih foto galeri baru">
            <div id="preview-galeri" class="flex flex-wrap gap-3">
                @if($galeri->gambar_sampul && (! $galeri->exists || ! $galeri->foto->contains('foto', $galeri->gambar_sampul)))
                    <div id="preview-gambar-utama" class="relative h-28 w-28" title="Gambar sampul">
                        <img src="{{ str_starts_with($galeri->gambar_sampul, 'website/galeri/') ? asset('storage/'.$galeri->gambar_sampul) : asset($galeri->gambar_sampul) }}" alt="Gambar sampul {{ $galeri->judul }}" class="h-full w-full rounded-lg object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <span style="display:none" class="h-full w-full items-center justify-center rounded-lg bg-gray-100 px-2 text-center text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">Foto tidak tersedia</span>
                        <button id="hapus-gambar-utama" type="button" class="absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500" aria-label="Hapus gambar sampul {{ $galeri->judul }}">×</button>
                    </div>
                    <input id="hapus_gambar_utama" name="hapus_gambar_utama" type="checkbox" value="1" class="hidden">
                @endif
                @foreach($galeri->exists ? $galeri->foto : [] as $foto)
                    <div class="relative h-28 w-28" title="Foto galeri {{ $foto->id }}">
                        <img src="{{ str_starts_with($foto->foto, 'website/galeri/') ? asset('storage/'.$foto->foto) : asset($foto->foto) }}" alt="Foto galeri {{ $foto->id }}" class="h-full w-full rounded-lg object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                        <span style="display:none" class="h-full w-full items-center justify-center rounded-lg bg-gray-100 px-2 text-center text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">Foto tidak tersedia</span>
                        <button type="submit" form="hapus-foto-{{ $foto->id }}" class="absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500" aria-label="Hapus foto galeri {{ $foto->id }}">×</button>
                    </div>
                @endforeach
                <button id="tambah-gambar-galeri" type="button" class="flex h-28 w-28 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 text-4xl text-gray-500 hover:border-brand-500 hover:text-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-700" aria-label="Tambah foto galeri" aria-controls="gambar_baru">+</button>
            </div>
            <p id="gambar-utama-status" class="mt-1 hidden text-sm text-gray-500 dark:text-gray-400" role="status">Gambar sampul akan dihapus setelah Anda klik Update Data.</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WebP; maksimum 2 MB per foto, maksimal 20 foto baru. Klik X untuk menghapus foto tersimpan.</p>
            <p id="gambar-baru-status" class="mt-1 hidden text-sm text-red-500" role="alert"></p>
            @error('gambar_baru') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            @foreach($errors->get('gambar_baru.*') as $messages)
                @foreach($messages as $message)
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @endforeach
            @endforeach
        </div>
        <div class="flex justify-end gap-3 mt-8">
            <a href="{{ route('website.galeri.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-lg hover:bg-brand-600">{{ $galeri->exists ? 'Update Data' : 'Simpan Data' }}</button>
        </div>
    </form>
</div>

@if($galeri->exists)
    @foreach($galeri->foto as $foto)
        <form id="hapus-foto-{{ $foto->id }}" action="{{ route('website.galeri.foto.destroy', [$galeri, $foto]) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('gambar_baru');
        const addButton = document.getElementById('tambah-gambar-galeri');
        const preview = document.getElementById('preview-galeri');
        const status = document.getElementById('gambar-baru-status');
        document.getElementById('hapus-gambar-utama')?.addEventListener('click', () => {
            if (confirm('Hapus gambar sampul ini setelah menyimpan perubahan?')) {
                document.getElementById('hapus_gambar_utama').checked = true;
                document.getElementById('preview-gambar-utama').classList.add('hidden');
                document.getElementById('gambar-utama-status').classList.remove('hidden');
            }
        });
        const showLimit = () => {
            if (window.Swal?.fire) {
                window.Swal.fire({ icon: 'warning', text: 'Maksimal 20 foto baru.' });
            } else {
                status.textContent = 'Maksimal 20 foto baru.';
                status.classList.remove('hidden');
            }
        };
        let selected = [];
        let objectUrls = [];
        const syncInput = () => {
            const transfer = new DataTransfer();
            selected.forEach(file => transfer.items.add(file));
            input.files = transfer.files;
        };
        const render = () => {
            preview.querySelectorAll('[data-new-photo]').forEach(element => element.remove());
            objectUrls.forEach(url => URL.revokeObjectURL(url));
            objectUrls = [];
            selected.forEach((file, index) => {
                const url = URL.createObjectURL(file);
                objectUrls.push(url);
                const wrapper = document.createElement('div');
                wrapper.className = 'relative h-28 w-28';
                wrapper.dataset.newPhoto = '';
                const image = document.createElement('img');
                image.src = url;
                image.alt = file.name;
                image.className = 'h-full w-full rounded-lg object-cover';
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500';
                remove.textContent = '×';
                remove.setAttribute('aria-label', `Hapus foto baru ${file.name}`);
                remove.addEventListener('click', () => {
                    selected.splice(index, 1);
                    syncInput();
                    render();
                });
                wrapper.append(image, remove);
                preview.insertBefore(wrapper, addButton);
            });
        };
        addButton.addEventListener('click', () => {
            if (selected.length >= 20) {
                showLimit();
                return;
            }
            input.click();
        });
        input.addEventListener('change', () => {
            const incoming = Array.from(input.files);
            if (selected.length + incoming.length > 20) {
                showLimit();
            } else {
                selected.push(...incoming);
                status.classList.add('hidden');
            }
            syncInput();
            render();
        });
        window.addEventListener('pagehide', () => objectUrls.forEach(url => URL.revokeObjectURL(url)));
    });
</script>
@endpush
