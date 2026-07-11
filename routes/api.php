<?php

use App\Http\Controllers\ApiDashboardController;
use App\Http\Controllers\WhatsappSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/dashboard/stats', [ApiDashboardController::class, 'stats']);

Route::middleware(['auth', 'role:guru_bk'])->prefix('whatsapp')->group(function () {
    Route::get('/status', [WhatsappSettingController::class, 'status']);
    Route::post('/start', [WhatsappSettingController::class, 'start']);
    Route::post('/stop', [WhatsappSettingController::class, 'stop']);
    Route::post('/destroy', [WhatsappSettingController::class, 'destroy']);
});
