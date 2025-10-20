<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable{
    protected $fillable = [
    'nama',
    'alamat',
    'no_ktp',
    'role',
    'email',
    'id poli',
    'password'
    ];

protected $shidden =[

    'password',
    'remember_token',
];

protected function casts(): array
{
return[
    'email verified at' =>'datetime',
    'password' => 'hashed',
];
}

public Function poli(){
    return $this->belongsTo (Poli::class, 'id polt');
}

public function jadwalPeriksas(){
    return $this->hasMany (JadwalPeriksa::class, 'id dokter');
    }
}