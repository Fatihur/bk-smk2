<?php

namespace App\Http\Controllers;

use App\Services\FonnteService;
use Illuminate\Http\Request;

class WhatsappSettingController extends Controller
{
    public function index()
    {
        $token = config('services.fonnte.token');
        return view('pengaturan-whatsapp.index', compact('token'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $path = base_path('.env');
        $content = file_get_contents($path);
        $content = preg_replace(
            '/^FONNTE_TOKEN=.*$/m',
            'FONNTE_TOKEN=' . $validated['token'],
            $content
        );
        if (!str_contains($content, 'FONNTE_TOKEN=')) {
            $content .= "\nFONNTE_TOKEN=" . $validated['token'] . "\n";
        }
        file_put_contents($path, $content);

        return redirect()->route('whatsapp.settings')->with('success', 'Token Fonnte berhasil disimpan');
    }

    public function testSend(Request $request, FonnteService $fonnte)
    {
        $validated = $request->validate([
            'target' => 'required|string',
        ]);

        $result = $fonnte->sendText($validated['target'], 'Test pesan dari SMKN 2 Sumbawa — Sistem Monitoring Kedisiplinan.');

        if (isset($result['status']) && $result['status']) {
            return response()->json(['success' => true, 'message' => 'Pesan test berhasil dikirim']);
        }

        return response()->json([
            'success' => false,
            'message' => $result['reason'] ?? 'Gagal mengirim pesan',
        ], 422);
    }
}
