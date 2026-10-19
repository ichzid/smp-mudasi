@extends('layouts.app')

@section('content')
<style>
@media print {
    body { background: white !important; }
    aside, header, nav, .print-hidden, [x-show] { display: none !important; }
    .receipt { border: 0 !important; box-shadow: none !important; max-width: 100% !important; margin: 0 !important; color: black !important; background: white !important; }
    .receipt * { color: black !important; }
}
</style>
<div class="space-y-6">
    <div class="print-hidden">
        <x-common.page-breadcrumb pageTitle="Kuitansi Pembayaran" pageSubtitle="Bukti pembayaran yang telah dicatat." :breadcrumbs="[['label' => 'Kasir Pembayaran', 'url' => route('pembayaran.kasir.index')]]" />
        <div class="mt-4 flex gap-3">
            <a href="{{ route('pembayaran.kasir.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Kembali ke kasir</a>
            <button type="button" onclick="window.print()" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Cetak kuitansi</button>
        </div>
    </div>

    <div class="receipt mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-10">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-200 pb-6 dark:border-gray-700">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kuitansi Pembayaran</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Kode: {{ $transaksi->kode_kuitansi }}</p>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">{{ $transaksi->dibayar_pada?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</div>
        </div>
        <dl class="grid gap-5 border-b border-gray-200 py-6 text-sm dark:border-gray-700 sm:grid-cols-2">
            <div><dt class="text-gray-500 dark:text-gray-400">Nama siswa</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $tagihan->siswa->nama_lengkap }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">NIS / NISN</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $tagihan->siswa->nis }} / {{ $tagihan->siswa->nisn ?: '-' }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">Tahun ajaran / semester</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $tagihan->tarif->tahunAjaran->nama }} — {{ $tagihan->tarif->semester == 1 ? 'Ganjil' : 'Genap' }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">Jenis / periode</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ strtoupper($tagihan->tarif->jenis) }} — {{ $tagihan->periode_key }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">Kasir</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $transaksi->user->name }}</dd></div>
            <div><dt class="text-gray-500 dark:text-gray-400">Nomor transaksi</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">#{{ $transaksi->id }}</dd></div>
        </dl>
        <dl class="space-y-3 pt-6 text-sm text-gray-700 dark:text-gray-300">
            <div class="flex justify-between gap-3"><dt>Tagihan</dt><dd>Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3"><dt>Total dibayar hingga transaksi ini</dt><dd>Rp {{ number_format($dibayar, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 border-t border-gray-200 pt-3 text-lg font-bold text-gray-900 dark:border-gray-700 dark:text-white"><dt>Nominal transaksi ini</dt><dd>Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between gap-3 font-semibold"><dt>Sisa tagihan setelah transaksi ini</dt><dd>Rp {{ number_format($sisa, 0, ',', '.') }}</dd></div>
        </dl>
        <p class="mt-8 text-xs text-gray-500 dark:text-gray-400">Simpan kuitansi ini sebagai bukti pembayaran.</p>
    </div>
</div>
@endsection
