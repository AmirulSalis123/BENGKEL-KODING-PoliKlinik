<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemakaianObat extends Model
{
    protected $table = 'pemakaian_obat';

    protected $fillable = [
        'id_obat',
        'jumlah',
        'nama_dokter',
        'keterangan',
        'tanggal_pemakaian'
    ];

    protected $dates = ['tanggal_pemakaian'];

    // Relasi ke Obat
    public function obat()
    {
        return $this->belongsTo(Obat::class, 'id_obat');
    }
}