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
     * Format phone number to 639XXXXXXXXX (NO +)
     */
    private function formatPhoneNumber($number): ?string
    {
        // Remove all non-digit characters
        $n = preg_replace('/\D+/', '', $number);

        if (empty($n)) {
            return null;
        }

        // Convert 09XXXXXXXXX → 639XXXXXXXXX
        if (strlen($n) === 11 && Str::startsWith($n, '09')) {
            $n = '63' . substr($n, 1);
        }

        // Convert 9XXXXXXXXX → 639XXXXXXXXX
        elseif (strlen($n) === 10 && Str::startsWith($n, '9')) {
            $n = '63' . $n;
        }

        // Ensure valid PH format
        return (strlen($n) === 12) ? $n : null;
    }

    /**
     * Show all clients
     */
    public function index()
    {
        $clients = Clients::all();
        return view('admin.messages', compact('clients'));
    }

    /**
     * Send message to all clients
     */
    public function sendGeneral(Request $request)
    {
        Log::info('CONTROLLER START - sendGeneral');

        $request->validate([
            'title' => 'required|string',
            'body'  => 'required|string',
        ]);

        $clients = Clients::where('status', '!=', 'CUT')->get();

        $failed = [];

        foreach ($clients as $client) {

            Log::info('PROCESSING CLIENT', [
                'id' => $client->id,
                'raw_number' => $client->contact_number
            ]);

            if (empty($client->contact_number)) {
                continue;
            }

            $to = $this->formatPhoneNumber($client->contact_number);

            Log::info('FORMATTED NUMBER', [
                'client_id' => $client->id,
                'to' => $to
            ]);

            if (!$to) {
                Log::warning("INVALID NUMBER SKIPPED", [
                    'client_id' => $client->id,
                    'raw' => $client->contact_number
                ]);

                $failed[] = $client->full_name;
                continue;
            }

            $message = $request->title . " - " . $request->body;

            $response = $this->sms->sendSMS($to, $message);

            Log::info("SMS RESPONSE", [
                'to' => $to,
                'client_id' => $client->id,
                'response' => $response
            ]);

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
     * Send message to one client
     */
    public function sendPersonal(Request $request)
    {
        Log::info('CONTROLLER START - sendPersonal');

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title'     => 'required|string',
            'body'      => 'required|string',
        ]);

        $client = Clients::findOrFail($request->client_id);

        if ($client->status === 'CUT') {
            return back()->with('error', 'Cannot send: Client is CUT.');
        }

        Log::info('CLIENT SELECTED', [
            'id' => $client->id,
            'raw_number' => $client->contact_number
        ]);

        $to = $this->formatPhoneNumber($client->contact_number);

        Log::info('FORMATTED NUMBER', [
            'to' => $to
        ]);

        if (!$to) {
            Log::warning("INVALID NUMBER", [
                'client_id' => $client->id,
                'raw' => $client->contact_number
            ]);

            return back()->with('error', 'Invalid phone number for client ' . $client->full_name);
        }

        $message = $request->title . " - " . $request->body;

        $response = $this->sms->sendSMS($to, $message);

        Log::info("SMS RESPONSE", [
            'to' => $to,
            'response' => $response
        ]);

        switch ($response['status'] ?? '') {
            case 'sent':
                return back()->with('success', 'Message delivered to ' . $client->full_name);

            case 'pending':
                return back()->with('info', 'Message queued. ID: ' . ($response['id'] ?? 'N/A'));

            default:
                $errorMsg = $response['error'] ?? json_encode($response);
                return back()->with('error', "Failed to send message: {$errorMsg}");
        }
    }
}