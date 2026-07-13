<?php
namespace App\Http\Controllers;

use App\Models\SuratTeguran;
use App\Jobs\KirimWaTeguran;

class SuratTeguranController extends Controller
{
    public function index()
    {
        $teguran = SuratTeguran::with('siswa')
            ->orderBy('tanggal_terbit', 'desc')
            ->paginate(50);

        return view('surat-teguran.index', compact('teguran'));
    }

    public function kirimWa(SuratTeguran $suratTeguran)
    {
        $siswa = $suratTeguran->siswa;

        if (!$siswa->no_wali) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WA wali belum diisi untuk siswa ' . $siswa->nama_siswa . '. Silakan isi di halaman Data Siswa.',
            ], 422);
        }

        dispatch(new KirimWaTeguran($siswa->id, $suratTeguran->tingkat, $suratTeguran->file_pdf));

        return response()->json([
            'success' => true,
            'message' => 'Pesan WA sedang dikirim ke ' . $siswa->no_wali,
        ]);
    }
}
