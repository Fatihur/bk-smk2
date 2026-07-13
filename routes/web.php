<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisPelanggaranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\PengaturanPoinController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\Select2Controller;
use App\Http\Controllers\SuratTeguranController;
use App\Http\Controllers\WhatsappSettingController;
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
    Route::get('/data-siswa', [SiswaController::class, 'index']);
    Route::post('/data-siswa', [SiswaController::class, 'store']);
    Route::post('/data-siswa/import', [SiswaController::class, 'import']);
    Route::get('/data-siswa/{siswa}/edit', [SiswaController::class, 'edit']);
    Route::put('/data-siswa/{siswa}', [SiswaController::class, 'update']);
    Route::delete('/data-siswa/{siswa}', [SiswaController::class, 'destroy']);

    Route::get('/jenis-pelanggaran', [JenisPelanggaranController::class, 'index'])->name('jenis-pelanggaran.index');
    Route::post('/jenis-pelanggaran', [JenisPelanggaranController::class, 'store']);
    Route::put('/jenis-pelanggaran/{jenisPelanggaran}', [JenisPelanggaranController::class, 'update']);
    Route::delete('/jenis-pelanggaran/{jenisPelanggaran}', [JenisPelanggaranController::class, 'destroy']);

    Route::get('/pengaturan-poin', [PengaturanPoinController::class, 'index'])->name('pengaturan-poin.index');
    Route::put('/pengaturan-poin', [PengaturanPoinController::class, 'update'])->name('pengaturan-poin.update');

    Route::get('/pelanggaran/input', [PelanggaranController::class, 'index'])->name('pelanggaran.input');
    Route::post('/pelanggaran', [PelanggaranController::class, 'store']);

    Route::get('/select2/siswa', [Select2Controller::class, 'siswa'])->name('select2.siswa');
    Route::get('/select2/jenis', [Select2Controller::class, 'jenis'])->name('select2.jenis');

    Route::get('/pengaturan-whatsapp', [WhatsappSettingController::class, 'index'])->name('whatsapp.settings');

    Route::prefix('api/whatsapp')->group(function () {
        Route::get('/status', [WhatsappSettingController::class, 'status']);
        Route::post('/start', [WhatsappSettingController::class, 'start']);
        Route::post('/stop', [WhatsappSettingController::class, 'stop']);
        Route::post('/destroy', [WhatsappSettingController::class, 'destroy']);
        Route::get('/logs', [WhatsappSettingController::class, 'logs']);
    });

    Route::post('/surat-teguran/{suratTeguran}/kirim-wa', [SuratTeguranController::class, 'kirimWa'])->name('teguran.kirim-wa');
});

require __DIR__.'/auth.php';
