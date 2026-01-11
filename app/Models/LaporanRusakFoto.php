<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanRusakFoto extends Model
{
    protected $table = 'laporan_rusak_fotos';

    protected $fillable = [
        'laporan_rusak_id', 'posisi', 'file_path'
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanRusak::class, 'laporan_rusak_id');
    }
}
