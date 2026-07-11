<?php

use App\Http\Controllers\ApiDashboardController;
use App\Http\Controllers\WhatsappSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/dashboard/stats', [ApiDashboardController::class, 'stats']);


