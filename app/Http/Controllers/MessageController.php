<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SmsService;
use App\Models\Clients;

class MessageController extends Controller
{
    protected $sms;

    public function __construct(SmsService $sms)
    {
        $this->sms = $sms;
    }

    private function formatPhoneNumber($number)
    {
        $number = preg_replace('/\D+/', '', $number);

        if (strpos($number, '0') === 0) {
            $number = '+63' . substr($number, 1);
        } elseif (strpos($number, '9') === 0) {
            $number = '+63' . $number;
        } elseif (strpos($number, '63') === 0 && strpos($number, '+63') !== 0) {
            $number = '+' . $number;
        }

        return $number;
    }

    // 📩 Send to all clients
    public function sendGeneral(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $clients = Clients::all();
        $failed = [];

        foreach ($clients as $client) {
            if (!empty($client->contact_number)) {
                $to = $this->formatPhoneNumber($client->contact_number);
                $message = $request->title . " - " . $request->body;

                $response = $this->sms->sendSMS($to, $message);

                if (!$response['success']) {
                    $failed[] = $client->name;
                }
            }
        }

        if (count($failed) > 0) {
            return back()->with('error', 'Some messages failed: ' . implode(', ', $failed));
        }

        return back()->with('success', 'General message sent successfully!');
    }

    // 📨 Send to a specific client
    public function sendPersonal(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $client = Clients::findOrFail($request->client_id);
        $to = $this->formatPhoneNumber($client->contact_number);
        $message = $request->title . " - " . $request->body;

        $response = $this->sms->sendSMS($to, $message);

        if ($response['success']) {
            return back()->with('success', 'Message sent to ' . $client->name);
        } else {
            return back()->with('error', 'Failed to send message: ' . ($response['error'] ?? 'Unknown error'));
        }
    }

    public function index()
    {
        $clients = Clients::all();
        return view('admin.messages', compact('clients'));
    }
}
