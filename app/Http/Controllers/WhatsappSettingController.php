<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;
use Kstmostofa\LaravelWhatsApp\Models\WaMessage;

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
                'phone_number' => $info['info']['wid']['user'] ?? $info['info']['me']['user'] ?? null,
                'push_name' => $info['info']['pushname'] ?? $info['info']['me']['name'] ?? null,
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

    public function logs(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $sessionId = $this->sessionId;

        $messages = WaMessage::where('session_id', $sessionId)
            ->whereIn('direction', ['outgoing', 'incoming'])
            ->orderBy('wa_timestamp', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $messages->getCollection()->transform(function ($msg) {
            $contact = $msg->direction === 'outgoing' ? $msg->to_id : $msg->from_id;
            $body = $msg->body;
            if ($msg->type !== 'text' && $msg->type !== 'chat') {
                $body = '[' . strtoupper($msg->type) . '] ' . ($body ?? '');
            }

            return [
                'id' => $msg->id,
                'wa_message_id' => $msg->wa_message_id,
                'direction' => $msg->direction,
                'contact' => $contact,
                'type' => $msg->type,
                'body' => $body,
                'status' => $msg->status,
                'ack' => $msg->ack,
                'wa_timestamp' => $msg->wa_timestamp ? $msg->wa_timestamp->toIso8601String() : null,
                'created_at' => $msg->created_at?->toIso8601String(),
            ];
        });

        return response()->json($messages);
    }
}
