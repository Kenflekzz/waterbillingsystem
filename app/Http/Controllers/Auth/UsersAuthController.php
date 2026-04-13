<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Illuminate\Support\Facades\Log;
use App\Models\Clients;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;

class UsersAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('user.userlogin');
    }

    public function showRegisterForm()
    {
        return view('user.userregister');
    }

    /**
     * Vue/API login
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('user')->attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::guard('user')->user();

            // Determine if this is the first login
            $isNew = !$user->last_login_at;

            // Store result for navbar greeting
            session(['is_new_user' => $isNew]);

            // Update last login timestamp
            $user->last_login_at = now();
            $user->save();

            return response()->json([
                'success' => true,
                'redirect' => route('user.home'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials, please contact the admin.'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('user')->logout();

        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }

    public function consumption()
    {
        $user = Auth::guard('user')->user();

        // Get client associated with the user (via meter number)
        $user = Auth::user();

        $latestData = \App\Models\BehavioralData::where('user_id', $user->id)
            ->where('metric_name', 'consumption')
            ->orderByDesc('created_at')
            ->take(2)
            ->get();

        $currentConsumption  = $latestData->first()->value ?? 0;
        $previousConsumption = $latestData->skip(1)->first()->value ?? 0;

        $limit = 50; // highest safe C.U

        return view('user.consumption', compact('currentConsumption', 'previousConsumption', 'limit'));
    }

    public function apiRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meter_number' => 'required|string|exists:clients,meter_no',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the client by meter number
        $client = Clients::where('meter_no', $request->meter_number)->first();
        
        if (!$client) {
            return response()->json([
                'errors' => ['meter_number' => ['Invalid meter number']]
            ], 422);
        }
        
        // Check if already registered
        if ($client->user_id) {
            return response()->json([
                'errors' => ['meter_number' => ['This meter number is already registered']]
            ], 422);
        }
        
        // Verify that the provided name matches the client record
        $fullName = trim($client->full_name ?? '');
        $providedFullName = trim($request->first_name . ' ' . $request->last_name);
        
        if (strcasecmp($fullName, $providedFullName) !== 0) {
            return response()->json([
                'errors' => [
                    'first_name' => ['Name does not match our records for this meter number']
                ]
            ], 422);
        }
        
        // Verify phone number matches
        if ($client->contact_number !== $request->phone_number) {
            return response()->json([
                'errors' => [
                    'phone_number' => ['Phone number does not match our records for this meter number']
                ]
            ], 422);
        }
        
        // Create the user
        $user = Users::create([
            'meter_number' => $request->meter_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'profile_image' => null,
        ]);
        
        // Link the user to the client record
        $client->user_id = $user->id;
        $client->save();
        
        // Log the user in
        auth('user')->login($user);
        
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user
        ], 201);
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = Users::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'E-mail not found in our records.',
                'otpSent' => false
            ], 422);
        }

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        try {
            // Configure Brevo API
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', env('BREVO_API_KEY'));
            
            $apiInstance = new TransactionalEmailsApi(new Client(), $config);
            
            // Prepare email content
            $htmlContent = view('mails.otp', ['recepient' => $user, 'otp' => $otp])->render();
            
            // Create email
            $email = new \Brevo\Client\Model\SendSmtpEmail([
                'to' => [[
                    'email' => $user->email, 
                    'name' => $user->first_name . ' ' . $user->last_name
                ]],
                'subject' => 'Password Reset – One-Time Password (OTP)',
                'html_content' => $htmlContent,
                'sender' => [
                    'email' => 'magallaneswaterbilling@gmail.com', 
                    'name' => 'MEEDMO Magallanes Water Billing'
                ]
            ]);
            
            // Send email
            $apiInstance->sendTransacEmail($email);
            
            Log::info('OTP sent successfully to: ' . $user->email);
            
            return response()->json([
                'message' => 'OTP sent to your registered e-mail.',
                'otpSent' => true
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Brevo API Error: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error sending OTP: ' . $e->getMessage(),
                'otpSent' => false
            ], 500);
        }
    }

    // verify OTP + change password
    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|digits:6',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = Users::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->where('otp_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user->password = bcrypt($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        // Send confirmation email via Brevo (don't fail if it doesn't work)
        try {
            $config = Configuration::getDefaultConfiguration()
                ->setApiKey('api-key', env('BREVO_API_KEY'));
            
            $apiInstance = new TransactionalEmailsApi(new Client(), $config);
            
            $confirmationHtml = "<h2>Password Changed Successfully</h2>
                                <p>Your Magallanes Water Billing password has been changed successfully.</p>
                                <p>If you did not perform this action, please contact support immediately.</p>
                                <p>Thank you,<br>Magallanes Water Billing System</p>";
            
            $email = new \Brevo\Client\Model\SendSmtpEmail([
                'to' => [[
                    'email' => $user->email, 
                    'name' => $user->first_name . ' ' . $user->last_name
                ]],
                'subject' => 'Password Changed Successfully',
                'html_content' => $confirmationHtml,
                'sender' => [
                    'email' => 'magallaneswaterbilling@gmail.com', 
                    'name' => 'MEEDMO Magallanes Water Billing'
                ]
            ]);
            
            $apiInstance->sendTransacEmail($email);
            
        } catch (\Exception $e) {
            // Don't fail the request if confirmation email fails
            Log::warning('Confirmation email failed: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }
}