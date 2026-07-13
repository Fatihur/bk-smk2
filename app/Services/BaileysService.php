<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BaileysService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.baileys.url', 'http://127.0.0.1:3001');
    }

    public function status(): array
    {
        $res = Http::get($this->baseUrl . '/status');
        return $res->json() ?? [];
    }

    public function start(): array
    {
        $res = Http::get($this->baseUrl . '/start');
        return $res->json() ?? [];
    }

    public function sendText(string $target, string $message): array
    {
        $res = Http::post($this->baseUrl . '/send-text', [
            'target' => $target,
            'message' => $message,
        ]);
        return $res->json() ?? [];
    }

    public function sendDocument(string $target, string $filePath, string $filename, string $caption = ''): array
    {
        $res = Http::post($this->baseUrl . '/send-document', [
            'target' => $target,
            'filePath' => $filePath,
            'filename' => $filename,
            'caption' => $caption,
        ]);
        return $res->json() ?? [];
    }

    public function stop(): array
    {
        $res = Http::post($this->baseUrl . '/stop');
        return $res->json() ?? [];
    }

    public function logout(): array
    {
        $res = Http::post($this->baseUrl . '/logout');
        return $res->json() ?? [];
    }
}
