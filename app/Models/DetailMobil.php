<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailMobil extends Model
{
    protected $table = 'detail_mobil';
    protected $fillable = [
        'mobil_id', 'kilometer', 'bahan_bakar', 'transmisi', 'kondisi',
        'depan','belakang','kanan','kiri','joksabuk','acventilasi','panelaudio',
        'lampukabin','interior_bersih','toolkitdongkrak'
    ];

    // Relasi ke mobil
    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }
}
