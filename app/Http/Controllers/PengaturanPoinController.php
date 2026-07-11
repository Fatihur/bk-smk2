<?php

namespace App\Http\Controllers;

use App\Models\PengaturanPoin;
use Illuminate\Http\Request;

class PengaturanPoinController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanPoin::orderBy('batas_poin')->get();
        return view('pengaturan-poin.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'batas' => 'required|array',
            'batas.*' => 'required|integer|min:1',
        ]);

        foreach ($validated['batas'] as $id => $value) {
            PengaturanPoin::where('id', $id)->update(['batas_poin' => $value]);
        }

        return redirect()->route('pengaturan-poin.index')->with('success', 'Pengaturan poin berhasil disimpan');
    }
}
