@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-indigo-500 p-6 text-white shadow-lg shadow-brand-500/20 sm:p-8">
            <div class="absolute -right-12 -top-12 h-52 w-52 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-20 right-28 h-44 w-44 rounded-full bg-white/10"></div>
            <div class="relative">
                <p class="mb-2 text-sm font-medium text-white/80">Dashboard Sekolah</p>
                <h2 class="max-w-2xl text-2xl font-bold sm:text-3xl">Selamat Datang di Sistem Informasi Sekolah</h2>
                <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-sm backdrop-blur-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ \Carbon\Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-3">
            @foreach([
                ['label' => 'Total Siswa', 'value' => $totalSiswa, 'note' => 'Siswa aktif tahun ajaran ini', 'accent' => 'bg-blue-50 dark:bg-blue-500/5', 'iconClass' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400', 'icon' => 'M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0v1H5v-1z'],
                ['label' => 'Total Guru', 'value' => $totalGuru, 'note' => 'Tenaga pengajar terdaftar', 'accent' => 'bg-violet-50 dark:bg-violet-500/5', 'iconClass' => 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-400', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118.5 17c-2.04 1.172-4.259 1.75-6.5 1.75S7.54 18.172 5.5 17a12.078 12.078 0 01.34-6.422L12 14z'],
                ['label' => 'Total Kelas', 'value' => $totalRombel, 'note' => 'Kelas aktif tahun ajaran ini', 'accent' => 'bg-amber-50 dark:bg-amber-500/5', 'iconClass' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400', 'icon' => 'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h1m-1 4h1m4-4h1m-1 4h1m-6 10v-5h6v5'],
            ] as $item)
                <article class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full {{ $item['accent'] }}"></div>
                    <div class="relative flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ number_format($item['value']) }}</p>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $item['note'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $item['iconClass'] }}">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/></svg>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <article class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                <header class="flex flex-col gap-2 border-b border-gray-100 p-6 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Kehadiran Hari Ini</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Distribusi status seluruh siswa aktif.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-sm font-semibold {{ $persentaseHadir >= 90 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' }}">{{ $persentaseHadir }}% hadir</span>
                </header>
                <div class="grid gap-6 p-6 lg:grid-cols-[1fr_1.35fr] lg:items-center">
                    <div id="attendanceChart" class="mx-auto min-h-[270px] w-full max-w-[320px]"></div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach([
                            ['Hadir', $hadir, 'bg-emerald-500'], ['Terlambat', $terlambat, 'bg-amber-500'],
                            ['Izin', $izin, 'bg-blue-500'], ['Sakit', $sakit, 'bg-orange-500'],
                            ['Alpa', $alpa, 'bg-rose-500'], ['Belum Tercatat', $belumTercatat, 'bg-gray-400']
                        ] as [$label, $value, $color])
                            <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                                <div class="mb-3 flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full {{ $color }}"></span><span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span></div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($value) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between">
                    <div><h3 class="text-lg font-bold text-gray-900 dark:text-white">Status Kepulangan</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Progres siswa yang sudah masuk.</p></div>
                    <div class="rounded-xl bg-indigo-50 p-2.5 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></div>
                </div>
                <div class="mt-7 text-center"><p class="text-5xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $persentasePulang }}%</p><p class="mt-2 text-sm text-gray-500 dark:text-gray-400">telah melakukan presensi pulang</p></div>
                <div class="mt-6 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-brand-500" style="width: {{ min(100, $persentasePulang) }}%"></div></div>
                <div class="mt-7 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-500/10"><p class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Sudah Pulang</p><p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $sudahPulang }}</p></div>
                    <div class="rounded-xl bg-amber-50 p-4 dark:bg-amber-500/10"><p class="text-xs font-medium text-amber-700 dark:text-amber-400">Belum Pulang</p><p class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-400">{{ $belumPulang }}</p></div>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach([
                ['Sudah Masuk', $sudahMasuk, 'Dari '.$totalSiswa.' siswa aktif', 'text-blue-600 dark:text-blue-400'],
                ['Terlambat', $terlambat, 'Memerlukan perhatian', 'text-amber-600 dark:text-amber-400'],
                ['Pulang Cepat', $pulangCepat, 'Dengan izin petugas', 'text-orange-600 dark:text-orange-400'],
                ['Belum Tercatat', $belumTercatat, 'Belum ada status hari ini', 'text-rose-600 dark:text-rose-400'],
            ] as [$label, $value, $note, $color])
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-2 text-3xl font-bold {{ $color }}">{{ number_format($value) }}</p><p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ $note }}</p>
                </article>
            @endforeach
        </section>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const element = document.querySelector('#attendanceChart');
        if (!element || typeof ApexCharts === 'undefined') return;

        const chart = new ApexCharts(element, {
            chart: { type: 'donut', height: 285, fontFamily: 'Outfit, sans-serif', toolbar: { show: false } },
            series: {{ Illuminate\Support\Js::from([$hadir, $terlambat, $izin, $sakit, $alpa, $belumTercatat]) }},
            labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Belum Tercatat'],
            colors: ['#10b981', '#f59e0b', '#3b82f6', '#f97316', '#f43f5e', '#9ca3af'],
            legend: { show: false },
            dataLabels: { enabled: false },
            stroke: { width: 4, colors: [document.documentElement.classList.contains('dark') ? '#111827' : '#ffffff'] },
            plotOptions: { pie: { donut: { size: '72%', labels: { show: true, name: { show: true, color: '#9ca3af' }, value: { show: true, fontSize: '28px', fontWeight: 700 }, total: { show: true, label: 'Total Siswa', fontSize: '13px', color: '#9ca3af', formatter: () => '{{ number_format($totalSiswa) }}' } } } } },
            tooltip: { y: { formatter: value => `${value} siswa` } }
        });
        chart.render();
    });
</script>
@endpush
