<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $table = 'obat';

    protected $fillable = [
        'nama_obat',
        'kemasan',
        'stok',
        'harga'
    ];

    // Konstanta untuk batas stok menipis
    const STOK_MENIPIS = 100000;
    const STOK_HABIS = 0;

    public function detailPeriksas(){
        return $this->hasMany(DetailPeriksa::class, 'id_obat');
    }

    // Method untuk mengecek apakah stok cukup
    public function isStockSufficient($jumlah)
    {
        return $this->stok >= $jumlah;
    }

    // Method untuk mengurangi stok dengan validasi
    public function reduceStock($jumlah)
    {
        if (!$this->isStockSufficient($jumlah)) {
            throw new \Exception("Stok {$this->nama_obat} tidak cukup. Tersedia: {$this->stok}, Diminta: {$jumlah}");
        }
        
        $this->decrement('stok', $jumlah);
        return true;
    }

    // Method untuk menambah stok
    public function addStock($jumlah)
    {
        $this->increment('stok', $jumlah);
        return true;
    }

    // Method untuk cek status stok
    public function getStockStatus()
    {
        if ($this->stok <= self::STOK_HABIS) {
            return [
                'status' => 'habis',
                'class' => 'danger',
                'icon' => 'fas fa-times-circle',
                'message' => 'Stok Habis'
            ];
        } elseif ($this->stok <= self::STOK_MENIPIS) {
            return [
                'status' => 'menipis',
                'class' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => 'Stok Menipis'
            ];
        } else {
            return [
                'status' => 'aman',
                'class' => 'success',
                'icon' => 'fas fa-check-circle',
                'message' => 'Stok Aman'
            ];
        }
    }

    // Method untuk format stok dengan warna
    public function getFormattedStock()
    {
        $status = $this->getStockStatus();
        return "<span class='text-{$status['class']}'><i class='{$status['icon']}'></i> {$this->stok} ({$status['message']})</span>";
    }

    // Scope untuk filter berdasarkan status stok
    public function scopeStokHabis($query)
    {
        return $query->where('stok', '<=', self::STOK_HABIS);
    }

    public function scopeStokMenipis($query)
    {
        return $query->where('stok', '>', self::STOK_HABIS)
                     ->where('stok', '<=', self::STOK_MENIPIS);
    }

    public function scopeStokAman($query)
    {
        return $query->where('stok', '>', self::STOK_MENIPIS);
    }
}