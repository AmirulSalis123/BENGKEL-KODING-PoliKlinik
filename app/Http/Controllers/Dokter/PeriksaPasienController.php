<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\StokLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        // Hanya tampilkan obat dengan stok > 0
        $obats = Obat::where('stok', '>', 0)->get();
        $daftarPoli = DaftarPoli::with('pasien')->findOrFail($id);
        
        return view('dokter.periksa-pasien.create', compact('obats', 'id', 'daftarPoli'));
    }

    // masih kurang benar
    public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required|array',
            'obat_json.*' => 'required|exists:obat,id',
            'obat_jumlah' => 'required|array',
            'obat_jumlah.*' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer|min:0',
        ]);

        $obatIds = $request->obat_json;
        $obatJumlah = $request->obat_jumlah;

        // Validasi stok sebelum transaction
        foreach ($obatIds as $index => $obatId) {
            $obat = Obat::findOrFail($obatId);
            $jumlah = $obatJumlah[$index] ?? 1;
            
            if (!$obat->isStockSufficient($jumlah)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Stok {$obat->nama_obat} tidak cukup. Tersedia: {$obat->stok}, Diminta: {$jumlah}");
            }
        }

        // Hitung total harga obat
        $totalHargaObat = 0;
        foreach ($obatIds as $index => $obatId) {
            $obat = Obat::find($obatId);
            if ($obat) {
                $jumlah = $obatJumlah[$index] ?? 1;
                $totalHargaObat += $obat->harga * $jumlah;
            }
        }

        DB::beginTransaction();
        try {
            // 1. Buat data pemeriksaan
            $periksa = Periksa::create([
                'id_daftar_poli' => $request->id_daftar_poli,
                'tgl_periksa' => now(),
                'catatan' => $request->catatan,
                'biaya_periksa' => $request->biaya_periksa + $totalHargaObat,
            ]);

            // dd($periksa);
            // 2. Simpan detail obat dan kurangi stok
            foreach ($obatIds as $index => $obatId) {
                $obat = Obat::findOrFail($obatId);
                $jumlah = $obatJumlah[$index] ?? 1;

                // Kurangi stok obat
                $obat->reduceStock($jumlah);

                // Simpan detail periksa
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $obat->id,
                    'jumlah' => $jumlah,
                ]);

                // Simpan log pengurangan stok
                if (Schema::hasTable('stok_log')) {
                    StokLog::create([
                        'id_obat' => $obat->id,
                        'id_periksa' => $periksa->id,
                        'jumlah' => $jumlah,
                        'tipe' => 'keluar',
                        'keterangan' => 'Penggunaan untuk pemeriksaan pasien'
                    ]);
                }

                // Cek dan kirim notifikasi jika stok menipis/habis
                $this->checkAndNotifyLowStock($obat);
            }

            // dd($obatIds);
            DB::commit();


            return redirect()->route('periksa-pasien.index')
                ->with('success', 'Data periksa berhasil disimpan dan stok obat telah diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
    

    // Method untuk cek dan notifikasi stok rendah
    private function checkAndNotifyLowStock($obat)
    {
        $status = $obat->getStockStatus();
        
        if ($status['status'] == 'habis' || $status['status'] == 'menipis') {
            // Simpan notifikasi (bisa dikembangkan ke database notifications)
            session()->flash('stock_warning', [
                'obat' => $obat->nama_obat,
                'stok' => $obat->stok,
                'status' => $status['message']
            ]);
        }
    }

    // API untuk cek stok real-time (dipakai di JavaScript)
    public function checkStock(Request $request)
    {
        $request->validate([
            'obat_id' => 'required|exists:obat,id',
            'jumlah' => 'required|integer|min:1'
        ]);

        $obat = Obat::findOrFail($request->obat_id);
        
        return response()->json([
            'sufficient' => $obat->isStockSufficient($request->jumlah),
            'stok_tersedia' => $obat->stok,
            'status' => $obat->getStockStatus(),
            'message' => $obat->isStockSufficient($request->jumlah) 
                ? 'Stok mencukupi' 
                : "Stok tidak mencukupi. Tersedia: {$obat->stok}"
        ]);
    }
}