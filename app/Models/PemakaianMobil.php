<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function detail(): HasOne
    {
        // Detail mobil disimpan di tabel `detail_mobil` dan terkait ke mobil via `mobil_id`.
        // Karena model PemakaianMobil memiliki kolom `mobil_id`, relasi ini
        // menghubungkan record pemakaian ke detail mobil menggunakan mobil_id.
        return $this->hasOne(DetailMobil::class, 'mobil_id', 'mobil_id');
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
    return $query
        ->where('status', 'available')
        ->where(function ($q) {
            $q->whereNull('tanggal_selesai')
              ->orWhereDate('tanggal_selesai', '>=', Carbon::today());
        });
}


}