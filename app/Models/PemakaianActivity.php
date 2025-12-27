<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianActivity extends Model
{
    protected $table = 'pemakaian_activities';
    protected $fillable = ['pemakaian_id','user_id','action','data'];

    protected $casts = [
        'data' => 'array',
    ];

    public function pemakaian()
    {
        return $this->belongsTo(PemakaianMobil::class, 'pemakaian_id');
    }
}
