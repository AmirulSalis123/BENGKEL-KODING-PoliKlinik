<?php
namespace App\Http\Controllers;

use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DokterController extends Controller
{
    public function index()
    {
        // dimana role adalah dokter
        $dokters = User::where('role', 'dokter')->with('poli')->get();
        return view('admin.dokter.index', compact('dokters'));
    }

    public function create()
    {
        $polis = Poli::all();
        return view('admin.dokter.create', compact('polis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_ktp' => 'required|string|max:16|unique:users,no_ktp',
            'no_hp' => 'required|string|max:15',
            'id_poli' => 'required|string|exists:poli,id',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'no_ktp' => $request->no_ktp,
            'no_hp' => $request->no_hp,
            'id_poli' => $request->id_poli,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'dokter',
        ]);

        return redirect()->route('dokters.index')
            ->with('message', 'Data dokter berhasil ditambahkan')
            ->with('type', 'success');
    }

    public function edit(User $dokter)
    {
        $polis = Poli::all();
        return view('admin.dokter.edit', compact('dokter', 'polis'));
    }

    public function update(Request $request, User $dokter)
    {
        $request->validate([
            'nama' => 'required|string',
            'email' => 'required|string|unique:users,email,' . $dokter->id,
            'no_ktp' => 'required|string|max:16|unique:users,no_ktp,' . $dokter->id,
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'id_poli' => 'required|exists:poli,id',
        ]);

        $updateData = [
            'nama' => $request->nama,
            'email' => $request->email,
            'no_ktp' => $request->no_ktp,
            'no_hp' => $request->no_hp,
            'alamat' => $request->alamat,
            'id_poli' => $request->id_poli,
        ];

        if ($request->filled('password')) {
            $dokter->password = Hash::make($request->password);
        }

        $dokter->update($updateData);

        return redirect()->route('dokters.index')
            ->with('message', 'Data dokter berhasil diubah')
            ->with('type','success');
    }

    public function destroy(User $dokter)
    {
        $dokter->delete();
        return redirect()->route('dokters.index')
            ->with('message', 'Data dokter berhasil dihapus')
            ->with('type', 'success');
    }
}