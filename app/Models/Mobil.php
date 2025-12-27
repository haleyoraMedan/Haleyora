<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    use HasFactory;

    protected $table = 'mobil';

    protected $fillable = [
        'no_polisi',
        'merek_id',
        'jenis_id',
        'tipe',
        'tahun',
        'warna',
        'no_rangka',
        'no_mesin',
        'penempatan_id', 
    ];

    /* ================= RELASI ================= */

    public function merek()
    {
        return $this->belongsTo(MerekMobil::class, 'merek_id');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisMobil::class, 'jenis_id');
    }



    public function foto()
    {
        return $this->hasMany(MobilFoto::class, 'mobil_id');
    }

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'penempatan_id');
    }

    public function pemakaian()
{
    // Semua pemakaian mobil ini
    return $this->hasMany(PemakaianMobil::class, 'mobil_id');
}

// Scope untuk mobil yang tersedia di penempatan tertentu
public function scopeTersedia($query, $penempatanId)
{
    return $query->where('penempatan_id', $penempatanId)
                 ->whereDoesntHave('pemakaian', function($q){
                     // Filter pemakaian yang masih pending atau ongoing
                     $q->whereIn('status', ['pending', 'approved'])
                       ->whereNull('tanggal_selesai');
                 });
}

// PemakaianMobil.php
public function mobil() {
    return $this->belongsTo(Mobil::class, 'mobil_id');
}

public function detail() {
    return $this->hasOne(DetailMobil::class, 'mobil_id', 'mobil_id');
}

public function fotoKondisiPemakaian() {
    return $this->hasMany(FotoKondisiPemakaian::class, 'pemakaian_id');
}


}
