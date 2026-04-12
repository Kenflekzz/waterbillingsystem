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
            $body = $resp->body();
            
            // Log raw response
            Log::debug('Mocean raw', [
                'status' => $resp->status(),
                'body' => $body,
            ]);

            $data = json_decode($body, true);

            // If not JSON, return raw body
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => 'failed', 'error' => 'Non-JSON response: ' . $body];
            }

            // If no messages array
            if (empty($data['messages'])) {
                return ['status' => 'failed', 'error' => 'No messages in response', 'raw' => $data];
            }

            $msg = $data['messages'][0];
            
            // Return full details for any non-success
            if (($msg['status'] ?? 2) != 0) {
                return [
                    'status' => 'failed',
                    'mocean_status' => $msg['status'] ?? 'missing',
                    'mocean_err_msg' => $msg['err_msg'] ?? 'No error message',
                    'mocean_raw' => $msg,
                ];
            }

            return ['status' => 'sent', 'id' => $msg['msgid']];

        } catch (\Exception $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}