@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Salin Struktur Kelas" pageSubtitle="Salin struktur kelas tahun sebelumnya ke tahun ajaran aktif." />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Hanya nama, tingkat, dan wali kelas yang disalin. Siswa tidak disalin karena kenaikan kelas memerlukan pemetaan manual. Riwayat kelas dan presensi tahun sebelumnya tetap utuh.</p>

            @if($message)
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">{{ $message }}</div>
            @else
                <form action="{{ route('master.rombel.salin.store') }}" method="POST" id="salin-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="source_id" value="{{ $source->id }}">
                    <input type="hidden" name="target_id" value="{{ $target->id }}">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Sumber</span>
                            <p class="rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white">{{ $source->nama }}</p>
                            @error('source_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <span class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Tujuan</span>
                            <p class="rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white">{{ $target->nama }} (Aktif)</p>
                            @error('target_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('master.rombel.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-300">Batal</a>
                        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Salin Struktur Kelas</button>
                    </div>
                </form>
            @endif
            @if($message)
                <a href="{{ route('master.rombel.index') }}" class="mt-6 inline-block text-sm font-medium text-brand-500">Kembali ke Data Kelas</a>
            @endif
        </div>
    </div>
@endsection

@if(! $message)
    @push('scripts')
        <script>
            document.getElementById('salin-form').addEventListener('submit', async function (event) {
                if (this.dataset.confirmed) return;
                event.preventDefault();
                const result = await Swal.fire({ icon: 'question', title: 'Salin struktur kelas?', text: 'Hanya struktur kelas yang disalin. Anggota siswa harus ditentukan secara manual setelah kenaikan kelas.', showCancelButton: true, confirmButtonText: 'Ya, salin', cancelButtonText: 'Batal' });
                if (result.isConfirmed) {
                    this.dataset.confirmed = 'true';
                    this.requestSubmit();
                }
            });
        </script>
    @endpush
@endif
