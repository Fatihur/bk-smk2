<?php

namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Http\Request;

class OrangTuaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('kelas', 'orangTua')->get();
        return view('orang-tua.index', compact('siswa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'nama' => 'required|max:100',
            'nomor_wa' => 'required|max:20',
            'hubungan' => 'required|in:ayah,ibu,wali',
        ]);

        $orangTua = OrangTua::create($validated);
        $orangTua->load('siswa.kelas');

        return response()->json(['message' => 'Orang tua berhasil ditambahkan', 'data' => $orangTua]);
    }

    public function update(Request $request, OrangTua $orangTua)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'nama' => 'required|max:100',
            'nomor_wa' => 'required|max:20',
            'hubungan' => 'required|in:ayah,ibu,wali',
        ]);

        $orangTua->update($validated);
        $orangTua->load('siswa.kelas');

        return response()->json(['message' => 'Orang tua berhasil diperbarui', 'data' => $orangTua]);
    }

    public function destroy(OrangTua $orangTua)
    {
        $orangTua->delete();
        return response()->json(['message' => 'Orang tua berhasil dihapus']);
    }
}
