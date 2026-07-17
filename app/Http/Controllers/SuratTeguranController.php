<?php
namespace App\Http\Controllers;

use App\Models\SuratTeguran;
use App\Jobs\KirimWaTeguran;
use Illuminate\Support\Facades\Storage;



class SuratTeguranController extends Controller
{
    public function show(SuratTeguran $suratTeguran)
    {
        $path = 'teguran/' . $suratTeguran->file_pdf;
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File PDF tidak ditemukan');
        }
        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $suratTeguran->file_pdf . '"',
        ]);
    }

    public function kirimWa(SuratTeguran $suratTeguran)
    {
        if ($suratTeguran->status_terkirim) {
            return response()->json([
                'success' => false,
                'message' => 'Surat teguran ini sudah pernah dikirim.',
            ], 422);
        }

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
