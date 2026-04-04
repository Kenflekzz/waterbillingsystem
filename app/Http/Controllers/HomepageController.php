<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homepage;
use App\Models\UserAnnouncement;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class HomepageController extends Controller
{
    // -------------------------
    // Admin: Edit homepage
    // -------------------------
    public function edit()
    {
        $homepage = Homepage::first() ?? Homepage::create([]);
        return view('admin.edit-homepage', compact('homepage'));
    }

    public function update(Request $request)
    {
        $homepage = Homepage::first() ?? Homepage::create([]);

        $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'announcement_heading' => 'nullable|string|max:255',
            'announcement_text' => 'nullable|string',
            'announcement_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'advisories.*.title' => 'nullable|string|max:255',
            'advisories.*.text'  => 'nullable|string|max:1000',
            'advisories.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'connect_title' => 'nullable|string|max:255',
            'connect_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'footer_address' => 'nullable|string|max:255',
            'footer_contact' => 'nullable|string|max:50',
            'footer_email' => 'nullable|email|max:255',
            'facebook_link' => 'nullable|string|max:255',
            'twitter_link'  => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
        ]);

        $data = $request->only([
            'hero_title',
            'hero_subtitle',
            'announcement_heading',
            'announcement_text',
            'connect_title',
            'footer_address',
            'footer_contact',
            'footer_email',
            'facebook_link',
            'twitter_link',
            'email',
        ]);

        // Announcement image
        if ($request->hasFile('announcement_image')) {
            $data['announcement_image'] = $this->uploadToCloudinary($request->file('announcement_image'), 'homepage');
        }

        UserAnnouncement::updateOrCreate(
            ['id' => 1],
            [
                'title' => $data['announcement_heading'] ?? '',
                'content' => $data['announcement_text'] ?? '',
                'announcement_image' => $data['announcement_image'] ?? null,
                'scheduled_date' => now(),
            ]
        );

        // Advisories (3 structured items)
        $advisoriesData = [];
        if ($request->has('advisories')) {
            foreach ($request->advisories as $i => $advisory) {
                $item = [
                    'title' => $advisory['title'] ?? '',
                    'text'  => $advisory['text'] ?? '',
                ];
                if ($request->hasFile("advisories.$i.image")) {
                    $item['image'] = $this->uploadToCloudinary($request->file("advisories.$i.image"), 'advisories');
                } else {
                    $oldAdvisories = $homepage->advisories ?? [];
                    $item['image'] = $oldAdvisories[$i]['image'] ?? null;
                }
                $advisoriesData[] = $item;
            }
        }
        $data['advisories'] = $advisoriesData;

        // Connect Images (2 slots)
        $connectImages = [];
        foreach ([0, 1] as $i) {
            if ($request->hasFile("connect_images.$i")) {
                $connectImages[$i] = $this->uploadToCloudinary($request->file("connect_images.$i"), 'connect');
            } else {
                $oldConnect = $homepage->connect_images ?? [];
                $connectImages[$i] = $oldConnect[$i] ?? null;
            }
        }
        $data['connect_images'] = $connectImages;

        // Save all updates
        $homepage->update($data);

        return redirect()->back()->with('success', 'Homepage updated successfully.');
    }

    // -------------------------
    // Public: Display homepage
    // -------------------------
    public function index()
    {
        $homepage = Homepage::first();
        $announcements = UserAnnouncement::orderBy('created_at', 'desc')->get();
        return view('welcome', compact('homepage'));
    }

    // -------------------------
    // Helper: Upload to Cloudinary
    // -------------------------
    private function uploadToCloudinary($file, $folder)
    {
        $config = Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => ['secure' => true]
        ]);

        $uploadApi = new UploadApi($config);
        $result = $uploadApi->upload($file->getRealPath(), [
            'folder' => $folder
        ]);

        return $result['secure_url'];
    }
}