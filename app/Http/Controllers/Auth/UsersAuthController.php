<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Illuminate\Support\Facades\Log;
use App\Models\Clients;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    /*
     * Traditional login (Blade form)
    
    
    public function login(Request $request)
    {
        $request->validate([
            'meter_number' => 'required|string',
            'password' => 'required|string',
        ]);

        Log::info('Login attempt for meter_number: ' . $request->meter_number);

        if (Auth::guard('user')->attempt([
            'meter_number' => $request->meter_number,
            'password' => $request->password,
        ])) {
            $request->session()->regenerate();

            Log::info('Login successful for meter_number: ' . $request->meter_number);

            return $request->expectsJson()
                ? response()->json([
                    'success' => true,
                    'redirect' => route('user.dashboard')
                ])
                : redirect()->route('user.dashboard');
        }

        Log::warning('Login failed for meter_number: ' . $request->meter_number);

        return $request->expectsJson()
            ? response()->json([
                'success' => false,
                'message' => 'Invalid credentials, please contact the admin.'
            ], 401)
            : back()->withErrors([
                'meter_number' => 'Invalid credentials, please contact the admin.',
            ]);
    }
    */

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

        //$request->session()->invalidate();
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


        // Set the high consumption limit (C.U)
        $limit = 500; // You can change anytime

        return view('user.consumption', compact(
            'user',
            'currentConsumption',
            'previousConsumption',
            'limit'
        ));
    }


    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'meter_number'  => 'required|string|max:255|unique:users,meter_number',
            'phone_number'  => 'required|string|max:15',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:6|confirmed',
        ]);

        // Check if meter exists in clients
        $existsInClients = Clients::where('meter_no', $validated['meter_number'])->exists();

        if (!$existsInClients) {
            return response()->json([
                'success' => false,
                'errors'  => [
                    'meter_number' => ['The meter number does not exist in the system.']
                ]
            ], 422);
        }

        // Create user
        $user = Users::create([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'meter_number' => $validated['meter_number'],
            'phone_number' => $validated['phone_number'],
            'email'        => $validated['email'],
            'password'     => bcrypt($validated['password']),
        ]);

        // Link to client
        $client = Clients::where('meter_no', $validated['meter_number'])
                        ->where('contact_number', $validated['phone_number'])
                        ->first();

        if ($client) {
            $client->update(['user_id' => $user->id]);
        }

        // Auto login new user
        Auth::guard('user')->login($user);

        // Mark as NEW user so navbar shows "Welcome"
        session(['is_new_user' => true]);

        return response()->json([
            'success'  => true,
            'redirect' => route('user.home')
        ]);
    }
    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $exists = Users::where('email', $request->email)->exists();

        if ($exists) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user = Users::where('email', $request->email)->first();
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(15);
            $user->save();
            Mail::send('mails.otp', ['recepient' => $user, 'otp' => $otp], function ($msg) use ($user) {
                $msg->to($user->email)
                    ->subject('Password Reset – One-Time Password (OTP)');
            });
        }

        return response()->json([
            'message' => $exists
                ? 'OTP sent to your registered e-mail.'
                : 'E-mail not found in our records.',
            'otpSent' => $exists
        ], $exists ? 200 : 422);
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

        Mail::raw('Your Magallanes Water Billing password was changed successfully.', 
            fn ($msg) => $msg->to($user->email)
                        ->subject('Password Changed Notification')
        );

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }





}
