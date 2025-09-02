<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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



    public function logout(Request $request)
    {
        Auth::guard('user')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login');
    }

    public function dashboard()
    {
        return view('user.dashboard', [
            'user' => Auth::user()
        ]);
    }

    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'meter_number' => 'required|string|max:255|unique:users,meter_number',
            'phone_number' => 'required|string|max:15',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Users::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'meter_number' => $validated['meter_number'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        Auth::guard('user')->login($user);

        return redirect()->route('user.dashboard');
    }
}
