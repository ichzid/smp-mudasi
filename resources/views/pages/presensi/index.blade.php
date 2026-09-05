@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Manajemen Presensi Harian" pageSubtitle="Koreksi presensi masuk, pulang, dan izin pulang cepat." />

    @if($errors->any())
        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[.03]">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <div>
                <x-form.date-picker id="tanggal" name="tanggal" label="Tanggal" :defaultDate="$tanggal" altFormat="d/m/Y" placeholder="Pilih tanggal" />
            </div>
            <div>
                <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Ajaran</label>
                <div class="relative">
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" onchange="this.form.submit()">
                        @foreach($tahun_ajarans as $ta)
                            <option value="{{ $ta->id }}" @selected($tahun_ajaran_id==$ta->id)>{{ $ta->nama }} - Semester {{ $ta->semester }}</option>
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
                    <select id="rombel_id" name="rombel_id" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:border-gray-800 dark:disabled:bg-gray-900/50 dark:disabled:text-white/30" onchange="this.form.submit()" @disabled($rombels->isEmpty())>
                        <option value="">Pilih kelas</option>
                        @foreach($rombels as $r)
                            <option value="{{ $r->id }}" @selected($rombel_id==$r->id)>{{ $r->kelas_label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                        <svg class="size-5 stroke-current" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </div>
            </div>
        </form>
    </div>

    @if($rombel_id)
        <form method="POST" action="{{ route('presensi.store') }}">
            @csrf
            <input type="hidden" name="rombel_id" value="{{ $rombel_id }}">
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[.03]">
                <table class="w-full min-w-[960px] text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="p-4 text-left">Siswa</th>
                            <th class="p-3 text-left">Status Masuk</th>
                            <th class="p-3 text-left">Waktu Masuk</th>
                            <th class="p-3 text-left">Waktu Pulang</th>
                            <th class="p-3 text-left">Status Pulang</th>
                            <th class="p-3 text-left">Alasan / Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($presensi_data as $item)
                            @php $id=$item->siswa->id; @endphp
                            <tr x-data="{status:'{{ old("presensi.$id.status",$item->status??'alpa') }}', pulang:'{{ old("presensi.$id.status_pulang",$item->status_pulang) }}'}">
                                <td class="p-4 font-medium text-gray-800 dark:text-white/90">
                                    {{ $item->siswa->nama_lengkap }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item->siswa->nis }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="relative min-w-32">
                                        <select x-model="status" name="presensi[{{ $id }}][status]" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2.5 pr-9 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                            @foreach(['hadir','terlambat','izin','sakit','alpa'] as $s)
                                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                            <svg class="size-5 stroke-current" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <input type="time" name="presensi[{{ $id }}][waktu_masuk]" value="{{ old("presensi.$id.waktu_masuk",$item->waktu_masuk?substr($item->waktu_masuk,0,5):'') }}" :required="['hadir','terlambat'].includes(status)" :disabled="!['hadir','terlambat'].includes(status)" class="h-11 w-32 rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:border-gray-800 dark:disabled:bg-gray-900/50 dark:disabled:text-white/30">
                                </td>
                                <td class="p-3">
                                    <input type="time" name="presensi[{{ $id }}][waktu_pulang]" value="{{ old("presensi.$id.waktu_pulang",$item->waktu_pulang?substr($item->waktu_pulang,0,5):'') }}" :disabled="!['hadir','terlambat'].includes(status)" class="h-11 w-32 rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:border-gray-800 dark:disabled:bg-gray-900/50 dark:disabled:text-white/30">
                                </td>
                                <td class="p-3">
                                    <div class="relative min-w-40">
                                        <select x-model="pulang" name="presensi[{{ $id }}][status_pulang]" :disabled="!['hadir','terlambat'].includes(status)" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-3 py-2.5 pr-9 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:border-gray-800 dark:disabled:bg-gray-900/50 dark:disabled:text-white/30">
                                            <option value="">Belum pulang</option>
                                            <option value="tepat_waktu">Tepat waktu</option>
                                            <option value="pulang_cepat">Pulang cepat</option>
                                        </select>
                                        <span class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                            <svg class="size-5 stroke-current" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="w-48 space-y-2">
                                        <input name="presensi[{{ $id }}][alasan_pulang_cepat]" value="{{ old("presensi.$id.alasan_pulang_cepat",$item->alasan_pulang_cepat) }}" :required="pulang==='pulang_cepat'" placeholder="Alasan pulang cepat" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                        <input name="presensi[{{ $id }}][catatan_pulang]" value="{{ old("presensi.$id.catatan_pulang",$item->catatan_pulang) }}" placeholder="Catatan (opsional)" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-5 text-right">
                    <button class="inline-flex h-11 items-center justify-center rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-hidden focus:ring-3 focus:ring-brand-500/20">Simpan Semua Siswa</button>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
