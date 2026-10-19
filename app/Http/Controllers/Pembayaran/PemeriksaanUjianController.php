<?php

namespace App\Http\Controllers\Pembayaran;

use App\Http\Controllers\Controller;
use App\Models\KartuRfid;
use App\Models\Siswa;
use App\Models\TagihanPembayaran;
use App\Models\TarifPembayaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PemeriksaanUjianController extends Controller
{
    public function index()
    {
        return view('pages.pembayaran.ujian.index', [
            'title' => 'Pemeriksaan Pembayaran Ujian',
            'tarifs' => TarifPembayaran::with('tahunAjaran')->whereIn('jenis', ['uts', 'uas'])
                ->orderByDesc('tahun_ajaran_id')->orderBy('semester')->orderBy('jenis')->get(),
        ]);
    }

    public function cek(Request $request)
    {
        $request->merge(['kode_uid' => strtoupper(trim((string) $request->input('kode_uid')))]);
        $data = $request->validate([
            'kode_uid' => ['required', 'string', 'max:32'],
            'tarif_id' => ['required', 'integer', Rule::exists('tarif_pembayaran', 'id')->whereIn('jenis', ['uts', 'uas'])],
        ]);
        $kartu = KartuRfid::where('kode_uid', $data['kode_uid'])->where('status', 'aktif')->first();
        $siswa = $kartu ? Siswa::whereKey($kartu->siswa_id)->where('status', 'aktif')->first() : null;
        if (! $siswa) {
            throw ValidationException::withMessages(['kode_uid' => 'Kartu atau siswa tidak aktif/tidak ditemukan.']);
        }
        $tarif = TarifPembayaran::with('tahunAjaran')->findOrFail($data['tarif_id']);

        return response()->json([
            'success' => true,
            'siswa' => $siswa->only(['id', 'nis', 'nisn', 'nama_lengkap', 'status', 'foto_url']),
            'tarif' => $tarif,
            'tagihan' => TagihanPembayaran::statusUntuk($siswa, $tarif, strtoupper($tarif->jenis)),
        ]);
    }
}
