<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifPembayaran extends Model
{
    protected $table = 'tarif_pembayaran';

    protected $fillable = ['tahun_ajaran_id', 'semester', 'jenis', 'nominal'];

    protected $casts = ['semester' => 'integer', 'nominal' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (self $tarif) {
            if ($tarif->semester === null) {
                $tarif->semester = TahunAjaran::findOrFail($tarif->tahun_ajaran_id)->semester;
            }
        });
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function tagihan()
    {
        return $this->hasMany(TagihanPembayaran::class, 'tarif_id');
    }
}
