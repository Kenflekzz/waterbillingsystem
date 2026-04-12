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
        // Try config first, then env(), then hardcoded fallback
        $this->token  = $this->getConfigValue('MOCEAN_API_TOKEN', 'services.mocean.token');
        $this->sender = $this->getConfigValue('MOCEAN_SENDER', 'services.mocean.sender', 'MYAPP');
        $this->url    = $this->getConfigValue('MOCEAN_URL', 'services.mocean.url', 'https://rest.moceanapi.com/rest/2/sms');

        Log::info('SMS Service initialized', [
            'token_length' => strlen($this->token),
            'sender' => $this->sender,
            'url' => $this->url,
        ]);
    }

    private function getConfigValue(string $envKey, string $configKey, ?string $default = null): string
    {
        // Priority: $_ENV > env() > config() > default
        $value = $_ENV[$envKey] ?? 
                 getenv($envKey) ?: 
                 env($envKey) ?: 
                 config($configKey) ?: 
                 $default;
        
        if (empty($value)) {
            Log::error("Config missing: {$envKey} / {$configKey}");
            throw new \RuntimeException("Missing required config: {$envKey}");
        }
        
        return $value;
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

        Log::debug('Mocean request', $payload);

        try {
            $resp = Http::asForm()->post($this->url, $payload);

            Log::debug('Mocean response', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);

            $data = $resp->json();
            
            if (!isset($data['messages'][0])) {
                return ['status' => 'failed', 'error' => 'Invalid response from Mocean'];
            }

            $msg = $data['messages'][0];
            
            return match ((int)($msg['status'] ?? 2)) {
                0 => ['status' => 'sent', 'id' => $msg['msgid'] ?? 'unknown'],
                1 => ['status' => 'pending', 'id' => $msg['msgid'] ?? 'unknown'],
                default => ['status' => 'failed', 'error' => $msg['err_msg'] ?? "Error code {$msg['status']}"],
            };

        } catch (\Exception $e) {
            Log::error('SMS send failed', ['error' => $e->getMessage()]);
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }
}