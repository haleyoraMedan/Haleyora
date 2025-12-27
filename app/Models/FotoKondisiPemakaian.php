<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoKondisiPemakaian extends Model
{
    protected $table = 'foto_kondisi_pemakaian';
    protected $fillable = [
        'pemakaian_id', 'posisi', 'foto_sebelum', 'foto_sesudah'
    ];

    // Relasi ke pemakaian mobil
    public function pemakaianMobil(): BelongsTo
    {
        return $this->belongsTo(PemakaianMobil::class, 'pemakaian_id');
    }
}
