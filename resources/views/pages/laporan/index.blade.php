@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Laporan Presensi" pageSubtitle="Rekap masuk dan pulang siswa." />

    <div class="flex gap-2 overflow-x-auto border-b border-gray-200 dark:border-gray-800">
        @foreach(['harian' => 'Rekap Harian per Kelas', 'bulanan' => 'Rekap Bulanan per Siswa'] as $key => $label)
            <a href="{{ route('laporan.index', ['tab' => $key, 'tahun_ajaran_id' => $tahunAjaran?->id]) }}"
                class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition-colors {{ $tab === $key ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form class="grid grid-cols-1 items-end gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-4 dark:border-gray-800 dark:bg-white/[.03]">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div>
            <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran</label>
            <div class="relative">
                <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    @foreach($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}" @selected($tahunAjaran?->id===$ta->id)>{{ $ta->nama }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </div>
        </div>
        <div>
            <label for="rombel_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kelas</label>
            <div class="relative">
                <select id="rombel_id" name="rombel_id" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih kelas</option>
                    @foreach($rombels as $r)
                        <option value="{{ $r->id }}" @selected($rombel?->id===$r->id)>{{ $r->kelas_label }}</option>
                    @endforeach
                </select>
                <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                    <svg class="size-5 stroke-current" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </span>
            </div>
        </div>
        <div>
            @if($tab === 'harian')
                <x-form.date-picker id="periode" name="tanggal" label="Tanggal" :defaultDate="$tanggal" altFormat="d/m/Y" placeholder="Pilih tanggal" />
            @else
                <x-form.date-picker id="periode" name="bulan" label="Bulan" :defaultDate="$bulan" dateFormat="Y-m" altFormat="F Y" placeholder="Pilih bulan" />
            @endif
        </div>
        <div class="flex items-end">
            <button class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20">Tampilkan</button>
        </div>
    </form>

    @if($rombel)
        @php $params=request()->query(); @endphp
        <div class="rounded-2xl border bg-white dark:border-gray-800 dark:bg-white/[.03]">
            <div class="flex justify-end gap-2 p-4">
                <a class="rounded border px-3 py-2 dark:text-white" href="{{ route('laporan.csv',$params) }}">CSV</a>
                <a target="_blank" class="rounded bg-gray-800 px-3 py-2 text-white" href="{{ route('laporan.print',$params) }}">Cetak</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="p-3">NIS</th>
                            <th>Nama</th>
                            @if($tab==='harian')
                                <th>Status Masuk</th><th>Masuk</th><th>Pulang</th><th>Status Pulang</th><th>Durasi</th><th>Alasan</th>
                            @else
                                @foreach(['Hadir','Terlambat','Izin','Sakit','Alpa','Sudah Pulang','Belum Pulang','Pulang Cepat'] as $h)
                                    <th class="p-2">{{ $h }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-800">
                        @forelse($rows as $r)
                            <tr class="dark:text-gray-300">
                                @if($tab==='harian')
                                    <td class="p-3">{{ $r->siswa->nis }}</td><td>{{ $r->siswa->nama_lengkap }}</td><td>{{ ucfirst($r->status) }}</td><td>{{ $r->waktu_masuk??'-' }}</td><td>{{ $r->waktu_pulang??'-' }}</td><td>{{ $r->status_pulang?str_replace('_',' ',ucfirst($r->status_pulang)):'-' }}</td><td>{{ $r->durasi_sekolah??'-' }}</td><td>{{ $r->alasan_pulang_cepat??'-' }}</td>
                                @else
                                    <td class="p-3">{{ $r->nis }}</td><td>{{ $r->nama_lengkap }}</td>
                                    @foreach(['hadir','terlambat','izin','sakit','alpa','sudah_pulang','belum_pulang','pulang_cepat'] as $f)
                                        <td class="text-center">{{ $r->{$f} }}</td>
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="10" class="p-10 text-center text-gray-500">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
