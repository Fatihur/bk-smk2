<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FonnteService
{
    public function __construct(
        protected string $token = '',
    ) {
        if (!$this->token) {
            $this->token = config('services.fonnte.token', env('FONNTE_TOKEN', ''));
        }
    }

    public function sendText(string $target, string $message): array
    {
        return $this->post('/send', [
            'target' => $target,
            'message' => $message,
            'type' => 'text',
        ]);
    }

    public function sendDocument(string $target, string $url, string $filename): array
    {
        return $this->post('/send', [
            'target' => $target,
            'message' => $url,
            'type' => 'text',
        ]);
    }

    public function validateToken(): array
    {
        return $this->get('/balance');
    }

    protected function post(string $endpoint, array $data): array
    {
        $response = Http::withToken($this->token)
            ->post('https://api.fonnte.com' . $endpoint, $data);

        return $response->json();
    }

    protected function get(string $endpoint): array
    {
        $response = Http::withToken($this->token)
            ->get('https://api.fonnte.com' . $endpoint);

        return $response->json();
    }
}
