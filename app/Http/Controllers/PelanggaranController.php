<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\JenisPelanggaran;
use App\Models\PengaturanPoin;
use App\Models\SuratTeguran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PelanggaranController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk'])->except(['riwayat']);
        $this->middleware(['auth', 'role:guru_bk,kepala_sekolah'])->only(['riwayat']);
    }

    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama')->get();
        $jenis = JenisPelanggaran::orderBy('nama')->get();
        return view('pelanggaran.create', compact('siswa', 'jenis'));
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

    public function riwayat(Request $request)
    {
        $query = Pelanggaran::with(['siswa.kelas', 'jenis']);

        if ($request->filled('id_siswa')) {
            $query->where('id_siswa', $request->id_siswa);
        }

        if ($request->filled('id_kelas')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id_kelas', $request->id_kelas);
            });
        }

        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->sampai);
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')->paginate(50);

        $siswa = Siswa::with('kelas')->orderBy('nama')->get();

        return view('pelanggaran.index', compact('pelanggaran', 'siswa'));
    }

    private function cekDanTerbitkanTeguran($idSiswa)
    {
        $totalPoin = Pelanggaran::where('id_siswa', $idSiswa)
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');

        $pengaturan = PengaturanPoin::orderBy('batas_poin', 'desc')->get();

        foreach ($pengaturan as $p) {
            if ($totalPoin >= $p->batas_poin) {
                $exists = SuratTeguran::where('id_siswa', $idSiswa)
                    ->where('tingkat', $p->tingkat)
                    ->exists();

                if (!$exists) {
                    $siswa = Siswa::with('kelas')->findOrFail($idSiswa);
                    $filename = 'teguran_' . $p->tingkat . '_' . $idSiswa . '_' . now()->format('Ymd') . '.pdf';

                    $pdf = Pdf::loadView('pdf.surat-teguran', [
                        'siswa' => $siswa,
                        'tingkat' => strtoupper($p->tingkat),
                        'totalPoin' => $totalPoin,
                        'tanggal' => now()->format('d F Y'),
                    ]);

                    $pdf->save(storage_path('app/public/teguran/' . $filename));

                    SuratTeguran::create([
                        'id_siswa' => $idSiswa,
                        'tingkat' => $p->tingkat,
                        'total_poin' => $totalPoin,
                        'file_pdf' => 'teguran/' . $filename,
                        'tanggal_terbit' => now()->toDateString(),
                        'status_terkirim' => false,
                    ]);

                    if (class_exists(\App\Jobs\KirimWaTeguran::class)) {
                        \App\Jobs\KirimWaTeguran::dispatch($idSiswa, $p->tingkat, $filename);
                    }
                }

                break;
            }
        }
    }
}
