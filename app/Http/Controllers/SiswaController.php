<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('kelas')->get();
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('siswa.index', compact('siswa', 'kelas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'required|unique:siswa',
            'nama' => 'required|max:100',
            'id_kelas' => 'required|exists:kelas,id',
        ]);

        $siswa = Siswa::create($validated);
        $siswa->load('kelas');

        return response()->json(['message' => 'Siswa berhasil ditambahkan', 'data' => $siswa]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nisn' => 'required|unique:siswa,nisn,' . $siswa->id,
            'nama' => 'required|max:100',
            'id_kelas' => 'required|exists:kelas,id',
        ]);

        $siswa->update($validated);
        $siswa->load('kelas');

        return response()->json(['message' => 'Siswa berhasil diperbarui', 'data' => $siswa]);
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return response()->json(['message' => 'Siswa berhasil dihapus']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        $rows = Excel::toCollection(null, $request->file('file'))->first();
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $nisn = $row[0] ?? null;
            $nama = $row[1] ?? null;
            $namaKelas = $row[2] ?? null;

            if (!$nisn || !$nama || !$namaKelas) {
                $skipped++;
                continue;
            }

            $kelas = Kelas::where('nama_kelas', $namaKelas)->first();
            if (!$kelas) {
                $skipped++;
                continue;
            }

            Siswa::updateOrCreate(
                ['nisn' => $nisn],
                ['nama' => $nama, 'id_kelas' => $kelas->id]
            );
            $imported++;
        }

        return response()->json([
            'message' => "Import selesai: $imported berhasil, $skipped dilewati",
        ]);
    }
}
