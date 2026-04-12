<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $token;
    protected string $sender;
    protected string $url;

    public function __construct()
    {
        $this->token  = config('mocean.token');
        $this->sender = config('mocean.sender');
        $this->url    = config('mocean.url');
    }

    public function sendSMS(string $number, string $message): array
    {
        $number = ltrim($number, '+');

        $payload = [
            'mocean-api-token'   => $this->token,
            'mocean-from'        => $this->sender,
            'mocean-to'          => $number,
            'mocean-text'        => $message,
            'mocean-resp-format' => 'json',
        ];

        try {
            $resp = Http::asForm()->post($this->url, $payload);
            $data = $resp->json();

            $status = (int) ($data['messages'][0]['status'] ?? 2);

            return match ($status) {
                0 => ['status' => 'sent', 'id' => $data['messages'][0]['msgid']],
                1 => ['status' => 'pending', 'id' => $data['messages'][0]['msgid']],
                default => ['status' => 'failed', 'error' => 'Mocean error'],
            };

        } catch (\Exception $e) {
            Log::error('SMS failed', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}