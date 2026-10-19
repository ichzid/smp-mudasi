@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Pemeriksaan Pembayaran Ujian" pageSubtitle="Pindai kartu untuk melihat status pembayaran UTS atau UAS tanpa memblokir siswa." />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <form id="exam-check" class="grid gap-4 md:grid-cols-2 md:items-end">
            <div>
                <label for="tarif_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis ujian / semester</label>
                <div class="relative">
                    <select id="tarif_id" required class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white bg-none px-4 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih tarif ujian</option>
                        @foreach($tarifs as $tarif)
                            <option value="{{ $tarif->id }}">{{ strtoupper($tarif->jenis) }} — {{ $tarif->tahunAjaran->nama }} {{ $tarif->semester == 1 ? 'Ganjil' : 'Genap' }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </div>
            </div>
            <div>
                <label for="kode_uid" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Pindai kartu RFID (akhiri dengan Enter)</label>
                <input id="kode_uid" maxlength="32" autocomplete="off" autofocus required placeholder="Fokuskan kolom ini lalu tempelkan kartu" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <button id="check-button" type="submit" class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 md:col-span-2 md:justify-self-start">Periksa Pembayaran</button>
        </form>
    </div>

    <div id="check-result" role="status" class="rounded-2xl border border-gray-200 bg-white p-5 text-sm text-gray-600 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300 sm:p-6">Pilih tarif dan pindai kartu untuk melihat status.</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('exam-check');
    const tarif = document.getElementById('tarif_id');
    const scan = document.getElementById('kode_uid');
    const button = document.getElementById('check-button');
    const result = document.getElementById('check-result');
    const money = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value));
    let requestId = 0;

    tarif.addEventListener('change', () => {
        requestId++;
        result.textContent = 'Pindai kartu untuk melihat status tarif yang dipilih.';
        scan.focus();
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!tarif.value || !scan.value.trim()) return;
        const id = ++requestId;
        const uid = scan.value.trim();
        const selectedTarif = tarif.value;
        scan.value = '';
        result.textContent = 'Memeriksa pembayaran...';
        button.disabled = true;
        try {
            const response = await fetch(@json(route('pembayaran.ujian.cek')), {
                method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: JSON.stringify({ kode_uid: uid, tarif_id: selectedTarif }),
            });
            const data = await response.json();
            if (id !== requestId) return;
            if (!response.ok) {
                const error = response.status === 401 || response.status === 403 ? 'Sesi berakhir atau Anda tidak berwenang. Silakan masuk kembali.' : Object.values(data.errors || {}).flat()[0] || data.message || 'Pemeriksaan gagal.';
                throw new Error(error);
            }
            const status = data.tagihan.status;
            const label = { belum_bayar: 'Belum bayar', sebagian: 'Dibayar sebagian', lunas: 'Lunas' }[status] || status;
            const lines = [
                data.siswa.nama_lengkap + ' — NIS ' + data.siswa.nis + (data.siswa.nisn ? ' / NISN ' + data.siswa.nisn : ''),
                String(data.tarif.jenis).toUpperCase() + ' — ' + data.tarif.tahun_ajaran.nama + ' Semester ' + data.tarif.semester,
                'Status: ' + label,
                'Tagihan: ' + money(data.tagihan.nominal) + ' · Dibayar: ' + money(data.tagihan.dibayar) + ' · Sisa: ' + money(data.tagihan.sisa),
                status === 'lunas' ? 'Pembayaran ujian lunas.' : 'Perhatian: pembayaran belum lunas. Tindak lanjuti secara manual; pemeriksaan ini tidak menolak akses ujian.',
            ];
            result.replaceChildren();
            result.classList.toggle('border-amber-400', status !== 'lunas');
            result.classList.toggle('dark:border-amber-600', status !== 'lunas');
            lines.forEach((line, index) => {
                const p = document.createElement('p');
                p.textContent = line;
                p.className = index === 0 ? 'text-lg font-semibold text-gray-900 dark:text-white' : index === 4 && status !== 'lunas' ? 'mt-3 font-semibold text-amber-700 dark:text-amber-400' : 'mt-2';
                result.append(p);
            });
        } catch (error) {
            if (id === requestId) {
                result.textContent = error.message;
                result.classList.remove('border-amber-400', 'dark:border-amber-600');
            }
        } finally {
            button.disabled = false;
            scan.focus();
        }
    });
});
</script>
@endpush
