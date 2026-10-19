@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Kasir Pembayaran" pageSubtitle="Cari siswa, periksa tagihan, lalu konfirmasi pembayaran secara manual." />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <form id="scan-form" class="space-y-3">
                <label for="kode_uid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pindai kartu RFID (akhiri dengan Enter)</label>
                <input id="kode_uid" maxlength="32" autocomplete="off" autofocus placeholder="Fokuskan kolom ini lalu tempelkan kartu" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Cari kartu</button>
            </form>
            <form id="search-form" class="space-y-3">
                <label for="q" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari manual berdasarkan nama / NIS</label>
                <input id="q" maxlength="100" autocomplete="off" placeholder="Ketik nama atau NIS siswa" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cari siswa</button>
            </form>
        </div>
        <p id="search-message" role="status" class="mt-4 text-sm text-gray-500 dark:text-gray-400">Pemindaian kartu hanya mencari siswa, tidak mencatat pembayaran.</p>
        <div id="search-results" class="mt-4 hidden space-y-2" aria-label="Hasil pencarian siswa"></div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Rincian Tagihan</h2>
        <p id="student-info" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih siswa melalui kartu atau hasil pencarian.</p>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div>
                <label for="tarif_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tarif / semester</label>
                <div class="relative">
                    <select id="tarif_id" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white bg-none px-4 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Pilih tarif</option>
                        @foreach($tarifs as $tarif)
                            @php($tahunValid = preg_match('/^\d{4}\/\d{4}$/D', $tarif->tahunAjaran->nama) && (int) substr($tarif->tahunAjaran->nama, 5, 4) === (int) substr($tarif->tahunAjaran->nama, 0, 4) + 1)
                            @if($tarif->jenis !== 'spp' || $tahunValid)
                                <option value="{{ $tarif->id }}" data-jenis="{{ $tarif->jenis }}" data-tahun="{{ $tarif->tahunAjaran->nama }}" data-semester="{{ $tarif->semester }}">{{ strtoupper($tarif->jenis) }} — {{ $tarif->tahunAjaran->nama }} {{ $tarif->semester == 1 ? 'Ganjil' : 'Genap' }} (Rp {{ number_format($tarif->nominal, 0, ',', '.') }})</option>
                            @endif
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </div>
            </div>
            <div>
                <label for="periode_key" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Periode tagihan</label>
                <div class="relative">
                    <select id="periode_key" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-white bg-none px-4 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" disabled><option value="">Pilih tarif dahulu</option></select>
                    <span class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </div>
            </div>
        </div>
        <div id="bill-info" class="mt-5 rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-900/50 dark:text-gray-300" role="status">Pilih siswa, tarif, dan periode untuk melihat tagihan.</div>
        <form id="payment-form" class="mt-5 flex flex-wrap items-end gap-3">
            <div>
                <label for="payment-nominal" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominal pembayaran (Rp)</label>
                <input id="payment-nominal" type="number" min="1" step="1" required disabled class="h-11 w-56 rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 disabled:opacity-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <button id="payment-button" type="submit" disabled class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Konfirmasi Pembayaran</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const scan = document.getElementById('kode_uid');
    const query = document.getElementById('q');
    const tarif = document.getElementById('tarif_id');
    const periode = document.getElementById('periode_key');
    const results = document.getElementById('search-results');
    const message = document.getElementById('search-message');
    const studentInfo = document.getElementById('student-info');
    const billInfo = document.getElementById('bill-info');
    const nominal = document.getElementById('payment-nominal');
    const payButton = document.getElementById('payment-button');
    const money = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value));
    let student = null;
    let bill = null;
    let requestId = 0;
    let submitting = false;

    function invalidateBill() {
        bill = null;
        nominal.value = '';
        nominal.disabled = true;
        nominal.removeAttribute('max');
        payButton.disabled = true;
        billInfo.textContent = student ? 'Pilih tarif dan periode untuk melihat tagihan.' : 'Pilih siswa, tarif, dan periode untuk melihat tagihan.';
    }

    function errorText(response, data) {
        if (response.status === 401 || response.status === 403) return 'Sesi berakhir atau Anda tidak berwenang. Silakan masuk kembali.';
        return Object.values(data?.errors || {}).flat()[0] || data?.message || 'Permintaan gagal. Coba lagi.';
    }

    function chooseStudent(item) {
        student = item.siswa;
        studentInfo.textContent = student.nama_lengkap + ' — NIS ' + student.nis + (student.nisn ? ' / NISN ' + student.nisn : '');
        results.replaceChildren();
        results.classList.add('hidden');
        invalidateBill();
        loadBill();
    }

    function populatePeriods() {
        periode.replaceChildren();
        const option = tarif.selectedOptions[0];
        if (!option.value) {
            periode.add(new Option('Pilih tarif dahulu', ''));
            periode.disabled = true;
        } else if (option.dataset.jenis === 'spp') {
            const year = Number(option.dataset.tahun.slice(0, 4)) + (option.dataset.semester === '2' ? 1 : 0);
            const start = option.dataset.semester === '1' ? 7 : 1;
            for (let month = start; month < start + 6; month++) {
                const key = year + '-' + String(month).padStart(2, '0');
                periode.add(new Option(new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date(year, month - 1, 1)), key));
            }
            periode.disabled = false;
        } else {
            const key = option.dataset.jenis.toUpperCase();
            periode.add(new Option(key + ' — ' + option.dataset.tahun, key));
            periode.disabled = false;
        }
        invalidateBill();
        loadBill();
    }

    async function lookup(params) {
        const id = ++requestId;
        student = null;
        studentInfo.textContent = 'Pilih siswa melalui kartu atau hasil pencarian.';
        invalidateBill();
        results.replaceChildren();
        results.classList.add('hidden');
        message.textContent = 'Mencari siswa...';
        try {
            const url = new URL(@json(route('pembayaran.kasir.cari')), window.location.href);
            const isScan = Object.hasOwn(params, 'kode_uid');
            const response = await fetch(isScan ? url.toString() : (url.search = new URLSearchParams(params).toString(), url.toString()), {
                method: isScan ? 'POST' : 'GET',
                headers: { 'Accept': 'application/json', ...(isScan ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) } : {}) },
                ...(isScan ? { body: JSON.stringify(params) } : {}),
            });
            const data = await response.json();
            if (id !== requestId) return;
            if (!response.ok) throw new Error(errorText(response, data));
            if (!data.data.length) { message.textContent = 'Tidak ada siswa aktif yang cocok.'; return; }
            if (isScan || data.data.length === 1) {
                chooseStudent(data.data[0]);
                message.textContent = 'Siswa ditemukan. Periksa tagihan sebelum membayar.';
                return;
            }
            message.textContent = 'Pilih siswa dari hasil pencarian:';
            results.classList.remove('hidden');
            data.data.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'block w-full rounded-lg border border-gray-200 px-4 py-3 text-left text-sm text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:text-white dark:hover:bg-gray-800';
                button.textContent = item.siswa.nama_lengkap + ' — NIS ' + item.siswa.nis;
                button.addEventListener('click', () => chooseStudent(item));
                results.append(button);
            });
        } catch (error) {
            if (id === requestId) message.textContent = error.message;
        }
    }

    async function loadBill() {
        const id = ++requestId;
        invalidateBill();
        if (!student || !tarif.value || !periode.value) return;
        billInfo.textContent = 'Memuat tagihan...';
        try {
            const url = new URL(@json(route('pembayaran.kasir.cari')), window.location.href);
            url.search = new URLSearchParams({ siswa_id: student.id, tarif_id: tarif.value, periode_key: periode.value }).toString();
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();
            if (id !== requestId) return;
            if (!response.ok) throw new Error(errorText(response, data));
            bill = data.data[0]?.tagihan || null;
            if (!bill) throw new Error('Tagihan tidak ditemukan.');
            const labels = { belum_bayar: 'Belum bayar', sebagian: 'Dibayar sebagian', lunas: 'Lunas' };
            billInfo.textContent = 'Status: ' + (labels[bill.status] || bill.status) + ' · Tagihan: ' + money(bill.nominal) + ' · Dibayar: ' + money(bill.dibayar) + ' · Sisa: ' + money(bill.sisa);
            nominal.max = bill.sisa;
            nominal.disabled = bill.sisa <= 0;
            payButton.disabled = submitting || bill.sisa <= 0;
        } catch (error) {
            if (id === requestId) billInfo.textContent = error.message;
        }
    }

    document.getElementById('scan-form').addEventListener('submit', event => {
        event.preventDefault();
        if (scan.value.trim()) lookup({ kode_uid: scan.value.trim() });
        scan.value = '';
        scan.focus();
    });
    document.getElementById('search-form').addEventListener('submit', event => {
        event.preventDefault();
        if (query.value.trim()) lookup({ q: query.value.trim() });
    });
    tarif.addEventListener('change', populatePeriods);
    periode.addEventListener('change', loadBill);

    document.getElementById('payment-form').addEventListener('submit', async event => {
        event.preventDefault();
        if (!student || !bill || submitting || payButton.disabled) return;
        const amount = Number(nominal.value);
        if (!Number.isSafeInteger(amount) || amount < 1 || amount > Number(bill.sisa)) {
            await Swal.fire({ icon: 'warning', text: 'Nominal harus lebih dari nol dan tidak melebihi sisa tagihan.' });
            return;
        }
        const snapshot = {
            siswa_id: student.id, tarif_id: tarif.value, periode_key: periode.value,
            tagihan_id: bill.tagihan_id, bill, nominal: amount, input: nominal.value, requestId,
        };
        submitting = true;
        payButton.disabled = true;
        try {
            const confirmation = await Swal.fire({ icon: 'question', title: 'Konfirmasi pembayaran?', text: student.nama_lengkap + ' — ' + tarif.selectedOptions[0].textContent + ' — ' + snapshot.periode_key + ' — ' + money(amount), showCancelButton: true, confirmButtonText: 'Ya, catat pembayaran', cancelButtonText: 'Batal' });
            if (!confirmation.isConfirmed) return;
            if (requestId !== snapshot.requestId || student?.id !== snapshot.siswa_id || tarif.value !== snapshot.tarif_id || periode.value !== snapshot.periode_key || bill !== snapshot.bill || nominal.value !== snapshot.input) {
                await Swal.fire({ icon: 'warning', text: 'Pilihan atau nominal berubah. Periksa kembali tagihan sebelum mengonfirmasi.' });
                return;
            }
            const payload = snapshot.tagihan_id
                ? { tagihan_id: snapshot.tagihan_id, nominal: snapshot.nominal }
                : { siswa_id: snapshot.siswa_id, tarif_id: snapshot.tarif_id, periode_key: snapshot.periode_key, nominal: snapshot.nominal };
            const response = await fetch(@json(route('pembayaran.kasir.bayar')), {
                method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(errorText(response, data));
            invalidateBill();
            await Swal.fire({ icon: 'success', title: 'Pembayaran berhasil', text: 'Kode kuitansi: ' + data.kode_kuitansi, confirmButtonText: 'Lihat kuitansi' });
            window.location.assign(data.kuitansi_url);
        } catch (error) {
            await Swal.fire({ icon: 'error', title: 'Pembayaran gagal', text: error.message });
            loadBill();
        } finally {
            submitting = false;
            payButton.disabled = !bill || bill.sisa <= 0;
        }
    });
});
</script>
@endpush
