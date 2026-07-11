<?php

namespace App\Http\Controllers;

use App\Models\JenisPelanggaran;
use App\Models\Siswa;
use Illuminate\Http\Request;

class Select2Controller extends Controller
{
    public function siswa(Request $request)
    {
        $q = $request->input('q', '');
        $siswa = Siswa::with('kelas')
            ->where('nama', 'like', "%{$q}%")
            ->orWhere('nisn', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'text' => "{$s->nama} ({$s->nisn}) - {$s->kelas->tingkat} {$s->kelas->nama_kelas}",
            ]);

        return response()->json(['results' => $siswa]);
    }

    public function jenis(Request $request)
    {
        $q = $request->input('q', '');
        $jenis = JenisPelanggaran::where('nama', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(fn($j) => [
                'id' => $j->id,
                'text' => "{$j->nama} ({$j->poin} poin)",
            ]);

        return response()->json(['results' => $jenis]);
    }
}
