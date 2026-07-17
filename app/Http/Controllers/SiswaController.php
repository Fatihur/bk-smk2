<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

    public function template()
    {
        $spreadsheet = IOFactory::load(__DIR__ . '/../../../resources/templates/import_siswa.xlsx');
        $writer = new XlsxWriter($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_import_siswa.xlsx"',
        ]);
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

        // detect format: if A1 = "NISN", it's a template format
        $isTemplate = strtoupper(trim($sheet->getCell('A1')->getValue() ?? '')) === 'NISN';

        $startRow = $isTemplate ? 2 : 7;

        for ($r = $startRow; $r <= $highestRow; $r++) {
            if ($isTemplate) {
                $nisn = trim($sheet->getCell('A' . $r)->getValue() ?? '');
                $namaSiswa = trim($sheet->getCell('B' . $r)->getValue() ?? '');
                $rombel = trim($sheet->getCell('L' . $r)->getValue() ?? '');
                $jk = trim($sheet->getCell('C' . $r)->getValue() ?? '');
                $tempatLahir = trim($sheet->getCell('D' . $r)->getValue() ?? '');
                $tglLahir = trim($sheet->getCell('E' . $r)->getValue() ?? '');
                $nik = trim($sheet->getCell('F' . $r)->getValue() ?? '');
                $agama = trim($sheet->getCell('G' . $r)->getValue() ?? '');
                $alamat = trim($sheet->getCell('H' . $r)->getValue() ?? '');
                $hp = trim($sheet->getCell('I' . $r)->getValue() ?? '');
                $ayah = trim($sheet->getCell('J' . $r)->getValue() ?? '');
                $ibu = trim($sheet->getCell('K' . $r)->getValue() ?? '');
                $noWali = trim($sheet->getCell('M' . $r)->getValue() ?? '');
            } else {
                // ponytail: column positions hardcoded for specific Dapodik export format.
                // If import starts failing, re-map by inspecting the source Excel headers.
                $rombel = trim($sheet->getCell('AQ' . $r)->getValue() ?? '');
                $nisn = trim($sheet->getCell('E' . $r)->getValue() ?? '');
                $namaSiswa = trim($sheet->getCell('B' . $r)->getValue() ?? '');
                $jk = trim($sheet->getCell('D' . $r)->getValue() ?? '');
                $tempatLahir = trim($sheet->getCell('F' . $r)->getValue() ?? '');
                $tglLahir = trim($sheet->getCell('G' . $r)->getValue() ?? '');
                $nik = trim($sheet->getCell('H' . $r)->getValue() ?? '');
                $agama = trim($sheet->getCell('I' . $r)->getValue() ?? '');
                $alamat = trim($sheet->getCell('J' . $r)->getValue() ?? '');
                $hp = trim($sheet->getCell('T' . $r)->getValue() ?? '');
                $ayah = trim($sheet->getCell('Y' . $r)->getValue() ?? '');
                $ibu = trim($sheet->getCell('AE' . $r)->getValue() ?? '');
            }

            if (!in_array($rombel, ['X KJJ', 'XI KJJ', 'XII KJJ'])) {
                $skipped++;
                continue;
            }

            if (!$nisn || !$namaSiswa) {
                $skipped++;
                continue;
            }

            if ($tglLahir) {
                if (is_numeric($tglLahir)) {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tglLahir);
                    $tglLahir = $dt->format('Y-m-d');
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglLahir)) {
                    // already YYYY-MM-DD
                } else {
                    try {
                        $tglLahir = \Carbon\Carbon::parse($tglLahir)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tglLahir = null;
                    }
                }
            } else {
                $tglLahir = null;
            }

            Siswa::updateOrCreate(
                ['nisn' => $nisn],
                [
                    'nama_siswa' => $namaSiswa,
                    'jk' => $jk,
                    'tempat_lahir' => $tempatLahir,
                    'tgl_lahir' => $tglLahir,
                    'nik' => $nik,
                    'agama' => $agama,
                    'alamat' => $alamat,
                    'hp' => $hp,
                    'ayah' => $ayah,
                    'ibu' => $ibu,
                    'no_wali' => $noWali,
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
