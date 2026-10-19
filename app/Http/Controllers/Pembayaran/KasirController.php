<?php

namespace App\Http\Controllers\Pembayaran;

use App\Http\Controllers\Controller;
use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\TagihanPembayaran;
use App\Models\TarifPembayaran;
use App\Models\TransaksiPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    public function index()
    {
        return view('pages.pembayaran.kasir.index', [
            'title' => 'Kasir Pembayaran',
            'tarifs' => TarifPembayaran::with('tahunAjaran')->orderByDesc('tahun_ajaran_id')->orderBy('semester')->orderBy('jenis')->get(),
        ]);
    }

    public function cari(Request $request)
    {
        $request->merge(['kode_uid' => strtoupper(trim((string) $request->input('kode_uid')))]);
        $data = $request->validate([
            'kode_uid' => ['nullable', 'string', 'max:32'],
            'siswa_id' => ['nullable', 'integer', 'exists:siswa,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'tarif_id' => ['nullable', 'integer', 'exists:tarif_pembayaran,id'],
            'periode_key' => ['nullable', 'string', 'max:7'],
        ]);
        if (empty($data['kode_uid']) && empty($data['siswa_id']) && empty($data['q'])) {
            throw ValidationException::withMessages(['q' => 'Isi UID, siswa, atau nama/NIS.']);
        }

        if (! empty($data['kode_uid'])) {
            $kartu = KartuRfid::where('kode_uid', $data['kode_uid'])->where('status', 'aktif')->first();
            $siswa = $kartu ? Siswa::whereKey($kartu->siswa_id)->where('status', 'aktif')->first() : null;
            if (! $siswa) {
                throw ValidationException::withMessages(['kode_uid' => 'Kartu atau siswa tidak aktif/tidak ditemukan.']);
            }
            $siswas = collect([$siswa]);
        } elseif (! empty($data['siswa_id'])) {
            $siswas = Siswa::whereKey($data['siswa_id'])->where('status', 'aktif')->get();
        } else {
            $siswas = Siswa::where('status', 'aktif')->where(function ($query) use ($data) {
                $query->where('nis', 'like', '%'.$data['q'].'%')->orWhere('nama_lengkap', 'like', '%'.$data['q'].'%');
            })->orderBy('nama_lengkap')->limit(20)->get();
        }

        $tarif = ! empty($data['tarif_id']) ? TarifPembayaran::find($data['tarif_id']) : null;
        $periodeKey = $tarif ? $this->periodeKey($tarif, $data['periode_key'] ?? null) : null;
        $results = $siswas->map(fn ($siswa) => [
            'siswa' => $siswa->only(['id', 'nis', 'nisn', 'nama_lengkap', 'status', 'foto_url']),
            'tagihan' => $tarif ? TagihanPembayaran::statusUntuk($siswa, $tarif, $periodeKey) : [],
        ]);

        return response()->json(['success' => true, 'data' => $results]);
    }

    public function bayar(Request $request)
    {
        $data = $request->validate([
            'tagihan_id' => ['nullable', 'integer', 'exists:tagihan_pembayaran,id'],
            'siswa_id' => ['required_without:tagihan_id', 'nullable', 'integer', 'exists:siswa,id'],
            'tarif_id' => ['required_without:tagihan_id', 'nullable', 'integer', 'exists:tarif_pembayaran,id'],
            'periode_key' => ['required_without:tagihan_id', 'nullable', 'string', 'max:7'],
            'nominal' => ['required', 'integer', 'min:1', 'max:9223372036854775807'],
        ]);

        $transaksi = DB::transaction(function () use ($data, $request) {
            $existing = ! empty($data['tagihan_id']) ? TagihanPembayaran::findOrFail($data['tagihan_id']) : null;
            $siswa = Siswa::whereKey($existing?->siswa_id ?? $data['siswa_id'])->lockForUpdate()->firstOrFail();
            if ($siswa->status !== 'aktif') {
                throw ValidationException::withMessages(['siswa_id' => 'Siswa tidak aktif.']);
            }
            $tarif = TarifPembayaran::whereKey($existing?->tarif_id ?? $data['tarif_id'])->lockForUpdate()->firstOrFail();
            $periodeKey = $this->periodeKey($tarif, $existing?->periode_key ?? ($data['periode_key'] ?? null));
            $tagihan = TagihanPembayaran::where('siswa_id', $siswa->id)->where('tarif_id', $tarif->id)
                ->where('periode_key', $periodeKey)->lockForUpdate()->first();
            if (! $tagihan) {
                $tagihan = TagihanPembayaran::create([
                    'siswa_id' => $siswa->id, 'tarif_id' => $tarif->id,
                    'periode_key' => $periodeKey, 'nominal' => $tarif->nominal,
                ]);
            }
            $dibayar = (int) $tagihan->transaksi()->sum('nominal');
            if ($dibayar >= $tagihan->nominal || $data['nominal'] > $tagihan->nominal - $dibayar) {
                throw ValidationException::withMessages(['nominal' => 'Nominal melebihi sisa tagihan atau tagihan sudah lunas.']);
            }

            return $tagihan->transaksi()->create([
                'nominal' => $data['nominal'],
                'user_id' => $request->user()->id,
                'dibayar_pada' => now('Asia/Jakarta'),
                'kode_kuitansi' => 'BYR-'.strtoupper((string) Str::ulid()),
            ]);
        }, 3);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'transaksi_id' => $transaksi->id, 'kode_kuitansi' => $transaksi->kode_kuitansi, 'kuitansi_url' => route('pembayaran.kasir.kuitansi', $transaksi->id)], 201);
        }

        return redirect()->route('pembayaran.kasir.kuitansi', $transaksi->id)->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function kuitansi(TransaksiPembayaran $transaksi)
    {
        $transaksi->load(['user', 'tagihan.siswa', 'tagihan.tarif.tahunAjaran']);
        $tagihan = $transaksi->tagihan;
        $dibayar = (int) $tagihan->transaksi()->where('id', '<=', $transaksi->id)->sum('nominal');

        return view('pages.pembayaran.kasir.kuitansi', [
            'title' => 'Kuitansi Pembayaran', 'transaksi' => $transaksi, 'tagihan' => $tagihan,
            'dibayar' => $dibayar, 'sisa' => $tagihan->nominal - $dibayar,
        ]);
    }

    private function periodeKey(TarifPembayaran $tarif, ?string $key): string
    {
        if ($tarif->jenis !== 'spp') {
            $expected = strtoupper($tarif->jenis);
            if ($key !== null && $key !== $expected) {
                throw ValidationException::withMessages(['periode_key' => 'Periode UTS/UAS harus '.$expected.'.']);
            }

            return $expected;
        }

        $tahun = $tarif->tahunAjaran;
        $awal = (int) substr($tahun->nama, 0, 4);
        if (! preg_match('/^\d{4}\/\d{4}$/', $tahun->nama) || (int) substr($tahun->nama, 5, 4) !== $awal + 1) {
            throw ValidationException::withMessages(['periode_key' => 'Format tahun ajaran tidak valid untuk SPP bulanan.']);
        }
        $year = $tarif->semester == 1 ? $awal : $awal + 1;
        $months = $tarif->semester == 1 ? '0[7-9]|1[0-2]' : '0[1-6]';
        if (! $key || ! preg_match('/^'.$year.'-('.$months.')$/D', $key)) {
            throw ValidationException::withMessages(['periode_key' => 'Bulan SPP harus berada dalam semester tarif (YYYY-MM).']);
        }

        return $key;
    }
}
