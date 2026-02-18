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

    private function formatPhoneNumber($number): ?string
    {
        $n = preg_replace('/\D+/', '', $number);

        if (strlen($n) === 10 && Str::startsWith($n, '9')) {
            $n = '63' . $n;
        }
        if (strlen($n) === 11 && Str::startsWith($n, '09')) {
            $n = '63' . substr($n, 1);
        }

        return strlen($n) === 12 ? $n : null;
    }

    // 📩 Send to all clients (EXCEPT CUT)
    public function sendGeneral(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        // 🔴 FIX: Exclude CUT status clients
        $clients = Clients::where('status', '!=', 'CUT')->get();
        $failed = [];

        foreach ($clients as $client) {
            if (!empty($client->contact_number)) {
                $to = $this->formatPhoneNumber($client->contact_number);
                if ($to === null) {
                    Log::warning("Bad number skipped", ['client' => $client->id, 'raw' => $client->contact_number]);
                    continue;
                }
                $message = $request->title . " - " . $request->body;

                $response = $this->sms->sendSMS($to, $message);
                Log::info("Sending SMS to {$to} with message: {$message}");

                if ($response['status'] !== 'sent') {
                    $failed[] = $client->full_name;
                }
            }
        }

        if (count($failed) > 0) {
            return back()->with('error', 'Some messages failed: ' . implode(', ', $failed));
        }

        return back()->with('success', 'General message sent successfully!');
    }

    // 📨 Send to a specific client (check CUT status)
    public function sendPersonal(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $client = Clients::findOrFail($request->client_id);

        // 🔴 FIX: Block if CUT status
        if ($client->status === 'CUT') {
            return back()->with('error', 'Cannot send: Client ' . $client->full_name . ' is CUT (disconnected).');
        }

        $to = $this->formatPhoneNumber($client->contact_number);
        $message = $request->title . " - " . $request->body;

        $response = $this->sms->sendSMS($to, $message);

        switch ($response['status']) {
            case 'sent':
                return back()->with('success', 'Message delivered to ' . $client->full_name);
            case 'pending':
                return back()->with('info', 'Message queued (pending) – ID: ' . $response['id']);
            default:
                return back()->with('error', 'Failed to send message: ' . ($response['error'] ?? 'Unknown'));
        }
    }

    public function index()
    {
        $clients = Clients::all();
        return view('admin.messages', compact('clients'));
    }
}