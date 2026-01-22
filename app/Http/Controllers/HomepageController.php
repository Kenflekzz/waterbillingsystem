<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Homepage;
use App\Models\UserAnnouncement;
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
            $data['announcement_image'] = $request->file('announcement_image')->store('homepage', 'public');
        }

        UserAnnouncement::updateOrCreate(
            ['id' => 1], // you can choose to always keep one record, or use other logic
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
                    $path = $request->file("advisories.$i.image")->store("uploads/advisories", "public");
                    $item['image'] = "storage/" . $path;
                } else {
                    $oldAdvisories = $homepage->advisories ?? [];
                    $item['image'] = $oldAdvisories[$i]['image'] ?? null;
                }
                $advisoriesData[] = $item;
            }
        }
        $data['advisories'] = $advisoriesData; // no json_encode()

        // Connect Images (2 slots)
        $connectImages = [];
        foreach ([0, 1] as $i) {
            if ($request->hasFile("connect_images.$i")) {
                $path = $request->file("connect_images.$i")->store("uploads/connect", "public");
                $connectImages[$i] = "storage/" . $path;
            } else {
                $oldConnect = $homepage->connect_images ?? [];
                $connectImages[$i] = $oldConnect[$i] ?? null;
            }
        }
        $data['connect_images'] = $connectImages; // no json_encode()

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

        $announcements = UserAnnouncement::orderBy('created_at' , 'desc')->get();
        return view('welcome', compact('homepage'));
    }
}
