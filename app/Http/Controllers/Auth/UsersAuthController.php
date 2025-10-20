<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use Illuminate\Support\Facades\Log;
use App\Models\Clients;

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

        return response()->json([
            'success' => true,
            'redirect' => route('user.dashboard'),
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
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'meter_number'  => 'required|string|max:255|unique:users,meter_number',
            'phone_number'  => 'required|string|max:15',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:6|confirmed',
        ], [
            'meter_number.unique' => 'The meter number is already been taken.', // ✅ Custom message
        ]);

        // 🔹 Check if the meter number exists in clients table
        $existsInClients = Clients::where('meter_no', $validated['meter_number'])->exists();

        if (!$existsInClients) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors'  => [
                        'meter_number' => ['The meter number does not exist in the system.'] // ✅ Same style
                    ]
                ], 422);
            }

            return back()->withErrors([
                'meter_number' => 'The meter number does not exist in the system.'
            ])->withInput();
        }

        // ✅ Create the user
        $user = Users::create([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'meter_number' => $validated['meter_number'],
            'phone_number' => $validated['phone_number'],
            'email'        => $validated['email'],
            'password'     => bcrypt($validated['password']),
        ]);

        Auth::guard('user')->login($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('user.dashboard')
            ]);
        }

        return redirect()->route('user.dashboard');
    }



}
