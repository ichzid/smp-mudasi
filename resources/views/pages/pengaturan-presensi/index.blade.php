@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Pengaturan Presensi" pageSubtitle="Jadwal reguler presensi masuk dan pulang per hari." />

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="p-4 text-left">Hari</th>
                    <th class="p-4">Aktif</th>
                    <th class="p-4">Mulai Masuk</th>
                    <th class="p-4">Batas Terlambat</th>
                    <th class="p-4">Mulai Pulang</th>
                    <th class="p-4">Akhir Pulang</th>
                    @if (auth()->user()->role === 'admin')
                        <th class="p-4">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($jadwals as $jadwal)
                    @php($formId = 'jadwal-presensi-'.$jadwal->id)
                    <tr x-data="{ aktif: {{ $jadwal->is_aktif ? 'true' : 'false' }} }">
                        <td class="p-4 font-medium dark:text-white">{{ $jadwal->nama_hari }}</td>
                        <td class="p-4 text-center">
                            <label class="inline-flex items-center" @class(['cursor-pointer' => auth()->user()->role === 'admin', 'cursor-not-allowed' => auth()->user()->role !== 'admin'])>
                                <input
                                    type="checkbox"
                                    name="is_aktif"
                                    value="1"
                                    form="{{ $formId }}"
                                    x-model="aktif"
                                    class="peer sr-only"
                                    @disabled(auth()->user()->role !== 'admin')
                                >
                                <span class="flex size-6 items-center justify-center rounded-md border border-gray-300 bg-white text-transparent transition peer-checked:border-brand-500 peer-checked:bg-brand-500 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:peer-checked:border-brand-500 dark:peer-checked:bg-brand-500">
                                    <svg class="size-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                        <path d="M3.5 8.5 6.5 11.5 12.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span class="sr-only" x-text="aktif ? 'Aktif' : 'Tidak aktif'"></span>
                            </label>
                        </td>
                        @foreach (['jam_mulai_masuk', 'batas_terlambat', 'jam_mulai_pulang', 'jam_akhir_pulang'] as $field)
                            <td class="p-3">
                                <input
                                    type="time"
                                    name="{{ $field }}"
                                    form="{{ $formId }}"
                                    value="{{ $jadwal->{$field} ? substr($jadwal->{$field}, 0, 5) : '' }}"
                                    :required="aktif && '{{ $field }}' !== 'jam_akhir_pulang'"
                                    @disabled(auth()->user()->role !== 'admin')
                                    class="h-10 cursor-pointer rounded-lg border border-gray-300 bg-transparent px-2 dark:border-gray-700 dark:text-white disabled:cursor-not-allowed"
                                >
                                <div class="text-xs text-red-500">@error($field){{ $message }}@enderror</div>
                            </td>
                        @endforeach
                        @if (auth()->user()->role === 'admin')
                            <td class="p-3">
                                <form id="{{ $formId }}" method="POST" action="{{ route('pengaturan-presensi.update', $jadwal) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="hari" value="{{ $jadwal->hari }}">
                                    <button class="rounded-lg bg-brand-500 px-3 py-2 text-white">Simpan</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
