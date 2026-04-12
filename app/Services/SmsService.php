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
        // Read directly from $_ENV where Render stores secrets
        $this->token  = $_ENV['MOCEAN_API_TOKEN'] ?? throw new \RuntimeException('MOCEAN_API_TOKEN missing');
        $this->sender = $_ENV['MOCEAN_SENDER'] ?? 'MYAPP';
        $this->url    = $_ENV['MOCEAN_URL'] ?? 'https://rest.moceanapi.com/rest/2/sms';

        Log::info('SMS Service loaded', ['token_preview' => substr($this->token, 0, 10)]);
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