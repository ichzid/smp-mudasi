@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="{{ $title }}" pageSubtitle="Lengkapi formulir di bawah ini untuk menyimpan data berita." />

<div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
    <h3 class="mb-6 text-lg font-semibold text-gray-800 dark:text-white/90">Formulir Berita</h3>
    <form id="berita-form" action="{{ $berita->exists ? route('website.berita.update', $berita->id) : route('website.berita.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($berita->exists)
            @method('PUT')
        @endif
        
        <div>
            <div class="mb-4.5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Judul Berita <span class="text-red-500">*</span>
                </label>
                <input type="text" name="judul" value="{{ old('judul', $berita->judul) }}" placeholder="Masukkan judul berita" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>
                @error('judul')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4.5 flex flex-col gap-6 xl:flex-row">
                <div class="w-full xl:w-1/2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Kategori
                    </label>
                    <div class="relative z-20 bg-transparent">
                        <select name="kategori_berita_id" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">-- Pilih Kategori (Opsional) --</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_berita_id', $berita->kategori_berita_id) == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                    @error('kategori_berita_id')
                        <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div class="w-full xl:w-1/2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative z-20 bg-transparent">
                        <select name="status" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>
                            <option value="published" {{ old('status', $berita->status) == 'published' ? 'selected' : '' }}>Published (Terbit)</option>
                            <option value="draft" {{ old('status', $berita->status) == 'draft' ? 'selected' : '' }}>Draft (Konsep)</option>
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                    @error('status')
                        <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Gambar Thumbnail
                </label>
                <input id="gambar" type="file" name="gambar" accept="image/jpeg,image/png,image/webp" class="sr-only" aria-label="Pilih thumbnail berita">
                <input id="hapus_gambar" type="hidden" name="hapus_gambar" value="{{ old('hapus_gambar', 0) ? 1 : 0 }}">
                <div class="flex flex-wrap gap-3">
                    @if($berita->gambar)
                        <div id="thumbnail-lama" class="relative h-28 w-28 {{ old('hapus_gambar') ? 'hidden' : '' }}">
                            <img src="{{ str_starts_with($berita->gambar, 'website/') ? asset('storage/'.$berita->gambar) : asset($berita->gambar) }}" alt="Thumbnail {{ $berita->judul }}" class="h-full w-full rounded-lg object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <span style="display:none" class="h-full w-full items-center justify-center rounded-lg bg-gray-100 px-2 text-center text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">Foto tidak tersedia</span>
                            <button id="hapus-thumbnail-lama" type="button" class="absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500" aria-label="Hapus thumbnail tersimpan">×</button>
                        </div>
                    @endif
                    <div id="thumbnail-baru" class="relative hidden h-28 w-28">
                        <img id="gambar-preview" alt="Thumbnail baru" class="h-full w-full rounded-lg object-cover">
                        <button id="hapus-thumbnail-baru" type="button" class="absolute right-1 top-1 rounded-full bg-red-600 px-2 text-white focus:outline-none focus:ring-2 focus:ring-red-500" aria-label="Batalkan thumbnail baru">×</button>
                    </div>
                    <button id="tambah-thumbnail" type="button" class="{{ $berita->gambar && ! old('hapus_gambar') ? 'hidden' : 'flex' }} h-28 w-28 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 text-4xl text-gray-500 hover:border-brand-500 hover:text-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-700" aria-label="Tambah thumbnail berita" aria-controls="gambar">+</button>
                </div>
                <p id="thumbnail-status" class="mt-1 {{ old('hapus_gambar') ? '' : 'hidden' }} text-sm text-gray-500 dark:text-gray-400" role="status">Thumbnail tersimpan akan dihapus setelah Anda menyimpan perubahan.</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, atau WebP; maksimum 2 MB. Klik X untuk menghapus thumbnail tersimpan sebelum memilih pengganti.</p>
                @error('gambar')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-6">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Isi Konten <span class="text-red-500">*</span>
                </label>
                <div id="berita-editor" class="hidden"><div id="berita-quill" aria-label="Isi konten berita"></div></div>
                <textarea id="berita-isi" name="isi" rows="15" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" required>{{ old('isi', $berita->isi) }}</textarea>
                @error('isi')
                    <span class="mt-1 block text-sm text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('website.berita.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                    {{ $berita->exists ? 'Update Data' : 'Simpan Data' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    #berita-editor:not(.hidden) {
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
    }
    #berita-editor .ql-toolbar {
        border-radius: 0.75rem 0.75rem 0 0;
        border-color: #d0d5dd;
        background: #f9fafb;
        padding: 0.75rem;
    }
    #berita-editor .ql-container {
        border-radius: 0 0 0.75rem 0.75rem;
        border-color: #d0d5dd;
        font-family: inherit;
        font-size: 0.875rem;
    }
    #berita-editor .ql-editor {
        min-height: 280px;
        padding: 1rem;
        color: #1d2939;
    }
    #berita-editor .ql-editor:focus { outline: 2px solid #8098f9; outline-offset: -2px; }
    #berita-editor .ql-picker-options { z-index: 10; }
    .dark #berita-editor .ql-toolbar { background: #1d2939; border-color: #344054; }
    .dark #berita-editor .ql-container { border-color: #344054; }
    .dark #berita-editor .ql-editor { color: #f2f4f7; }
    .dark #berita-editor .ql-stroke { stroke: #d0d5dd; }
    .dark #berita-editor .ql-fill { fill: #d0d5dd; }
    .dark #berita-editor .ql-picker, .dark #berita-editor .ql-picker-label { color: #d0d5dd; }
    .dark #berita-editor .ql-picker-options, .dark #berita-editor .ql-tooltip { background: #1d2939; border-color: #475467; color: #f2f4f7; }
    .dark #berita-editor .ql-tooltip input { background: #101828; color: #f2f4f7; border-color: #475467; }
    .dark #berita-editor .ql-editor.ql-blank::before { color: #98a2b3; }
    .dark #berita-editor .ql-toolbar button:hover .ql-stroke,
    .dark #berita-editor .ql-toolbar button.ql-active .ql-stroke { stroke: #8098f9; }
    .dark #berita-editor .ql-toolbar button:hover .ql-fill,
    .dark #berita-editor .ql-toolbar button.ql-active .ql-fill { fill: #8098f9; }
    .dark #berita-editor .ql-toolbar .ql-picker-item:hover,
    .dark #berita-editor .ql-toolbar .ql-picker-label:hover { color: #8098f9; }
</style>
@endpush

@push('scripts')
    @vite('resources/js/berita-editor.js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('gambar');
            const oldPreview = document.getElementById('thumbnail-lama');
            const newPreview = document.getElementById('thumbnail-baru');
            const image = document.getElementById('gambar-preview');
            const addButton = document.getElementById('tambah-thumbnail');
            const removeFlag = document.getElementById('hapus_gambar');
            const status = document.getElementById('thumbnail-status');
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
            document.getElementById('hapus-thumbnail-lama')?.addEventListener('click', () => {
                removeFlag.value = '1';
                render();
            });
            document.getElementById('hapus-thumbnail-baru').addEventListener('click', () => {
                input.value = '';
                render();
            });
            window.addEventListener('pagehide', () => {
                if (objectUrl) URL.revokeObjectURL(objectUrl);
            });
        });
    </script>
@endpush