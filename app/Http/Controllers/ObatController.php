<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = Obat::query();
        
        // Filter berdasarkan status stok
        if ($request->has('status')) {
            switch ($request->status) {
                case 'habis':
                    $query = $query->stokHabis();
                    break;
                case 'menipis':
                    $query = $query->stokMenipis();
                    break;
                case 'aman':
                    $query = $query->stokAman();
                    break;
            }
        }
        
        $obats = $query->get();
        
        // Hitung statistik stok
        $totalObat = $obats->count();
        $stokHabis = Obat::stokHabis()->count();
        $stokMenipis = Obat::stokMenipis()->count();
        $stokAman = Obat::stokAman()->count();
        
        return view('admin.obat.index', compact('obats', 'totalObat', 'stokHabis', 'stokMenipis', 'stokAman'));
    }

    public function create()
    {
        return view('admin.obat.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_obat' => 'required|string|max:255|unique:obat,nama_obat',
            'kemasan' => 'required|string|max:35',
            'stok' => 'required|integer|min:0|max:999999',
            'harga' => 'required|integer|min:0|max:99999999',
        ]);

        Obat::create([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'stok' => $request->stok,
            'harga' => $request->harga
        ]);

        return redirect()->route('obat.index')
            ->with('message', 'Data obat berhasil ditambahkan')
            ->with('type', 'success');
    }

    public function edit(string $id)
    {
        $obat = Obat::findOrFail($id);
        return view('admin.obat.edit', compact('obat'));
    }

    public function update(Request $request, string $id)
    {
        $obat = Obat::findOrFail($id);
        
        $request->validate([
            'nama_obat' => 'required|string|max:255|unique:obat,nama_obat,' . $obat->id,
            'kemasan' => 'required|string|max:35',
            'stok' => 'required|integer|min:0|max:999999',
            'harga' => 'required|integer|min:0|max:99999999',
        ]);

        // Catat perubahan stok
        $stokLama = $obat->stok;
        $stokBaru = $request->stok;
        $perubahanStok = $stokBaru - $stokLama;
        
        // Log perubahan stok (jika ada tabel stok_log)
        if ($perubahanStok != 0 && \Illuminate\Support\Facades\Schema::hasTable('stok_log')) {
            $tipe = $perubahanStok > 0 ? 'masuk' : 'keluar';
            
            if (class_exists('App\Models\StokLog')) {
                \App\Models\StokLog::create([
                    'id_obat' => $obat->id,
                    'jumlah' => abs($perubahanStok),
                    'tipe' => $tipe,
                    'keterangan' => 'Update data obat melalui form edit',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                DB::table('stok_log')->insert([
                    'id_obat' => $obat->id,
                    'jumlah' => abs($perubahanStok),
                    'tipe' => $tipe,
                    'keterangan' => 'Update data obat melalui form edit',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        // Update data obat
        $obat->update([
            'nama_obat' => $request->nama_obat,
            'kemasan' => $request->kemasan,
            'stok' => $request->stok,
            'harga' => $request->harga
        ]);

        // Notifikasi jika stok menipis atau habis
        $this->checkStockNotification($obat, $stokLama, $stokBaru);
        
        // Tambahkan flash message tentang perubahan stok
        if ($perubahanStok > 0) {
            $pesan = "Data obat berhasil diubah. Stok ditambahkan: +{$perubahanStok}";
        } elseif ($perubahanStok < 0) {
            $pesan = "Data obat berhasil diubah. Stok dikurangi: {$perubahanStok}";
        } else {
            $pesan = "Data obat berhasil diubah";
        }

        return redirect()->route('obat.index')
            ->with('message', $pesan)
            ->with('type', 'success');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $obat = Obat::findOrFail($id);
            
            // Cek apakah obat masih digunakan dalam pemeriksaan
            if ($obat->detailPeriksas()->exists()) {
                return redirect()->route('obat.index')
                    ->with('message', 'Obat tidak dapat dihapus karena masih digunakan dalam pemeriksaan')
                    ->with('type', 'danger');
            }

            // Hapus log stok terkait (jika ada tabel stok_log dan model StokLog)
            if (\Illuminate\Support\Facades\Schema::hasTable('stok_log')) {
                // Cek apakah model StokLog ada sebelum menggunakannya
                if (class_exists('App\Models\StokLog')) {
                    \App\Models\StokLog::where('id_obat', $obat->id)->delete();
                } else {
                    // Jika model tidak ada, hapus langsung menggunakan DB
                    DB::table('stok_log')->where('id_obat', $obat->id)->delete();
                }
            }

            // Hapus notifikasi terkait obat (jika ada tabel notifications dan model Notification)
            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                // Cek apakah model Notification ada sebelum menggunakannya
                if (class_exists('App\Models\Notification')) {
                    \App\Models\Notification::where('message', 'LIKE', '%' . $obat->nama_obat . '%')->delete();
                } else {
                    // Jika model tidak ada, hapus langsung menggunakan DB
                    DB::table('notifications')->where('message', 'LIKE', '%' . $obat->nama_obat . '%')->delete();
                }
            }

            // Hapus obat
            $obat->delete();
            
            DB::commit();

            return redirect()->route('obat.index')
                ->with('message', 'Data obat berhasil dihapus')
                ->with('type', 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('obat.index')
                ->with('message', 'Gagal menghapus obat: ' . $e->getMessage())
                ->with('type', 'danger');
        }
    }

    // Method untuk restok obat
    public function restock(Request $request, $id)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1|max:9999',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $obat = Obat::findOrFail($id);
        $stokLama = $obat->stok;
        
        DB::beginTransaction();
        try {
            // Tambah stok
            $obat->addStock($request->jumlah);
            
            // Catat di stok_log (jika ada tabel dan modelnya)
            if (\Illuminate\Support\Facades\Schema::hasTable('stok_log')) {
                if (class_exists('App\Models\StokLog')) {
                    \App\Models\StokLog::create([
                        'id_obat' => $obat->id,
                        'jumlah' => $request->jumlah,
                        'tipe' => 'masuk',
                        'keterangan' => $request->keterangan ?? 'Restok oleh admin'
                    ]);
                } else {
                    // Jika model tidak ada, insert langsung ke DB
                    DB::table('stok_log')->insert([
                        'id_obat' => $obat->id,
                        'jumlah' => $request->jumlah,
                        'tipe' => 'masuk',
                        'keterangan' => $request->keterangan ?? 'Restok oleh admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            DB::commit();
            
            // Cek notifikasi
            $this->checkStockNotification($obat, $stokLama, $obat->stok);
            
            return redirect()->route('obat.index')
                ->with('message', 'Stok obat berhasil ditambahkan')
                ->with('type', 'success');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('obat.index')
                ->with('message', 'Gagal menambah stok: ' . $e->getMessage())
                ->with('type', 'danger');
        }
    }

    // Method untuk cek dan kirim notifikasi stok
    private function checkStockNotification($obat, $stokLama, $stokBaru)
    {
        $statusBaru = $obat->getStockStatus();
        
        // Jika stok baru habis atau menipis, dan sebelumnya tidak
        if (($statusBaru['status'] == 'habis' || $statusBaru['status'] == 'menipis') && 
            $stokLama > Obat::STOK_MENIPIS) {
            
            // Simpan notifikasi (jika ada tabel dan modelnya)
            if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                if (class_exists('App\Models\Notification')) {
                    \App\Models\Notification::create([
                        'user_id' => auth()->id(),
                        'title' => 'Peringatan Stok Obat',
                        'message' => "Obat {$obat->nama_obat} {$statusBaru['message']} (Stok: {$stokBaru})",
                        'type' => 'warning',
                        'read' => false
                    ]);
                } else {
                    // Jika model tidak ada, insert langsung ke DB
                    DB::table('notifications')->insert([
                        'user_id' => auth()->id(),
                        'title' => 'Peringatan Stok Obat',
                        'message' => "Obat {$obat->nama_obat} {$statusBaru['message']} (Stok: {$stokBaru})",
                        'type' => 'warning',
                        'read' => false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}