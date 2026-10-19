@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Tarif Pembayaran" pageSubtitle="Atur tarif SPP, UTS, dan UAS untuk setiap semester tahun ajaran." />

    @if($errors->any())
        <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-300" role="alert">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @if(auth()->user()->role === 'admin')
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Tambah Tarif</h2>
        <form action="{{ route('pembayaran.tarif.store') }}" method="POST" class="grid gap-4 md:grid-cols-5 md:items-end">
            @csrf
            <div>
                <label for="tahun_ajaran_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun ajaran</label>
                <div class="relative">
                    <select id="tahun_ajaran_id" name="tahun_ajaran_id" required class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white bg-none px-4 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih tahun ajaran</option>
                        @foreach($tahunAjarans as $tahun)
                            <option value="{{ $tahun->id }}" @selected(old('tahun_ajaran_id') == $tahun->id)>{{ $tahun->nama }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </div>
            </div>
            <div>
                <label for="semester" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Semester tarif</label>
                <select id="semester" name="semester" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">Pilih semester</option>
                    <option value="1" @selected(old('semester') == 1)>Ganjil</option>
                    <option value="2" @selected(old('semester') == 2)>Genap</option>
                </select>
            </div>
            <div>
                <label for="jenis" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis pembayaran</label>
                <div class="relative">
                    <select id="jenis" name="jenis" required class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white bg-none px-4 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih jenis</option>
                        @foreach(['spp' => 'SPP (per bulan)', 'uts' => 'UTS', 'uas' => 'UAS'] as $jenis => $label)
                            <option value="{{ $jenis }}" @selected(old('jenis') === $jenis)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </div>
            </div>
            <div>
                <label for="nominal" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal (Rp)</label>
                <input id="nominal" name="nominal" type="number" min="1" step="1" required value="{{ old('nominal') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <button type="submit" class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">Simpan Tarif</button>
        </form>
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Setiap jenis tarif hanya dapat dibuat sekali per semester. Tarif SPP berlaku untuk setiap bulan dalam semester tersebut.</p>
    </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto p-5">
            <table class="data-table w-full border-collapse text-left">
                <thead class="bg-gray-50 dark:bg-gray-900/50"><tr>
                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Tahun ajaran</th>
                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Semester</th>
                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Jenis</th>
                    <th class="px-5 py-3 text-sm font-semibold text-gray-500 dark:text-gray-400">Nominal (Rp)</th>
                    <th class="px-5 py-3 text-right text-sm font-semibold text-gray-500 dark:text-gray-400" data-orderable="false">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($tarifs as $tarif)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-5 py-4 text-sm text-gray-900 dark:text-white">{{ $tarif->tahunAjaran->nama }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $tarif->semester == 1 ? 'Ganjil' : 'Genap' }}</td>
                            <td class="px-5 py-4 text-sm font-medium uppercase text-gray-900 dark:text-white">{{ $tarif->jenis }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ number_format($tarif->nominal, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right">
                                @if(auth()->user()->role === 'admin')
                                <form action="{{ route('pembayaran.tarif.update', $tarif->id) }}" method="POST" class="inline-flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <label for="nominal-{{ $tarif->id }}" class="sr-only">Ubah nominal {{ strtoupper($tarif->jenis) }} {{ $tarif->tahunAjaran->nama }}</label>
                                    <input id="nominal-{{ $tarif->id }}" name="nominal" type="number" min="1" step="1" required value="{{ $tarif->nominal }}" class="h-10 w-36 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Perbarui</button>
                                </form>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Hanya admin</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
