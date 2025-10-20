<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payments; // Assuming you have a Payment model for payments
use App\Models\Billings; // Assuming you have a Billing model for billings
use App\Models\Clients; // Assuming you have a Client model for clients
class AdminAuthController extends Controller
{

public function showLoginForm()
{
    return view('admin.adminlogin');
}

public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::guard('admin')->attempt($credentials)) {
        return redirect()->intended('/admin/dashboard');
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
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

public function register(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:admins,email',
        'password' => 'required|min:6|confirmed',
    ]);

    $admin = Admin::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    Auth::guard('admin')->login($admin);

    return redirect()->route('admin.dashboard');
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
   $request->validate([
    'email' => 'required|email',
    'password' => 'required|string',
   ]);
   
   $credentials = $request->only('email', 'password');

   if(Auth::guard('admin')->attempt($credentials)) {
      $request->session()->regenerate();

      return response()->json([
        'message' => 'Login successful',
        'redirect' => '/admin/dashboard',
      ]);
   }
   return response()->json(['error' => 'Invalid credentials'], 401);

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

}