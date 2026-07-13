<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index()
    {
        return view('pelanggaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'id_jenis' => 'required|exists:jenis_pelanggaran,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $pelanggaran = Pelanggaran::create($validated);

        return response()->json(['message' => 'Pelanggaran berhasil dicatat', 'data' => $pelanggaran]);
    }

    public function riwayat(Request $request)
    {
        $query = Pelanggaran::with(['siswa', 'jenis']);

        if ($request->filled('id_siswa')) {
            $query->where('id_siswa', $request->id_siswa);
        }

        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->sampai);
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')->paginate(50);

        $siswa = Siswa::orderBy('nama_siswa')->get();

        return view('pelanggaran.index', compact('pelanggaran', 'siswa'));
    }
}
