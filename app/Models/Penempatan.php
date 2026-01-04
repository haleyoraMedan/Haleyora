<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penempatan extends Model
{
    use HasFactory;

    protected $table = 'penempatan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'kode_kantor',
        'nama_kantor',
        'alamat',
        'kota',
        'provinsi',
        'is_deleted',
    ];
}
