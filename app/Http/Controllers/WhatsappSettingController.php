<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

class WhatsappSettingController extends Controller
{
    protected string $sessionId;

    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
        $this->sessionId = config('laravel-whatsapp.sidecar.default_session', 'smkn2_monitoring');
    }

    public function index()
    {
        return view('pengaturan-whatsapp.index');
    }

    public function status()
    {
        $sidecarRunning = false;
        $sidecarInstalled = false;
        try {
            // ponytail: simple HTTP check to see if sidecar is reachable
            $web = WhatsApp::web($this->sessionId);
            $state = $web->state();
            $sidecarRunning = true;
            $sidecarInstalled = true;
        } catch (\Exception $e) {
            // sidecar not reachable or not installed
        }

        $session = null;
        if ($sidecarRunning) {
            try {
                $web = WhatsApp::web($this->sessionId);
                $state = $web->state();
                $info = null;
                $qr = null;
                if ($state === 'qr') {
                    $qrData = $web->qr();
                    $qr = $qrData['qr'] ?? null;
                }
                if (in_array($state, ['authenticated', 'ready'])) {
                    $info = $web->info();
                }

                $session = [
                    'id' => $this->sessionId,
                    'status' => $state,
                    'qr' => $qr,
                    'phone_number' => $info['phone_number'] ?? $info['me']['user'] ?? null,
                    'push_name' => $info['push_name'] ?? $info['me']['name'] ?? null,
                    'ready_at' => null,
                ];
            } catch (\Exception $e) {
                $session = [
                    'id' => $this->sessionId,
                    'status' => 'error',
                    'qr' => null,
                    'phone_number' => null,
                    'push_name' => null,
                    'ready_at' => null,
                ];
            }
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

            // after start, get QR
            $state = $web->state();
            $qr = null;
            if ($state === 'qr') {
                $qrData = $web->qr();
                $qr = $qrData['qr'] ?? null;
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $this->sessionId,
                    'status' => $state,
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
