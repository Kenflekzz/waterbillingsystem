<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?string $token;
    protected ?string $sender;
    protected ?string $url;

    public function __construct()
    {
        $this->token  = config('services.mocean.token');
        $this->sender = config('services.mocean.sender');
        $this->url    = config('services.mocean.url');
    }

    public function sendSMS(string $number, string $message): array
    {
        // Guard clause: check if properly configured
        if (empty($this->token) || empty($this->url)) {
            Log::error('Mocean SMS not configured', [
                'token_set' => !empty($this->token),
                'url_set'   => !empty($this->url),
            ]);
            return ['status' => 'failed', 'error' => 'SMS service not configured'];
        }

        $number = ltrim($number, '+');

        Log::debug('Mocean payload', [
            'mocean-api-token' => $this->token,
            'mocean-from'      => $this->sender,
            'mocean-to'        => $number,
            'mocean-text'      => $message,
        ]);

        try {
            $resp = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                ])
                ->post($this->url, [
                    'mocean-from'        => $this->sender,
                    'mocean-to'          => $number,
                    'mocean-text'        => $message,
                    'mocean-resp-format' => 'json',
                ]);

            Log::debug('Mocean raw response', [
                'http_status' => $resp->status(),
                'body'        => $resp->body(),
            ]);

            $payload = $resp->json();
            $msgId   = $payload['messages'][0]['msgid'] ?? 'no-id';
            $status  = $payload['messages'][0]['status'] ?? 2;

            Log::info('Mocean submit', [
                'to'     => $number,
                'id'     => $msgId,
                'status' => $status,
            ]);

            return match ($status) {
                0       => ['status' => 'sent',    'id' => $msgId],
                1       => ['status' => 'pending', 'id' => $msgId],
                default => ['status' => 'failed',  'error' => "Mocean error {$status}"],
            };

        } catch (\Exception $e) {
            Log::error('Mocean SMS exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'error' => 'SMS service error: ' . $e->getMessage()];
        }
    }
}