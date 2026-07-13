<?php

namespace App\Http\Controllers;

use App\Services\BaileysService;
use Illuminate\Http\Request;

class WhatsappSettingController extends Controller
{
    public function index()
    {
        return view('pengaturan-whatsapp.index');
    }

    public function status(Request $request, BaileysService $wa)
    {
        return response()->json($wa->status());
    }

    public function start(Request $request, BaileysService $wa)
    {
        return response()->json($wa->start());
    }

    public function testSend(Request $request, BaileysService $wa)
    {
        $validated = $request->validate([
            'target' => 'required|string',
        ]);

        $result = $wa->sendText($validated['target'], 'Test pesan dari SMKN 2 Sumbawa — Sistem Monitoring Kedisiplinan.');

        if (isset($result['success']) && $result['success']) {
            return response()->json(['success' => true, 'message' => 'Pesan test berhasil dikirim']);
        }

        return response()->json([
            'success' => false,
            'message' => $result['reason'] ?? 'Gagal mengirim pesan',
        ], 422);
    }

    public function logout(Request $request, BaileysService $wa)
    {
        $wa->logout();
        return redirect()->route('whatsapp.settings')->with('success', 'Session WhatsApp berhasil dihapus');
    }
}
