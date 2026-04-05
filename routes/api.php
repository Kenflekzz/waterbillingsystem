<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FlowReadingController;

Route::post('/flow-readings', [FlowReadingController::class, 'store']);