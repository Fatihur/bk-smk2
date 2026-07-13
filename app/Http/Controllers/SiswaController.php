<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            'file' => 'required|mimes:xls,xlsx,csv',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $imported = 0;
        $skipped = 0;

        for ($r = 7; $r <= $highestRow; $r++) {
            $rombel = trim($sheet->getCell('AQ' . $r)->getValue() ?? '');
            if (!in_array($rombel, ['X KJJ', 'XI KJJ', 'XII KJJ'])) {
                $skipped++;
                continue;
            }

            $nisn = trim($sheet->getCell('E' . $r)->getValue() ?? '');
            $namaSiswa = trim($sheet->getCell('B' . $r)->getValue() ?? '');

            if (!$nisn || !$namaSiswa) {
                $skipped++;
                continue;
            }

            $tglLahir = trim($sheet->getCell('G' . $r)->getValue() ?? '');
            if (!$tglLahir || $tglLahir === '0000-00-00') {
                $tglLahir = null;
            }

            Siswa::updateOrCreate(
                ['nisn' => $nisn],
                [
                    'nama_siswa' => $namaSiswa,
                    'jk' => trim($sheet->getCell('D' . $r)->getValue() ?? ''),
                    'tempat_lahir' => trim($sheet->getCell('F' . $r)->getValue() ?? ''),
                    'tgl_lahir' => $tglLahir,
                    'nik' => trim($sheet->getCell('H' . $r)->getValue() ?? ''),
                    'agama' => trim($sheet->getCell('I' . $r)->getValue() ?? ''),
                    'alamat' => trim($sheet->getCell('J' . $r)->getValue() ?? ''),
                    'hp' => trim($sheet->getCell('T' . $r)->getValue() ?? ''),
                    'ayah' => trim($sheet->getCell('Y' . $r)->getValue() ?? ''),
                    'ibu' => trim($sheet->getCell('AE' . $r)->getValue() ?? ''),
                    'rombel' => $rombel,
                ]
            );
            $imported++;
        }

        $spreadsheet->disconnectWorksheets();

        return response()->json([
            'message' => "Import selesai: $imported berhasil, $skipped dilewati",
        ]);
    }
}
