<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerekMobil extends Model
{
    use HasFactory;

    protected $table = 'merek_mobil';

    protected $fillable = [
        'nama_merek',
    ];

    public function mobil()
    {
        return $this->hasMany(Mobil::class, 'merek_id');
    }
}
