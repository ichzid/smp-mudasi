@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Laporan Guru" pageSubtitle="Rekap presensi masuk dan pulang guru aktif. Data historis mengikuti status aktif guru saat ini." />

    <div class="flex gap-2 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        @foreach(['harian' => 'Rekap Harian', 'bulanan' => 'Rekap Bulanan'] as $key => $label)
            <a href="{{ route('laporan-guru.index', ['tab' => $key, 'tanggal' => $tanggal, 'bulan' => $bulan]) }}"
                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium {{ $tab === $key ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 dark:text-gray-400' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form class="grid grid-cols-1 items-end gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3 dark:border-gray-800 dark:bg-white/[.03]">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div>
            @if($tab === 'harian')
                <x-form.date-picker id="periode-guru" name="tanggal" label="Tanggal" :defaultDate="$tanggal" altFormat="d/m/Y" placeholder="Pilih tanggal" />
            @else
                <x-form.date-picker id="periode-guru" name="bulan" label="Bulan" :defaultDate="$bulan" dateFormat="Y-m" altFormat="F Y" placeholder="Pilih bulan" />
            @endif
        </div>
        <button class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">Tampilkan</button>
        <div class="flex gap-2">
            <a class="rounded border px-3 py-2 dark:text-white" href="{{ route('laporan-guru.csv', ['tab' => $tab, 'tanggal' => $tanggal, 'bulan' => $bulan]) }}">CSV</a>
            <a target="_blank" class="rounded bg-gray-800 px-3 py-2 text-white" href="{{ route('laporan-guru.print', ['tab' => $tab, 'tanggal' => $tanggal, 'bulan' => $bulan]) }}">Cetak</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50"><tr>
                <th class="p-3 text-left">No.</th><th class="p-3 text-left">Nomor Identitas</th><th class="p-3 text-left">Nama</th>
                @if($tab === 'harian')
                    <th class="p-3 text-left">Status Masuk</th><th class="p-3 text-left">Waktu Masuk</th><th class="p-3 text-left">Waktu Pulang</th><th class="p-3 text-left">Status Pulang</th>
                @else
                    <th class="p-3 text-center">Hadir</th><th class="p-3 text-center">Terlambat</th><th class="p-3 text-center">Belum Pulang</th>
                @endif
            </tr></thead>
            <tbody class="divide-y dark:divide-gray-800">
                @foreach($rows as $row)
                    <tr class="dark:text-gray-300">
                        <td class="p-3">{{ $loop->iteration }}</td><td class="p-3">{{ $row->nomor_indentitas ?: '-' }}</td><td class="p-3">{{ $row->nama_lengkap }}</td>
                        @if($tab === 'harian')
                            <td class="p-3">{{ $row->status }}</td><td class="p-3">{{ $row->waktu_masuk ?? '-' }}</td><td class="p-3">{{ $row->waktu_pulang ?? '-' }}</td><td class="p-3">{{ $row->status_pulang ?? '-' }}</td>
                        @else
                            <td class="p-3 text-center">{{ $row->hadir }}</td><td class="p-3 text-center">{{ $row->terlambat }}</td><td class="p-3 text-center">{{ $row->belum_pulang }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($rows->isEmpty())
            <p class="p-8 text-center text-sm text-gray-500">Belum ada guru aktif.</p>
        @endif
    </div>
</div>
@endsection
