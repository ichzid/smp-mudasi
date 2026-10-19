<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalinRombelController extends Controller
{
    public function create()
    {
        $target = TahunAjaran::where('is_aktif', true)->first();
        $source = null;
        if ($target && preg_match('/^(\d{4})\/(\d{4})$/', $target->nama, $years)) {
            $source = TahunAjaran::where('nama', ($years[1] - 1).'/'.$years[1])->first();
        }

        $message = null;
        if (! $target) {
            $message = 'Aktifkan tahun ajaran tujuan terlebih dahulu.';
        } elseif (! $source) {
            $message = 'Tahun ajaran sebelumnya tidak ditemukan.';
        } elseif (! Rombel::where('tahun_ajaran_id', $source->id)->exists()) {
            $message = 'Tahun ajaran sebelumnya belum memiliki kelas.';
        } elseif (Rombel::where('tahun_ajaran_id', $target->id)->exists()) {
            $message = 'Tahun ajaran tujuan sudah memiliki kelas. Penyalinan hanya berlaku untuk tahun ajaran yang masih kosong.';
        }

        return view('pages.master.rombel.salin', compact('target', 'source', 'message'), ['title' => 'Salin Struktur Kelas']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
            'target_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
        ]);

        DB::transaction(function () use ($data) {
            $target = TahunAjaran::whereKey($data['target_id'])->lockForUpdate()->firstOrFail();
            $source = TahunAjaran::whereKey($data['source_id'])->firstOrFail();
            if (! $target->is_aktif || ! preg_match('/^(\d{4})\/(\d{4})$/', $target->nama, $years) ||
                $source->nama !== ($years[1] - 1).'/'.$years[1]) {
                throw ValidationException::withMessages(['target_id' => 'Hanya struktur kelas tahun ajaran sebelumnya yang dapat disalin ke tahun ajaran aktif.']);
            }

            if (Rombel::where('tahun_ajaran_id', $target->id)->exists()) {
                throw ValidationException::withMessages(['target_id' => 'Tahun ajaran tujuan sudah memiliki kelas. Penyalinan tidak dapat diulang.']);
            }

            // Ambil snapshot genap bila tersedia, selain itu kelas tahunan, lalu snapshot ganjil.
            $classes = Rombel::where('tahun_ajaran_id', $source->id)->orderByRaw('CASE semester WHEN 2 THEN 0 WHEN 1 THEN 2 ELSE 1 END')->orderBy('id')->get();
            if ($classes->isEmpty()) {
                throw ValidationException::withMessages(['source_id' => 'Tahun ajaran sumber belum memiliki kelas.']);
            }
            $classes = $classes->unique('nama');
            if ($classes->whereNotNull('wali_guru_id')->pluck('wali_guru_id')->unique()->count() !== $classes->whereNotNull('wali_guru_id')->count()) {
                throw ValidationException::withMessages(['source_id' => 'Wali kelas pada sumber tidak unik. Perbaiki dahulu sebelum menyalin.']);
            }

            foreach ($classes as $class) {
                Rombel::create([
                    'nama' => $class->nama,
                    'tingkat' => $class->tingkat,
                    'wali_guru_id' => $class->wali_guru_id,
                    'tahun_ajaran_id' => $target->id,
                    'semester' => null,
                ]);
            }
        });

        return redirect()->route('master.rombel.index')->with('success', 'Struktur kelas berhasil disalin. Tempatkan siswa secara manual setelah menentukan kenaikan kelas.');
    }
}
