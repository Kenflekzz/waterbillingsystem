<?php

namespace App\Http\Controllers;

use App\Models\ProblemReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class ProblemReportController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'image'   => 'nullable|image|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $config = Configuration::instance([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => ['secure' => true]
            ]);

            $uploadApi = new UploadApi($config);
            $result = $uploadApi->upload($request->file('image')->getRealPath(), [
                'folder' => 'reports'
            ]);
            $imagePath = $result['secure_url'];
        }

        ProblemReport::create([
            'client_id'   => auth()->id(),
            'subject'     => $request->subject,
            'description' => $request->message,
            'image'       => $imagePath,
            'status'      => 'pending'
        ]);

        return back()->with('success', 'Your report has been sent successfully.');
    }

    public function showReportsPage()
    {
        $user = Auth::guard('user')->user();
        $reports = ProblemReport::where('client_id', $user->client_id)->get();
        return view('user.reports-page', compact('reports'));
    }
}