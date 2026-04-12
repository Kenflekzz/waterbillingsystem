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
        // Remove + sign if present
        $number = ltrim($number, '+');

        // Validate message
        if (empty($message)) {
            return ['status' => 'failed', 'error' => 'Message is empty'];
        }

        if (strlen($message) > 1600) {
            return ['status' => 'failed', 'error' => 'Message too long (max 1600 chars)'];
        }

        // Mocean API payload (TOKEN MODE)
        $params = [
            'mocean-api-token'   => $this->token,
            'mocean-from'        => $this->sender,
            'mocean-to'          => $number,
            'mocean-text'        => $message,
            'mocean-resp-format' => 'json',
        ];

        try {
            // IMPORTANT: use POST + form data (NOT GET)
            $resp = Http::asForm()->post($this->url, $params);

            $body = $resp->body();
            $data = json_decode($body, true);

            // Handle invalid JSON response
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 'failed',
                    'error' => 'Invalid response: ' . substr($body, 0, 200),
                ];
            }

            // Handle API-level errors
            if (isset($data['status']) && $data['status'] != 0) {
                return [
                    'status' => 'failed',
                    'error' => $data['err_msg'] ?? 'API error code: ' . $data['status'],
                ];
            }

            // Validate message response
            if (empty($data['messages'][0])) {
                return [
                    'status' => 'failed',
                    'error' => 'No message response',
                    'raw' => $data
                ];
            }

            $msg = $data['messages'][0];
            $status = (int) ($msg['status'] ?? 2);

            if ($status !== 0) {
                return [
                    'status' => 'failed',
                    'error' => $msg['err_msg'] ?? 'Failed with status: ' . $status,
                ];
            }

            return [
                'status' => 'sent',
                'id' => $msg['msgid'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Mocean SMS Error', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
}