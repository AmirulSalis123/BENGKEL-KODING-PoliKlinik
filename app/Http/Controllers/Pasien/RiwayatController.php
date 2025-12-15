<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index(){
        // Ambil user yang sedang login
        $user = Auth::user();
        
        // Hanya ambil riwayat periksa untuk pasien yang sedang login
        $riwayatPeriksa = Periksa::with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter.poli',
            'detailPeriksas.obat'
        ])
        ->whereHas('daftarPoli.pasien', function($query) use ($user) {
            $query->where('id', $user->id);
        })
        ->orderBy('tgl_periksa', 'desc')
        ->get();

        return view('pasien.riwayat.index', compact('riwayatPeriksa', 'user'));
    }
}