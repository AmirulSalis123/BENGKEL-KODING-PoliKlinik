<?php

namespace App\Http\Controllers;

use App\Models\Obat;
use Illuminate\Http\Request;

class ObatController extends Controller
{
  public function index()
  {
    $obats = Obat::all();
    return view('admin.obat.index', compact('obats'));
  }

  public function create()
  {
    return view('admin.obat.create');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'nama_obat' => 'required|string',
      'kemasan' => 'required|string',
      'harga' => 'required|integer|min:0|max:999999',
    ]);

    Obat::create([
      'nama_obat' => $request->nama_obat,
      'kemasan' => $request->kemasan,
      'harga' => $request->harga
    ]);

    return redirect()->route('obat.index')
        ->with('message', 'Data obat berhasil ditambahkan')
        ->with('type', 'success');
  }

  public function edit(string $id){
    $obat = Obat::findOrFail($id);
    return view('admin.obat.edit')
    ->with(['obat' => $obat]);
  }

  public function update(Request $request, string $id){
    $request->validate([
      'nama_obat' => 'required|string',
      'kemasan' => 'required|string',
      'harga' => 'required|integer|min:0|max:999999',
    ]);

    $obat = Obat::findOrFail($id);
    $obat->update([
      'nama_obat' => $request->nama_obat,
      'kemasan' => $request->kemasan,
      'harga' => $request->harga
    ]);

    return redirect()->route('obat.index')
        ->with('message', 'Data obat berhasil diubah')
        ->with('type', 'success');
  }    
  public function destroy($id)
    {
        $obat = Obat::findOrFail($id);
        $obat->delete();

        return redirect()->route('obat.index')
            ->with('message', 'Data obat berhasil dihapus')
            ->with('type', 'success');
    }
}