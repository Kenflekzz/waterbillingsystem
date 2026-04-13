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
        $this->token = config('mocean.token');
        $this->sender = config('mocean.sender');
        $this->url = config('mocean.url');
        
        // Debug: Log if config values are loaded (remove in production)
        Log::info('Mocean Config Loaded', [
            'has_token' => !empty($this->token),
            'has_sender' => !empty($this->sender),
            'url' => $this->url
        ]);
    }

    public function sendSMS(string $number, string $message): array
    {
        // Remove + sign if present
        $number = ltrim($number, '+');

        // Validate inputs
        if (empty($message)) {
            return ['status' => 'failed', 'error' => 'Message is empty'];
        }

        if (empty($this->token)) {
            Log::error('Mocean token is missing');
            return ['status' => 'failed', 'error' => 'API token not configured'];
        }

        if (strlen($message) > 1600) {
            return ['status' => 'failed', 'error' => 'Message too long (max 1600 chars)'];
        }

        // Mocean API payload - ONLY the message parameters
        $params = [
            'mocean-from' => $this->sender,
            'mocean-to'   => $number,
            'mocean-text' => $message,
        ];

        try {
            // Send request with Bearer token in Authorization header
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'application/json',
            ])->asForm()->post($this->url, $params);

            // Log response for debugging
            Log::info('Mocean API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Handle HTTP errors
            if ($response->failed()) {
                return [
                    'status' => 'failed',
                    'error' => 'HTTP ' . $response->status() . ': ' . $response->body()
                ];
            }

            $data = $response->json();

            // Handle JSON parsing errors
            if ($data === null) {
                return [
                    'status' => 'failed',
                    'error' => 'Invalid JSON response: ' . substr($response->body(), 0, 200)
                ];
            }

            // Handle API-level errors
            if (isset($data['status']) && $data['status'] != 0) {
                return [
                    'status' => 'failed',
                    'error' => $data['err_msg'] ?? 'API error code: ' . $data['status']
                ];
            }

            // Check for successful message response
            if (isset($data['messages'][0]) && $data['messages'][0]['status'] == 0) {
                return [
                    'status' => 'sent',
                    'id' => $data['messages'][0]['msgid'] ?? null
                ];
            }

            return [
                'status' => 'failed',
                'error' => $data['messages'][0]['err_msg'] ?? 'Unknown error',
                'raw' => $data
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Mocean Connection Error', ['error' => $e->getMessage()]);
            return [
                'status' => 'failed',
                'error' => 'Connection failed: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Mocean SMS Error', ['error' => $e->getMessage()]);
            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }
}