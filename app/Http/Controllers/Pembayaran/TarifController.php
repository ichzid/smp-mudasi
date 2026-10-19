<?php

namespace App\Http\Controllers\Pembayaran;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use App\Models\TarifPembayaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TarifController extends Controller
{
    public function index()
    {
        return view('pages.pembayaran.tarif.index', [
            'title' => 'Tarif Pembayaran',
            'tahunAjarans' => TahunAjaran::orderByDesc('nama')->get(),
            'tarifs' => TarifPembayaran::with('tahunAjaran')->orderByDesc('tahun_ajaran_id')->orderBy('semester')->orderBy('jenis')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'semester' => ['required', 'integer', Rule::in([1, 2])],
            'jenis' => ['required', Rule::in(['spp', 'uts', 'uas']), Rule::unique('tarif_pembayaran')->where('tahun_ajaran_id', $request->input('tahun_ajaran_id'))->where('semester', $request->input('semester'))],
            'nominal' => ['required', 'integer', 'min:1', 'max:9223372036854775807'],
        ]);
        if ($data['jenis'] === 'spp') {
            $nama = TahunAjaran::findOrFail($data['tahun_ajaran_id'])->nama;
            if (! preg_match('/^\d{4}\/\d{4}$/D', $nama) || (int) substr($nama, 5, 4) !== (int) substr($nama, 0, 4) + 1) {
                throw ValidationException::withMessages(['tahun_ajaran_id' => 'Format tahun ajaran untuk SPP harus YYYY/YYYY dengan tahun kedua satu tahun setelah tahun pertama.']);
            }
        }
        TarifPembayaran::create($data);

        return redirect()->route('pembayaran.tarif.index')->with('success', 'Tarif berhasil ditambahkan.');
    }

    public function update(Request $request, TarifPembayaran $tarif)
    {
        $data = $request->validate([
            'nominal' => ['required', 'integer', 'min:1', 'max:9223372036854775807'],
        ]);
        $tarif->update($data);

        return redirect()->route('pembayaran.tarif.index')->with('success', 'Tarif berhasil diperbarui. Tagihan yang sudah dibuat tetap memakai nominal sebelumnya.');
    }
}
