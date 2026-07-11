<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\OrangTuaController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\PengaturanPoinController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuratTeguranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth', 'role:guru_bk,kepala_sekolah'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pelanggaran', [PelanggaranController::class, 'riwayat'])->name('pelanggaran.riwayat');
    Route::get('/surat-teguran', [SuratTeguranController::class, 'index'])->name('teguran.index');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
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

    Route::get('/jenis-pelanggaran', [JenisPelanggaranController::class, 'index'])->name('jenis-pelanggaran.index');
    Route::post('/jenis-pelanggaran', [JenisPelanggaranController::class, 'store']);
    Route::put('/jenis-pelanggaran/{jenisPelanggaran}', [JenisPelanggaranController::class, 'update']);
    Route::delete('/jenis-pelanggaran/{jenisPelanggaran}', [JenisPelanggaranController::class, 'destroy']);

    Route::get('/pengaturan-poin', [PengaturanPoinController::class, 'index'])->name('pengaturan-poin.index');
    Route::put('/pengaturan-poin', [PengaturanPoinController::class, 'update'])->name('pengaturan-poin.update');

    Route::get('/pelanggaran/input', [PelanggaranController::class, 'index'])->name('pelanggaran.input');
    Route::post('/pelanggaran', [PelanggaranController::class, 'store']);
});

require __DIR__.'/auth.php';
