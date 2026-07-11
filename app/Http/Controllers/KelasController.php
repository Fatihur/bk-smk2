<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('kelas.index', compact('kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|max:50',
            'tingkat' => 'required|in:X,XI,XII',
        ]);

        $kelas = Kelas::create($validated);

        return response()->json(['message' => 'Kelas berhasil ditambahkan', 'data' => $kelas]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|max:50',
            'tingkat' => 'required|in:X,XI,XII',
        ]);

        $kelas->update($validated);

        return response()->json(['message' => 'Kelas berhasil diperbarui', 'data' => $kelas]);
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return response()->json(['message' => 'Kelas berhasil dihapus']);
    }
}
