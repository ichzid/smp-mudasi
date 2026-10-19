<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoFasilitas extends Model
{
    protected $table = 'foto_fasilitas';

    protected $fillable = ['fasilitas_id', 'foto'];

    public function fasilitas(): BelongsTo
    {
        return $this->belongsTo(Fasilitas::class);
    }
}
