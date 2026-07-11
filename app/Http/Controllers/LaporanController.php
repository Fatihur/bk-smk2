<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama')->get();
        return view('laporan.index', compact('siswa'));
    }

    public function cetak()
    {
        $query = Pelanggaran::with(['siswa.kelas', 'jenis'])->orderBy('tanggal', 'desc');

        if ($idSiswa = request('id_siswa')) {
            $query->where('id_siswa', $idSiswa);
        }
        if ($dari = request('dari')) {
            $query->where('tanggal', '>=', $dari);
        }
        if ($sampai = request('sampai')) {
            $query->where('tanggal', '<=', $sampai);
        }

        $pelanggaran = $query->get();
        $siswa = $idSiswa ? Siswa::find($idSiswa) : null;

        $pdf = Pdf::loadView('pdf.laporan', compact('pelanggaran', 'siswa'));
        return $pdf->download('laporan-kedisiplinan.pdf');
    }
}
