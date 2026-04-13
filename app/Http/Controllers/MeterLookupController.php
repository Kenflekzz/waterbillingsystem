<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clients;

class MeterLookupController extends Controller
{
    public function lookup($meter_no)
    {
        // Search for the meter number in clients table (using meter_no field)
        $client = Clients::where('meter_no', $meter_no)->first();
        
        if (!$client) {
            return response()->json([
                'found' => false,
                'message' => 'Meter number not found. Please contact your administrator.'
            ], 404);
        }
        
        // Check if this meter number is already registered as a user
        if ($client->user_id) {
            return response()->json([
                'found' => false,
                'message' => 'This meter number is already registered. Please login instead.'
            ], 409);
        }
        
        // Parse full_name into first_name and last_name
        $fullName = $client->full_name ?? '';
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        // Return the client details for auto-filling
        return response()->json([
            'found' => true,
            'data' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $client->full_name,
                'phone_number' => $client->contact_number,
                'email' => $client->email ?? '', // If email exists in clients table
                'barangay' => $client->barangay ?? '',
                'purok' => $client->purok ?? '',
                'meter_no' => $client->meter_no,
            ]
        ]);
    }
}