@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <!-- Breadcrumb -->
        <x-common.page-breadcrumb 
            pageTitle="Edit Data Guru" 
            pageSubtitle="Perbarui informasi data guru." 
            :breadcrumbs="[
                ['label' => 'Data Guru', 'url' => route('master.guru.index')]
            ]"
        />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-8 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('master.guru.update', $guru->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nomor Indentitas -->
                    <div>
                        <label for="nomor_indentitas" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Indentitas</label>
                        <input type="text" id="nomor_indentitas" name="nomor_indentitas" value="{{ old('nomor_indentitas', $guru->nomor_indentitas) }}" placeholder="Biarkan kosong jika non-PNS" maxlength="30"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('nomor_indentitas')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Lengkap -->
                    <div>
                        <label for="nama_lengkap" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $guru->nama_lengkap) }}" required maxlength="255"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('nama_lengkap')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="jenis_kelamin" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <div x-data="{ isOptionSelected: true }" class="relative z-20 bg-transparent">
                            <select id="jenis_kelamin" name="jenis_kelamin" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 text-gray-800 dark:text-white/90"
                                @change="isOptionSelected = true">
                                <option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">Pilih Jenis Kelamin</option>
                                <option value="L" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ old('jenis_kelamin', $guru->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        @error('jenis_kelamin')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No HP -->
                    <div>
                        <label for="no_hp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">No HP / WhatsApp</label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $guru->no_hp) }}"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('no_hp')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="tanggal_lahir" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Lahir</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($guru->tanggal_lahir)->format('Y-m-d')) }}"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('tanggal_lahir')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label for="jabatan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan <span class="text-red-500">*</span></label>
                        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $guru->jabatan) }}" required maxlength="255"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('jabatan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bidang Tugas -->
                    <div>
                        <label for="bidang_tugas" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bidang Tugas</label>
                        <input type="text" id="bidang_tugas" name="bidang_tugas" value="{{ old('bidang_tugas', $guru->bidang_tugas) }}" maxlength="255"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('bidang_tugas')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Urutan -->
                    <div>
                        <label for="urutan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Urutan</label>
                        <input type="number" id="urutan" name="urutan" value="{{ old('urutan', $guru->urutan ?? 0) }}" min="0"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('urutan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Aktif -->
                    <div class="flex items-center gap-3 md:col-span-2">
                        <input type="checkbox" id="aktif" name="aktif" value="1" {{ old('aktif', $guru->aktif) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                        <label for="aktif" class="text-sm font-medium text-gray-700 dark:text-gray-400">Aktif</label>
                    </div>

                    <!-- Biografi Singkat -->
                    <div class="md:col-span-2">
                        <label for="biografi_singkat" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Biografi Singkat</label>
                        <textarea id="biografi_singkat" name="biografi_singkat" rows="3"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('biografi_singkat', $guru->biografi_singkat) }}</textarea>
                        @error('biografi_singkat')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto -->
                    <div class="md:col-span-2">
                        <label for="foto" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Profil Guru</label>
                        @if($guru->foto)
                            <div class="mb-3 flex items-center justify-center w-32 h-32 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-800">
                                <img src="{{ Storage::url($guru->foto) }}" alt="{{ $guru->nama_lengkap }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/jpg" data-max-file-size="2097152"
                            class="focus:border-ring-brand-300 shadow-theme-xs focus:file:ring-brand-300 h-11 w-full overflow-hidden rounded-lg border border-gray-300 bg-transparent text-sm text-gray-500 transition-colors file:mr-5 file:border-collapse file:cursor-pointer file:rounded-l-lg file:border-0 file:border-r file:border-solid file:border-gray-200 file:bg-gray-50 file:py-3 file:pr-3 file:pl-3.5 file:text-sm file:text-gray-700 placeholder:text-gray-400 hover:file:bg-gray-100 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:text-white/90 dark:file:border-gray-800 dark:file:bg-white/[0.03] dark:file:text-gray-400 dark:placeholder:text-gray-400">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: JPG, JPEG, PNG. Maksimal 2 MB. Biarkan kosong jika tidak ingin mengubah foto.</p>
                        @error('foto')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('master.guru.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-lg shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all">
                        Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection