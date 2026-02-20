<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmsService;
use App\Models\Clients;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    protected $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Format phone number to +639XXXXXXXXX
     */
    private function formatPhoneNumber($number): ?string
    {
        // Remove all non-digit characters
        $n = preg_replace('/\D+/', '', $number);

        // Convert 09XXXXXXXXX or 9XXXXXXXXX to 639XXXXXXXXX
        if (strlen($n) === 10 && Str::startsWith($n, '9')) {
            $n = '63' . $n;
        }
        if (strlen($n) === 11 && Str::startsWith($n, '09')) {
            $n = '63' . substr($n, 1);
        }

        // Return number with '+' if valid
        return strlen($n) === 12 ? '+' . $n : null;
    }

    /**
     * Show all clients to send messages
     */
    public function index()
    {
        $clients = Clients::all();
        return view('admin.messages', compact('clients'));
    }

    /**
     * Send a message to all clients
     */
    public function sendGeneral(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body'  => 'required|string',
        ]);

        $clients = Clients::all();
        $failed  = [];

        foreach ($clients as $client) {
            if (empty($client->contact_number)) continue;

            $to = $this->formatPhoneNumber($client->contact_number);
            if (!$to) {
                Log::warning("Skipped client with invalid number", [
                    'client_id' => $client->id,
                    'raw'       => $client->contact_number
                ]);
                $failed[] = $client->full_name;
                continue;
            }

            $message = $request->title . " - " . $request->body;

            // Send SMS via SmsService
            $response = $this->sms->sendSMS($to, $message);
            Log::info("SMS attempt", ['to' => $to, 'client_id' => $client->id, 'response' => $response]);

            if (($response['status'] ?? '') !== 'sent') {
                $failed[] = $client->full_name;
            }
        }

        if (count($failed) > 0) {
            return back()->with('error', 'Some messages failed: ' . implode(', ', $failed));
        }

        return back()->with('success', 'General message sent successfully!');
    }

    /**
     * Send a message to a specific client
     */
    public function sendPersonal(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title'     => 'required|string',
            'body'      => 'required|string',
        ]);

        $client = Clients::findOrFail($request->client_id);
        $to = $this->formatPhoneNumber($client->contact_number);

        if (!$to) {
            Log::warning("Invalid client number", [
                'client_id' => $client->id,
                'raw'       => $client->contact_number
            ]);
            return back()->with('error', 'Invalid phone number for client ' . $client->full_name);
        }

        $message = $request->title . " - " . $request->body;

        $response = $this->sms->sendSMS($to, $message);
        Log::info("SMS attempt", ['to' => $to, 'client_id' => $client->id, 'response' => $response]);

        switch ($response['status'] ?? '') {
            case 'sent':
                return back()->with('success', 'Message delivered to ' . $client->full_name);
            case 'pending':
                return back()->with('info', 'Message queued (pending) – ID: ' . ($response['id'] ?? 'N/A'));
            default:
                $errorMsg = $response['error'] ?? 'Unknown Mocean error';
                return back()->with('error', "Failed to send message: {$errorMsg}");
        }
    }
}
