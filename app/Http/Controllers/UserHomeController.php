<?php

namespace App\Http\Controllers;

use App\Models\UserAnnouncement;
use App\Models\Homepage;

class UserHomeController extends Controller
{
    public function index()
    {
        $homepage = Homepage::first();

        $announcements = UserAnnouncement::latest()->take(1)->get();

        return view('user.home', compact('announcements', 'homepage'));
    }
}
