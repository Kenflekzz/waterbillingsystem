<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $apiKey;
    protected $senderName;

    public function __construct()
    {
        $this->apiKey = env('SEMAPHORE_API_KEY');
        $this->senderName = env('SEMAPHORE_SENDERNAME', 'SEMAPHORE');
    }

    public function sendSMS($number, $message)
    {
        try {
            $response = Http::post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => $this->apiKey,
                'number' => $number,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'response' => $response->json()];
            } else {
                return ['success' => false, 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
