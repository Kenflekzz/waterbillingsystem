<?php

namespace App\Http\Controllers;

use App\Models\Clients;

class TotalSubscribersController extends Controller
{
    public function index(){

        $subscribers = Clients::orderBy('full_name' , 'asc')->get();
        return view('admin.totals.total_subscribers', compact('subscribers'));
    }
}
