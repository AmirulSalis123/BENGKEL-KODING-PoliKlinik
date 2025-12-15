<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokLog extends Model
{
    protected $table = 'stok_log';

    protected $fillable = [
        'id_obat',
        'id_periksa',
        'jumlah',
        'tipe',
        'keterangan',
    ];

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'id_obat');
    }

    public function periksa()
    {
        return $this->belongsTo(Periksa::class, 'id_periksa');
    }
}
