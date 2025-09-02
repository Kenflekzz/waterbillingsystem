<?php

namespace App\Http\Controllers;

use App\Models\Payments;

class TotalDisconnectedController extends Controller
{
    public function index(){
        $totalDisconnected = Payments::where('status', 'disconnected')->get();
        return view('admin.totals.total_disconnected', compact('totalDisconnected'));
    }
}
