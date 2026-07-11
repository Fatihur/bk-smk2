<?php
namespace App\Http\Controllers;

use App\Models\SuratTeguran;
use Illuminate\Http\Request;

class SuratTeguranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:guru_bk,kepala_sekolah');
    }

    public function index()
    {
        $teguran = SuratTeguran::with('siswa.kelas')
            ->orderBy('tanggal_terbit', 'desc')
            ->paginate(50);

        return view('surat-teguran.index', compact('teguran'));
    }
}
