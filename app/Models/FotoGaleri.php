<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoGaleri extends Model
{
    protected $table = 'foto_galeri';

    protected $fillable = ['galeri_id', 'foto'];

    public function galeri(): BelongsTo
    {
        return $this->belongsTo(Galeri::class);
    }
}
