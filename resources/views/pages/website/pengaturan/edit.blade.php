@extends('layouts.app')

@section('content')

<x-common.page-breadcrumb pageTitle="Pengaturan Website Sekolah" pageSubtitle="Atur identitas dasar, logo, dan profil singkat yang akan tampil di website publik." />

@if ($errors->any())
<div class="mb-6 flex w-full border-l-6 border-danger bg-danger/20 px-7 py-4 shadow-md dark:bg-danger/30 md:p-5">
    <div class="mr-5 flex h-9 w-full max-w-[36px] items-center justify-center rounded-lg bg-danger">
        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6.4917 7.65579L11.106 12.2645C11.2545 12.4128 11.4715 12.5 11.7136 12.5C12.2048 12.5 12.5 12.2048 12.5 11.7136C12.5 11.4715 12.4128 11.2545 12.2645 11.106L7.65579 6.4917L12.2645 1.8774C12.4128 1.72887 12.5 1.51182 12.5 1.26972C12.5 0.77854 12.2048 0.5 11.7136 0.5C11.4715 0.5 11.2545 0.587184 11.106 0.735716L6.4917 5.32786L1.8774 0.735716C1.72887 0.587184 1.51182 0.5 1.26972 0.5C0.77854 0.5 0.5 0.77854 0.5 1.26972C0.5 1.51182 0.587184 1.72887 0.735716 1.8774L5.32786 6.4917L0.735716 11.106C0.587184 11.2545 0.5 11.4715 0.5 11.7136C0.5 12.2048 0.77854 12.5 1.26972 12.5C1.51182 12.5 1.72887 12.4128 1.8774 12.2645L6.4917 7.65579Z" fill="white"></path>
        </svg>
    </div>
    <div class="w-full">
        <h5 class="mb-3 font-bold text-danger">Terdapat Kesalahan</h5>
        <ul class="list-disc pl-5 text-[#637381]">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<form action="{{ route('website.pengaturan.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-1 gap-9 sm:grid-cols-2">
        
        <!-- Informasi Umum -->
        <div class="flex flex-col gap-9">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-6 font-bold text-black dark:text-white">Informasi Umum</h3>
                <div class="flex flex-col gap-5.5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Sekolah <span class="text-danger">*</span></label>
                        <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $pengaturan->nama_sekolah) }}" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Slogan / Motto</label>
                        <input type="text" name="slogan" value="{{ old('slogan', $pengaturan->slogan) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Profil Singkat Sekolah</label>
                        <textarea name="profil_singkat" rows="4" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('profil_singkat', $pengaturan->profil_singkat) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Logo Sekolah</label>
                        @if($pengaturan->logo)
                            @php
                                $logoPath = str_starts_with($pengaturan->logo, 'website/') 
                                    ? Storage::url($pengaturan->logo) 
                                    : asset($pengaturan->logo);
                            @endphp
                            <img src="{{ $logoPath }}" alt="Logo" class="h-20 w-auto mb-3 rounded border p-1 border-gray-200 bg-white" onerror="this.style.display='none'">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengganti. Maks: 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Kontak & Sosial Media -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-6 font-bold text-black dark:text-white">Kontak & Sosial Media</h3>
                <div class="flex flex-col gap-5.5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Sekolah</label>
                        <input type="email" name="email" value="{{ old('email', $pengaturan->email) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $pengaturan->nomor_telepon) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('alamat', $pengaturan->alamat) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Facebook (URL)</label>
                            <input type="url" name="facebook" value="{{ old('facebook', $pengaturan->facebook) }}" placeholder="https://facebook.com/..." class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Instagram (URL)</label>
                            <input type="url" name="instagram" value="{{ old('instagram', $pengaturan->instagram) }}" placeholder="https://instagram.com/..." class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">YouTube (URL)</label>
                            <input type="url" name="youtube" value="{{ old('youtube', $pengaturan->youtube) }}" placeholder="https://youtube.com/..." class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Kepala Sekolah & Statistik -->
        <div class="flex flex-col gap-9">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-6 font-bold text-black dark:text-white">Kepala Sekolah</h3>
                <div class="flex flex-col gap-5.5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Kepala Sekolah</label>
                        <input type="text" name="nama_kepala_sekolah" value="{{ old('nama_kepala_sekolah', $pengaturan->nama_kepala_sekolah) }}" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sambutan Kepala Sekolah</label>
                        <textarea name="sambutan_kepala_sekolah" rows="4" class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('sambutan_kepala_sekolah', $pengaturan->sambutan_kepala_sekolah) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Kepala Sekolah</label>
                        @if($pengaturan->foto_kepala_sekolah)
                            @php
                                $kepsekPath = str_starts_with($pengaturan->foto_kepala_sekolah, 'website/') 
                                    ? Storage::url($pengaturan->foto_kepala_sekolah) 
                                    : asset($pengaturan->foto_kepala_sekolah);
                            @endphp
                            <img src="{{ $kepsekPath }}" alt="Foto Kepsek" class="h-24 w-auto mb-3 rounded border p-1 border-gray-200 bg-white object-cover" onerror="this.style.display='none'">
                        @endif
                        <input type="file" name="foto_kepala_sekolah" accept="image/*" class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengganti. Maks: 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Statistik (Counter) -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-6 font-bold text-black dark:text-white">Statistik Sekolah</h3>
                <div>
                    <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">Data angka yang ditampilkan di halaman beranda.</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jumlah Siswa</label>
                            <input type="number" name="jumlah_siswa" value="{{ old('jumlah_siswa', $pengaturan->jumlah_siswa) }}" min="0" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jumlah Guru</label>
                            <input type="number" name="jumlah_guru" value="{{ old('jumlah_guru', $pengaturan->jumlah_guru) }}" min="0" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Prestasi</label>
                            <input type="number" name="jumlah_prestasi" value="{{ old('jumlah_prestasi', $pengaturan->jumlah_prestasi) }}" min="0" required class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                    Simpan Data
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
