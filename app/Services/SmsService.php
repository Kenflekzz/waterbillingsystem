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
        // Format number: remove + and ensure it's in international format
        $number = ltrim($number, '+');
        
        // Ensure message is not empty and within limits
        if (empty($message)) {
            return ['status' => 'failed', 'error' => 'Message is empty'];
        }
        
        if (strlen($message) > 1600) {
            return ['status' => 'failed', 'error' => 'Message too long (max 1600 chars)'];
        }

        // Build query string manually to ensure proper encoding
        $params = [
            'mocean-api-token'   => config('mocean.token'),
            'mocean-from'        => $this->sender,
            'mocean-to'          => $number,
            'mocean-text'        => $message,
            'mocean-resp-format' => 'json',
        ];

        // Use query parameters instead of form body (GET request)
        $url = $this->url . '?' . http_build_query($params);
        
        try {
            $resp = Http::get($url);
            $body = $resp->body();

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['status' => 'failed', 'error' => 'Invalid response: ' . substr($body, 0, 200)];
            }

            // Check for API-level errors in response
            if (isset($data['status']) && $data['status'] != 0) {
                return [
                    'status' => 'failed',
                    'error' => $data['err_msg'] ?? 'API error code: ' . $data['status'],
                ];
            }

            if (empty($data['messages']) || !isset($data['messages'][0])) {
                return ['status' => 'failed', 'error' => 'No message response', 'raw' => $data];
            }

            $msg = $data['messages'][0];
            $status = (int) ($msg['status'] ?? 2);

            if ($status !== 0) {
                return [
                    'status' => 'failed',
                    'error' => $msg['err_msg'] ?? 'Failed with status: ' . $status,
                ];
            }

            return ['status' => 'sent', 'id' => $msg['msgid']];

        } catch (\Exception $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}