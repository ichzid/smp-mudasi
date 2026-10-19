<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanPembayaran extends Model
{
    protected $table = 'tagihan_pembayaran';

    protected $fillable = ['siswa_id', 'tarif_id', 'periode_key', 'nominal'];

    protected $casts = ['nominal' => 'integer'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tarif()
    {
        return $this->belongsTo(TarifPembayaran::class, 'tarif_id');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiPembayaran::class, 'tagihan_id');
    }

    public static function statusUntuk(Siswa $siswa, TarifPembayaran $tarif, string $periodeKey): array
    {
        $tagihan = static::withSum('transaksi', 'nominal')
            ->where('siswa_id', $siswa->id)->where('tarif_id', $tarif->id)
            ->where('periode_key', $periodeKey)->first();
        $nominal = $tagihan?->nominal ?? $tarif->nominal;
        $dibayar = (int) ($tagihan?->transaksi_sum_nominal ?? 0);

        return [
            'tagihan_id' => $tagihan?->id,
            'tarif_id' => $tarif->id,
            'jenis' => $tarif->jenis,
            'periode_key' => $periodeKey,
            'nominal' => $nominal,
            'dibayar' => $dibayar,
            'sisa' => $nominal - $dibayar,
            'status' => $dibayar >= $nominal ? 'lunas' : ($dibayar > 0 ? 'sebagian' : 'belum_bayar'),
        ];
    }
}
