<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\SuratTeguran;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSiswa = Siswa::count();
        $totalPelanggaran = Pelanggaran::whereMonth('tanggal', now()->month)->count();
        $totalTeguran = SuratTeguran::count();

        $poinTertinggi = Pelanggaran::selectRaw('id_siswa, sum(jenis_pelanggaran.poin) as total')
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->groupBy('id_siswa')
            ->orderBy('total', 'desc')
            ->first();

        $terbaru = Pelanggaran::with(['siswa', 'jenis'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'siswa' => $p->siswa?->nama_siswa ?? '-',
                'kelas' => $p->siswa?->rombel ?? '-',
                'jenis' => $p->jenis?->nama ?? '-',
                'tanggal' => $p->tanggal,
            ]);

        return view('dashboard.index', [
            'totalSiswa' => $totalSiswa,
            'totalPelanggaran' => $totalPelanggaran,
            'totalTeguran' => $totalTeguran,
            'poinTertinggi' => $poinTertinggi?->total ?? 0,
            'terbaru' => $terbaru,
        ]);
    }
}
