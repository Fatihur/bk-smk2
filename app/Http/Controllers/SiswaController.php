<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::orderBy('nama_siswa')->get();
        return view('siswa.index', compact('siswa'));
    }

    public function edit(Siswa $siswa)
    {
        return response()->json($siswa);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|max:100',
            'jk' => 'required|in:L,P',
            'nisn' => 'required|unique:siswa',
            'tempat_lahir' => 'required|max:50',
            'tgl_lahir' => 'required|date',
            'nik' => 'required|max:20',
            'agama' => 'required|max:20',
            'alamat' => 'required',
            'hp' => 'nullable|max:20',
            'ayah' => 'nullable|max:100',
            'ibu' => 'nullable|max:100',
            'no_wali' => 'nullable|max:20',
            'rombel' => 'required|in:X KJJ,XI KJJ,XII KJJ',
        ]);

        $siswa = Siswa::create($validated);

        return response()->json(['message' => 'Siswa berhasil ditambahkan', 'data' => $siswa]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|max:100',
            'jk' => 'required|in:L,P',
            'nisn' => 'required|unique:siswa,nisn,' . $siswa->id,
            'tempat_lahir' => 'required|max:50',
            'tgl_lahir' => 'required|date',
            'nik' => 'required|max:20',
            'agama' => 'required|max:20',
            'alamat' => 'required',
            'hp' => 'nullable|max:20',
            'ayah' => 'nullable|max:100',
            'ibu' => 'nullable|max:100',
            'no_wali' => 'nullable|max:20',
            'rombel' => 'required|in:X KJJ,XI KJJ,XII KJJ',
        ]);

        $siswa->update($validated);

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
            $namaSiswa = $row[1] ?? null;
            $rombel = $row[2] ?? null;

            if (!$nisn || !$namaSiswa || !$rombel) {
                $skipped++;
                continue;
            }

            if (!in_array($rombel, ['X KJJ', 'XI KJJ', 'XII KJJ'])) {
                $skipped++;
                continue;
            }

            Siswa::updateOrCreate(
                ['nisn' => $nisn],
                ['nama_siswa' => $namaSiswa, 'rombel' => $rombel]
            );
            $imported++;
        }

        return response()->json([
            'message' => "Import selesai: $imported berhasil, $skipped dilewati",
        ]);
    }
}
