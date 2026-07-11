<?php

namespace App\Http\Controllers;

use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;

class JenisPelanggaranController extends Controller
{
    public function index()
    {
        $jenis = JenisPelanggaran::orderBy('nama')->get();
        return view('jenis-pelanggaran.index', compact('jenis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'poin' => 'required|integer|min:1',
        ]);

        $jenis = JenisPelanggaran::create($validated);

        return response()->json(['message' => 'Jenis pelanggaran berhasil ditambahkan', 'data' => $jenis]);
    }

    public function update(Request $request, JenisPelanggaran $jenisPelanggaran)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'poin' => 'required|integer|min:1',
        ]);

        $jenisPelanggaran->update($validated);

        return response()->json(['message' => 'Jenis pelanggaran berhasil diperbarui', 'data' => $jenisPelanggaran]);
    }

    public function destroy(JenisPelanggaran $jenisPelanggaran)
    {
        $jenisPelanggaran->delete();
        return response()->json(['message' => 'Jenis pelanggaran berhasil dihapus']);
    }
}
