<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payments; 
use App\Models\Billings; 
use App\Models\Clients;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class AdminAuthController extends Controller
{

    public function showLoginForm()
    {
        return view('admin.adminlogin');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ], [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Please enter your password.',
            ]);
        } catch (ValidationException $e) {
            // Return only the first error to Vue
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' =>(string) $firstError ?? 'Please fill in all required fields.'
            ], 422);
        }

        if (!Auth::guard('admin')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.'
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => route('admin.dashboard')
        ]);
    }

    public function dashboard()
    {
        // Get all consumers (you can adjust which columns to show)
        $consumers = Clients::select('id', 'full_name as name')->get();

        // Build a distinct list of barangays from Clients table
        $barangayNames = Clients::whereNotNull('barangay')
                                ->where('barangay', '!=', '')
                                ->distinct()
                                ->pluck('barangay');

        // Make an array of objects with id + name so Blade can do $b->id / $b->name
        $barangays = $barangayNames->values()->map(function ($name, $index) {
            return (object)[
                'id'   => $index + 1, // synthetic id; replace with actual id if you have a barangays table
                'name' => $name,
            ];
        });

        // Pass them to the view
        return view('admin.admindashboard', compact('barangays', 'consumers'));
    }


    public function logout( Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('admin/login');
    }

    public function showRegisterForm()
    {
        return view('admin.adminregister');
    }

    
    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $admin = Admin::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::guard('admin')->login($admin);

        return response()->json(['message' => 'Admin registered successfully.'], 201);
    }
    public function apiLogin(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        if ($validator->fails()) {
            // Only take the first error message
            $firstError = collect($validator->errors()->all())->first();

            return response()->json([
                'success' => false,
                'message' => $firstError
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => '/admin/dashboard',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }


    public function payment()
    {
        $payments = Payments::paginate(25); // or ->paginate(10)
        return view('admin.payments', compact('payments'));
    }

    public function billings()
    {
        $billings = Billings::paginate(25); // or ->paginate(10)
        return view('admin.billings', compact('billings'));
    }
    public function clients()
    {
        $clients =Clients::paginate(25); // or ->paginate(10)
        return view('admin.clients', compact('clients'));
    }

    public function admins()
    {
        $admins = Admin::paginate(25); // or ->paginate(10)
        return view('admin.admins', compact('admins'));
    }

    public function sendResetOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $exists = Admin::where('email', $request->email)->exists();

        if ($exists) {
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $admin = Admin::where('email', $request->email)->first();
            $admin->otp = $otp;
            $admin->otp_expires_at = now()->addMinutes(15);
            $admin->save();
            Mail::send('mails.otp', ['recepient' => $admin, 'otp' => $otp], function ($msg) use ($admin) {
                $msg->to($admin->email)
                    ->subject('Password Reset – One-Time Password (OTP)');
            });
        }

        return response()->json([
            'message' => $exists
                ? 'OTP sent to your registered e-mail.'
                : 'E-mail not found in our records.',
            'otpSent' => $exists
        ], $exists ? 200 : 422);   // 422 triggers alert-danger
    }

    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|digits:6',
            'password' => 'required|min:6|confirmed'
        ]);
        $admin = Admin::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->where('otp_expires_at', '>', now())
                    ->first();
        if (!$admin) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }
        $admin->password = bcrypt($request->password);
        $admin->otp = null;
        $admin->otp_expires_at = null;
        $admin->save();
        Mail::raw('Your admin password was changed successfully.', fn($m) => $m->to($admin->email)->subject('Admin Password Changed'));
        return response()->json(['message' => 'Password changed successfully.'], 200);
    }

}