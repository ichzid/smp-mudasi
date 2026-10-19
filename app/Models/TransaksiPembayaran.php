<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPembayaran extends Model
{
    protected $table = 'transaksi_pembayaran';

    protected $fillable = ['tagihan_id', 'nominal', 'user_id', 'dibayar_pada', 'kode_kuitansi'];

    protected $casts = ['nominal' => 'integer', 'dibayar_pada' => 'datetime'];

    public function tagihan()
    {
        return $this->belongsTo(TagihanPembayaran::class, 'tagihan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
