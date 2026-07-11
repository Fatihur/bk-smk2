<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\SuratTeguran;

class ApiDashboardController extends Controller
{
    public function stats()
    {
        $totalSiswa = Siswa::count();
        $totalPelanggaran = Pelanggaran::whereMonth('tanggal', now()->month)->count();
        $totalTeguran = SuratTeguran::count();

        $poinTertinggi = Pelanggaran::selectRaw('id_siswa, sum(jenis_pelanggaran.poin) as total')
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->groupBy('id_siswa')
            ->orderBy('total', 'desc')
            ->first();

        $terbaru = Pelanggaran::with(['siswa.kelas', 'jenis'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'siswa' => $p->siswa->nama ?? '-',
                'kelas' => $p->siswa->kelas->nama_kelas ?? '-',
                'jenis' => $p->jenis->nama ?? '-',
                'tanggal' => $p->tanggal,
            ]);

        return response()->json([
            'total_siswa' => $totalSiswa,
            'total_pelanggaran' => $totalPelanggaran,
            'total_teguran' => $totalTeguran,
            'poin_tertinggi' => $poinTertinggi ? $poinTertinggi->total : 0,
            'terbaru' => $terbaru,
        ]);
    }
}
