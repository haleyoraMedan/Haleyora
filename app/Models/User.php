<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nip',
        'username',
        'password',
        'role',
        'penempatan_id',
        'is_deleted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    /**
     * Hash password otomatis
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Laravel Auth pakai username
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'penempatan_id');
    }

    public function pemakaian()
{
    // Semua pemakaian yang dibuat user ini
    return $this->hasMany(PemakaianMobil::class, 'user_id');
}

}
