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

            // Log full response for debugging
            Log::debug('Mocean full response', [
                'http_status' => $resp->status(),
                'body' => $data,
            ]);

            // Check if response has expected structure
            if (!isset($data['messages']) || !is_array($data['messages']) || empty($data['messages'])) {
                return [
                    'status' => 'failed', 
                    'error' => 'Invalid Mocean response: ' . json_encode($data)
                ];
            }

            $msg = $data['messages'][0];
            $status = (int) ($msg['status'] ?? 2);
            $msgId = $msg['msgid'] ?? 'no-id';
            $errMsg = $msg['err_msg'] ?? null;

            // Return detailed error info
            if ($status !== 0 && $status !== 1) {
                return [
                    'status' => 'failed',
                    'error_code' => $status,
                    'error_message' => $errMsg ?? "Unknown error (code: {$status})",
                    'mocean_response' => $msg,
                ];
            }

            return match ($status) {
                0 => ['status' => 'sent', 'id' => $msgId],
                1 => ['status' => 'pending', 'id' => $msgId],
                default => ['status' => 'failed', 'error' => 'Unexpected status'],
            };

        } catch (\Exception $e) {
            Log::error('SMS exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}