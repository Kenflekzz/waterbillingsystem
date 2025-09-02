<?php

namespace App\Http\Controllers;

use App\Models\Clients;

class totalSubscriberscontroller extends Controller
{
    public function index(){

        $subscribers = Clients::all();
        return view('admin.totals.total_subscribers', compact('subscribers'));
    }
}
