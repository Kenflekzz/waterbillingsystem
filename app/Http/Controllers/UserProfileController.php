<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class UserProfileController extends Controller
{
    // Display the profile page
    public function index()
    {
        $user = Auth::guard('user')->user();
        return view('user.profile', compact('user'));
    }

    // Update profile information including profile image
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('user')->user();

        // Validation
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20|unique:users,phone_number,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($request->filled('current_password') || $request->filled('new_password') || $request->filled('new_password_confirmation')) {
            $rules['current_password'] = 'required';
            $rules['new_password'] = 'required|confirmed|min:6';
        }

        $request->validate($rules);

        try {
            // Update user fields
            $user->first_name = $request->first_name;
            $user->last_name  = $request->last_name;
            $user->email      = $request->email;
            $user->phone_number = $request->phone_number;

            // Image upload to Cloudinary
            if ($request->hasFile('profile_image')) {
                // Delete old image from Cloudinary if exists
                if ($user->profile_image_public_id) {
                    Cloudinary::destroy($user->profile_image_public_id);
                }

                $uploaded = Cloudinary::upload($request->file('profile_image')->getRealPath(), [
                    'folder' => 'profile_images'
                ]);

                $user->profile_image = $uploaded->getSecurePath();
                $user->profile_image_public_id = $uploaded->getPublicId();
            }

            // Password update
            if ($request->filled('current_password') && $request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Current password is incorrect'
                    ], 422);
                }

                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            // Update clients table too
            \DB::table('clients')
                ->where('user_id', $user->id)
                ->update([
                    'contact_number' => $user->phone_number
                ]);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == "23000") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This phone number or email is already taken.'
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected database error occurred.'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully!'
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('user')->user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect'
                ], 422);
            }
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully!'
            ]);
        }

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
}