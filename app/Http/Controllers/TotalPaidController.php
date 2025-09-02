<?php

namespace App\Http\Controllers;

use App\Models\Payments;

class TotalPaidController extends Controller
{
    public function index(){
        $totalPaid = Payments::where('status', 'paid')->get();
        return view('admin.totals.total_paid', compact('totalPaid'));
    }
}
