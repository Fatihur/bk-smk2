<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

class WhatsappSettingController extends Controller
{
    protected string $sessionId;

    public function __construct()
    {
        $this->sessionId = config('laravel-whatsapp.ui.default_session', 'smkn2_monitoring');
    }

    public function index()
    {
        return view('pengaturan-whatsapp.index');
    }

    public function status()
    {
        $sidecarRunning = false;
        $sidecarInstalled = false;
        $session = null;

        try {
            $client = WhatsApp::webClient();
            $client->ping();
            $sidecarRunning = true;
            $sidecarInstalled = true;

            $web = WhatsApp::web($this->sessionId);
            $state = $web->state();
            $status = $state['status'] ?? 'unknown';

            $qr = null;
            if ($status === 'qr') {
                $qrData = $web->qr();
                $qr = $qrData['qr'] ?? null;
            }

            $info = null;
            if (in_array($status, ['authenticated', 'ready'])) {
                $info = $web->info();
            }

            $session = [
                'id' => $this->sessionId,
                'status' => $status,
                'qr' => $qr,
                'phone_number' => $info['phone_number'] ?? $info['me']['user'] ?? null,
                'push_name' => $info['push_name'] ?? $info['me']['name'] ?? null,
                'ready_at' => null,
            ];
        } catch (\Exception $e) {
            // ponytail: log the error for debugging but don't expose to UI
            logger()->error('WhatsApp status check failed: ' . $e->getMessage());
        }

        return response()->json([
            'sidecar' => [
                'installed' => $sidecarInstalled,
                'running' => $sidecarRunning,
            ],
            'session' => $session,
        ]);
    }

    public function start()
    {
        try {
            $web = WhatsApp::web($this->sessionId);
            $result = $web->start();

            $state = $web->state();
            $status = $state['status'] ?? 'unknown';
            $qr = null;
            if ($status === 'qr') {
                $qrData = $web->qr();
                $qr = $qrData['qr'] ?? null;
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $this->sessionId,
                    'status' => $status,
                    'qr' => $qr,
                    'phone_number' => null,
                    'push_name' => null,
                    'ready_at' => null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function stop()
    {
        try {
            WhatsApp::web($this->sessionId)->stop();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy()
    {
        try {
            WhatsApp::web($this->sessionId)->destroy();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus session: ' . $e->getMessage(),
            ], 500);
        }
    }
}
