<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\PengaturanSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function edit()
    {
        // Ambil data pengaturan, jika kosong buat object baru dengan default
        $pengaturan = PengaturanSekolah::first() ?? new PengaturanSekolah([
            'nama_sekolah' => 'SMP Muhammadiyah Danau Sijabut',
            'jumlah_siswa' => 0,
            'jumlah_guru' => 0,
            'jumlah_prestasi' => 0,
        ]);

        return view('pages.website.pengaturan.edit', [
            'pengaturan' => $pengaturan,
            'title' => 'Pengaturan Web'
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'nomor_telepon' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'profil_singkat' => 'nullable|string',
            'nama_kepala_sekolah' => 'nullable|string|max:255',
            'sambutan_kepala_sekolah' => 'nullable|string',
            'jumlah_siswa' => 'required|integer|min:0',
            'jumlah_guru' => 'required|integer|min:0',
            'jumlah_prestasi' => 'required|integer|min:0',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'foto_kepala_sekolah' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $pengaturan = PengaturanSekolah::first() ?? new PengaturanSekolah();

        $data = $request->except(['logo', 'foto_kepala_sekolah']);

        // Handle upload logo
        if ($request->hasFile('logo')) {
            if ($pengaturan->logo && Storage::disk('public')->exists($pengaturan->logo)) {
                Storage::disk('public')->delete($pengaturan->logo);
            }
            $data['logo'] = $request->file('logo')->store('website/logo', 'public');
        }

        // Handle upload foto kepsek
        if ($request->hasFile('foto_kepala_sekolah')) {
            if ($pengaturan->foto_kepala_sekolah && Storage::disk('public')->exists($pengaturan->foto_kepala_sekolah)) {
                Storage::disk('public')->delete($pengaturan->foto_kepala_sekolah);
            }
            $data['foto_kepala_sekolah'] = $request->file('foto_kepala_sekolah')->store('website/kepala-sekolah', 'public');
        }

        $pengaturan->fill($data);
        $pengaturan->save();

        return redirect()->route('website.pengaturan.edit')
            ->with('success', 'Pengaturan website berhasil diperbarui!');
    }
}
