<?php

namespace App\Http\Controllers;

use App\Jobs\KirimWaTeguran;
use App\Models\Pelanggaran;
use App\Models\PengaturanPoin;
use App\Models\Siswa;
use App\Models\SuratTeguran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $this->cekDanTerbitkanTeguran($validated['id_siswa']);

        return response()->json(['message' => 'Pelanggaran berhasil dicatat', 'data' => $pelanggaran]);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|array|min:1',
            'id_siswa.*' => 'exists:siswa,id',
            'id_jenis' => 'required|array|min:1',
            'id_jenis.*' => 'exists:jenis_pelanggaran,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $data = [];
        foreach ($validated['id_siswa'] as $siswaId) {
            foreach ($validated['id_jenis'] as $jenisId) {
                $data[] = [
                    'id_siswa' => $siswaId,
                    'id_jenis' => $jenisId,
                    'tanggal' => $validated['tanggal'],
                    'keterangan' => $validated['keterangan'],
                ];
            }
        }

        Pelanggaran::insert($data);

        foreach (array_unique($validated['id_siswa']) as $siswaId) {
            $this->cekDanTerbitkanTeguran($siswaId);
        }

        return response()->json([
            'message' => count($data) . ' pelanggaran berhasil dicatat',
        ]);
    }

    private function cekDanTerbitkanTeguran(int $idSiswa): void
    {
        $siswa = Siswa::find($idSiswa);
        if (!$siswa) return;

        $totalPoin = Pelanggaran::where('id_siswa', $idSiswa)
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');

        $pengaturan = PengaturanPoin::orderBy('batas_poin')->get();

        foreach ($pengaturan as $p) {
            if ($totalPoin >= $p->batas_poin) {
                $exists = SuratTeguran::where('id_siswa', $idSiswa)
                    ->where('tingkat', $p->tingkat)
                    ->exists();
                if ($exists) continue;

                $pdf = Pdf::loadView('pdf.surat-teguran', [
                    'tingkat' => strtoupper($p->tingkat),
                    'siswa' => $siswa,
                    'totalPoin' => $totalPoin,
                    'tanggal' => now()->translatedFormat('d F Y'),
                ]);

                $filename = 'SP_' . $p->tingkat . '_' . $siswa->nisn . '_' . now()->format('Ymd') . '.pdf';
                Storage::disk('public')->put('teguran/' . $filename, $pdf->output());

                $surat = SuratTeguran::create([
                    'id_siswa' => $idSiswa,
                    'tingkat' => $p->tingkat,
                    'total_poin' => $totalPoin,
                    'file_pdf' => $filename,
                    'tanggal_terbit' => now()->toDateString(),
                    'status_terkirim' => false,
                ]);

                // Auto dispatch WA
                if ($siswa->no_wali) {
                    dispatch(new KirimWaTeguran($idSiswa, $p->tingkat, $filename));
                }
            }
        }
    }

    public function riwayat(Request $request)
    {
        $query = Siswa::select('siswa.*')
            ->selectRaw('(SELECT COALESCE(SUM(jp.poin), 0)
                          FROM pelanggaran p
                          JOIN jenis_pelanggaran jp ON p.id_jenis = jp.id
                          WHERE p.id_siswa = siswa.id) as total_poin')
            ->with(['pelanggaran.jenis', 'suratTeguran']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_siswa', 'like', "%{$s}%")
                  ->orWhere('nisn', 'like', "%{$s}%");
            });
        }

        if ($request->filled('id_siswa')) {
            $query->where('siswa.id', $request->id_siswa);
        }

        if ($request->filled('dari')) {
            $query->whereHas('pelanggaran', fn($q) => $q->where('tanggal', '>=', $request->dari));
        }

        if ($request->filled('sampai')) {
            $query->whereHas('pelanggaran', fn($q) => $q->where('tanggal', '<=', $request->sampai));
        }

        $daftarSiswa = $query->orderBy('total_poin', 'desc')
            ->paginate(50);

        $semuaSiswa = Siswa::orderBy('nama_siswa')->get();

        return view('pelanggaran.index', [
            'daftarSiswa' => $daftarSiswa,
            'siswa' => $semuaSiswa,
        ]);
    }
}
