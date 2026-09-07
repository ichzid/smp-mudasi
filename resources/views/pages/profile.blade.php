@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Profil Saya" pageSubtitle="Kelola informasi akun dan keamanan kata sandi Anda." />

    @php
        $user = auth()->user();
        $roleLabels = [
            'admin' => 'Administrator',
            'tu' => 'Tata Usaha',
            'wali_kelas' => 'Wali Kelas',
        ];
    @endphp

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-24 w-24 items-center justify-center rounded-full bg-brand-50 text-3xl font-bold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h2 class="mt-4 text-xl font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <span class="mt-4 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                    {{ $roleLabels[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-2">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Perbarui Profil</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui nama, alamat email, atau kata sandi akun Anda.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-400">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Lengkap <span class="text-error-500">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @error('name')<p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="username" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Username <span class="text-error-500">*</span></label>
                        <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required maxlength="255" autocomplete="username"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @error('username')<p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Internal (opsional)</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @error('email')<p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 dark:border-gray-800">
                    <h4 class="mb-1 text-base font-semibold text-gray-800 dark:text-white/90">Ubah Kata Sandi</h4>
                    <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Kosongkan bagian ini jika tidak ingin mengubah kata sandi.</p>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="current_password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kata Sandi Saat Ini</label>
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @error('current_password')<p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kata Sandi Baru</label>
                            <input id="password" name="password" type="password" autocomplete="new-password"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            @error('password')<p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Konfirmasi Kata Sandi Baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" data-required-if-filled="password" data-same-as="password" autocomplete="new-password"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @error('password_confirmation')<p class="mt-1.5 text-xs text-error-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:w-auto">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
