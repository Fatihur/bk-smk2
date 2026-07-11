<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\OrangTuaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth', 'role:guru_bk,kepala_sekolah'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:guru_bk'])->group(function () {
    Route::get('/data-kelas', [KelasController::class, 'index']);
    Route::post('/data-kelas', [KelasController::class, 'store']);
    Route::put('/data-kelas/{kelas}', [KelasController::class, 'update']);
    Route::delete('/data-kelas/{kelas}', [KelasController::class, 'destroy']);

    Route::get('/data-siswa', [SiswaController::class, 'index']);
    Route::post('/data-siswa', [SiswaController::class, 'store']);
    Route::post('/data-siswa/import', [SiswaController::class, 'import']);
    Route::put('/data-siswa/{siswa}', [SiswaController::class, 'update']);
    Route::delete('/data-siswa/{siswa}', [SiswaController::class, 'destroy']);

    Route::get('/data-orang-tua', [OrangTuaController::class, 'index']);
    Route::post('/data-orang-tua', [OrangTuaController::class, 'store']);
    Route::put('/data-orang-tua/{orangTua}', [OrangTuaController::class, 'update']);
    Route::delete('/data-orang-tua/{orangTua}', [OrangTuaController::class, 'destroy']);
});

require __DIR__.'/auth.php';
