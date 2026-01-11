<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanRusak extends Model
{
    use HasFactory;

    protected $table = 'laporan_rusak';

    protected $fillable = [
        'user_id', 'mobil_id', 'kondisi', 'catatan', 'lokasi'
    ];

    public function mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fotos()
    {
        return $this->hasMany(LaporanRusakFoto::class, 'laporan_rusak_id');
    }
}
