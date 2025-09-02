<?php

namespace App\Http\Controllers;

use App\Models\Payments;

class TotalUnpaidController extends Controller
{
    public function index(){
       $unpaid = Payments::where('status', 'unpaid')->get();
       return view('admin.totals.total_unpaid', compact('unpaid')); 
    }
}
