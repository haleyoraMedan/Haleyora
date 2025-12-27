<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisMobil extends Model
{
    use HasFactory;

    protected $table = 'jenis_mobil';

    protected $fillable = [
        'nama_jenis',
    ];

    public function mobil()
    {
        return $this->hasMany(Mobil::class, 'jenis_id');
    }
}
