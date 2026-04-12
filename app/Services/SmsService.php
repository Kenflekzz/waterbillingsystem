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
        
        // Log config values on construct
        Log::debug('SmsService config', [
            'token_set' => !empty($this->token),
            'token_preview' => substr($this->token, 0, 10) . '...',
            'sender' => $this->sender,
            'url' => $this->url,
        ]);
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

        // DEBUG: Check each parameter
        foreach ($payload as $key => $value) {
            if (empty($value)) {
                Log::error("Empty Mocean parameter: {$key}");
            }
        }

        Log::debug('Mocean full payload', $payload);

        try {
            $resp = Http::asForm()->post($this->url, $payload);
            $body = $resp->body();

            Log::info('MOCEAN DEBUG', [
                'http_status' => $resp->status(),
                'body' => $body,
                'payload_sent' => $payload,
            ]);

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => 'failed', 'error' => 'Non-JSON: ' . $body];
            }

            if (empty($data['messages'])) {
                return ['status' => 'failed', 'error' => 'No messages', 'raw' => $data];
            }

            $msg = $data['messages'][0];
            $status = (int) ($msg['status'] ?? 2);

            if ($status !== 0) {
                return [
                    'status' => 'failed',
                    'mocean_err_msg' => $msg['err_msg'] ?? 'Code: ' . $status,
                    'mocean_raw' => $msg,
                ];
            }

            return ['status' => 'sent', 'id' => $msg['msgid']];

        } catch (\Exception $e) {
            Log::error('SMS exception', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}