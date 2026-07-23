@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Laporan Presensi" pageSubtitle="Rekap presensi berdasarkan snapshot rombel saat presensi dicatat." />

    <div class="flex gap-2 border-b border-gray-200 dark:border-gray-800">
        @foreach(['harian' => 'Rekap Harian per Rombel', 'bulanan' => 'Rekap Bulanan per Siswa'] as $key => $label)
            <a href="{{ route('laporan.index', ['tab' => $key, 'tahun_ajaran_id' => $tahunAjaran?->id]) }}" class="px-4 py-3 text-sm font-medium {{ $tab === $key ? 'border-b-2 border-brand-500 text-brand-600' : 'text-gray-500' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('laporan.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4 items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran</label>
                <select name="tahun_ajaran_id" onchange="this.form.rombel_id.value='';this.form.submit()" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white">
                    @foreach($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}" @selected($tahunAjaran?->id === $ta->id)>{{ $ta->nama }} - Semester {{ $ta->semester }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rombel</label>
                <select name="rombel_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white">
                    <option value="">Pilih rombel</option>
                    @foreach($rombels as $item)
                        <option value="{{ $item->id }}" @selected($rombel?->id === $item->id)>{{ $item->select_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ $tab === 'harian' ? 'Tanggal' : 'Bulan' }}</label>
                <input type="{{ $tab === 'harian' ? 'date' : 'month' }}" name="{{ $tab === 'harian' ? 'tanggal' : 'bulan' }}" value="{{ $tab === 'harian' ? ($tanggal ?: now()->toDateString()) : ($bulan ?: now()->format('Y-m')) }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white">
            </div>
            <button class="h-11 rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Tampilkan</button>
        </form>
    </div>

    @if($rombel)
        @php $params = ['tab' => $tab, 'tahun_ajaran_id' => $tahunAjaran->id, 'rombel_id' => $rombel->id, 'tanggal' => $tanggal, 'bulan' => $bulan]; @endphp
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-3 border-b border-gray-200 p-5 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <div><h3 class="font-semibold text-gray-900 dark:text-white">{{ $rombel->label }}</h3><p class="text-sm text-gray-500">{{ $tab === 'harian' ? \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') : \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}</p></div>
                <div class="flex gap-2"><a href="{{ route('laporan.csv', $params) }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium dark:border-gray-700 dark:text-white">Ekspor CSV</a><a target="_blank" href="{{ route('laporan.print', $params) }}" class="rounded-lg bg-gray-800 px-3 py-2 text-sm font-medium text-white">Print / Save as PDF</a></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 dark:bg-gray-900/50"><tr>@if($tab === 'bulanan')<th class="px-5 py-3">NIS</th><th class="px-5 py-3">Nama Siswa</th>@else<th class="px-5 py-3">Rombel</th>@endif @foreach($statuses as $status)<th class="px-4 py-3 text-center">{{ ucfirst($status) }}</th>@endforeach<th class="px-4 py-3 text-center">Total</th></tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($rows as $row)<tr class="text-gray-700 dark:text-gray-300">@if($tab === 'bulanan')<td class="px-5 py-4">{{ $row->nis }}</td><td class="px-5 py-4 font-medium">{{ $row->nama_lengkap }}</td>@else<td class="px-5 py-4 font-medium">{{ $row->nama === $row->tingkat ? $row->nama : $row->tingkat.' '.$row->nama }}</td>@endif @foreach($statuses as $status)<td class="px-4 py-4 text-center">{{ $row->{$status} }}</td>@endforeach<td class="px-4 py-4 text-center font-semibold">{{ $row->total }}</td></tr>@empty<tr><td colspan="9" class="px-5 py-12 text-center text-gray-500">Belum ada data presensi untuk filter ini.</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center text-gray-500 dark:border-gray-700 dark:bg-white/[0.03]">Pilih rombel untuk menampilkan laporan.</div>
    @endif
</div>
@endsection
