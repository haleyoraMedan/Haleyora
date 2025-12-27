<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PemakaianMobil extends Model
{
    protected $table = 'pemakaian_mobil';
    protected $fillable = [
        'mobil_id', 'user_id', 'tanggal_mulai', 'tanggal_selesai',
        'tujuan', 'jarak_tempuh_km', 'bahan_bakar_liter', 'catatan', 'status'
    ];

    // Relasi ke mobil
    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }

    // Relasi ke user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

public function fotoKondisiPemakaian(): HasMany
{
    return $this->hasMany(FotoKondisiPemakaian::class, 'pemakaian_id');
}


    public function isPending()
{
    return $this->status === 'pending';
}

public function isApproved()
{
    return $this->status === 'approved';
}

public function isRejected()
{
    return $this->status === 'rejected';
}

// Scope untuk pemakaian yang menunggu approval
public function scopeMenungguApproval($query)
{
    return $query->where('status', 'pending');
}

// Scope untuk pemakaian yang aktif
public function scopeAktif($query)
{
    return $query->where('status', 'approved')
                 ->whereNull('tanggal_selesai');
}
public function detail()
{
    return $this->hasOne(DetailMobil::class, 'mobil_id', 'mobil_id');
}

}
