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
        'tahun',
        'warna',
        'no_rangka',
        'no_mesin',
        'penempatan_id', 
        'is_deleted',
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

    public function detail()
    {
        return $this->hasOne(DetailMobil::class, 'mobil_id');
    }
}
