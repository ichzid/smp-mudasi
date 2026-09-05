<?php

namespace App\Http\Controllers;

use App\Models\JadwalPresensi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalPresensiController extends Controller
{
    public function index()
    {
        return view('pages.pengaturan-presensi.index', ['title' => 'Pengaturan Presensi', 'jadwals' => JadwalPresensi::orderBy('hari')->get()]);
    }

    public function update(Request $request, JadwalPresensi $jadwalPresensi)
    {
        abort_unless($request->user()->role === 'admin', 403);
        $request->merge(['is_aktif' => $request->boolean('is_aktif')]);
        $validated = $request->validate([
            'hari' => ['required', 'integer', 'between:1,7', Rule::unique('jadwal_presensi', 'hari')->ignore($jadwalPresensi)],
            'is_aktif' => ['required', 'boolean'],
            'jam_mulai_masuk' => [Rule::requiredIf($request->boolean('is_aktif')), 'nullable', 'date_format:H:i'],
            'batas_terlambat' => [Rule::requiredIf($request->boolean('is_aktif')), 'nullable', 'date_format:H:i', 'after_or_equal:jam_mulai_masuk'],
            'jam_mulai_pulang' => [Rule::requiredIf($request->boolean('is_aktif')), 'nullable', 'date_format:H:i', 'after:batas_terlambat'],
            'jam_akhir_pulang' => ['nullable', 'date_format:H:i', 'after_or_equal:jam_mulai_pulang'],
        ], [], [
            'jam_mulai_masuk' => 'jam mulai masuk', 'batas_terlambat' => 'batas terlambat',
            'jam_mulai_pulang' => 'jam mulai pulang', 'jam_akhir_pulang' => 'jam akhir pulang',
        ]);
        $jadwalPresensi->update($validated);

        return back()->with('success', "Jadwal {$jadwalPresensi->nama_hari} berhasil diperbarui.");
    }
}
