<?php

use App\Http\Controllers\ApiDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/dashboard/stats', [ApiDashboardController::class, 'stats']);


