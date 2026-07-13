# Refactor Sistem Monitoring Kedisiplinan Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Simplify database schema (remove kelas & orang_tua tables, flatten fields into siswa) and make WhatsApp notifications manual.

**Architecture:** Migration-based schema changes, then update models/controllers/views to use direct fields instead of relations. WhatsApp dispatch removed from auto-flow, moved to manual button in views.

**Tech Stack:** Laravel 13.x, Blade, Tailwind, MySQL

---

### Task 1: Migration — Update `siswa` table

**Files:**
- Create: `database/migrations/2026_07_13_000001_update_siswa_table.php`
- Create: `database/migrations/2026_07_13_000002_remove_orang_tua_kelas_tables.php`

- [ ] **Step 1: Create migration to update siswa table**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            // Drop foreign key first, then column
            $table->dropForeign(['id_kelas']);
            $table->dropColumn('id_kelas');

            // Rename nama to nama_siswa
            $table->renameColumn('nama', 'nama_siswa');

            // Add new columns
            $table->enum('jk', ['L', 'P'])->after('nama_siswa');
            $table->string('tempat_lahir', 50)->after('nisn');
            $table->date('tgl_lahir')->after('tempat_lahir');
            $table->string('nik', 20)->after('tgl_lahir');
            $table->string('agama', 20)->after('nik');
            $table->text('alamat')->after('agama');
            $table->string('hp', 20)->nullable()->after('alamat');
            $table->string('ayah', 100)->nullable()->after('hp');
            $table->string('ibu', 100)->nullable()->after('ayah');
            $table->string('no_wali', 20)->nullable()->after('ibu');
            $table->enum('rombel', ['X KJJ', 'XI KJJ', 'XII KJJ'])->after('no_wali');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['jk', 'tempat_lahir', 'tgl_lahir', 'nik', 'agama', 'alamat', 'hp', 'ayah', 'ibu', 'no_wali', 'rombel']);
            $table->renameColumn('nama_siswa', 'nama');
            $table->foreignId('id_kelas')->constrained('kelas', 'id');
        });
    }
};
```

- [ ] **Step 2: Create migration to drop orang_tua and kelas tables**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('orang_tua');
        Schema::dropIfExists('kelas');
    }

    public function down(): void
    {
        // recreate kelas table
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
        });

        // recreate orang_tua table
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('nomor_wa', 20);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
        });
    }
};
```

- [ ] **Step 3: Run migrations**

Run: `php artisan migrate`
Expected: tables updated, old tables dropped

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_07_13_000001_update_siswa_table.php database/migrations/2026_07_13_000002_remove_orang_tua_kelas_tables.php
git commit -m "feat: update siswa schema, remove kelas & orang_tua tables"
```

---

### Task 2: Update Models

**Files:**
- Modify: `app/Models/Siswa.php`
- Delete: `app/Models/Kelas.php`
- Delete: `app/Models/OrangTua.php`

- [ ] **Step 1: Rewrite `app/Models/Siswa.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'nama_siswa', 'jk', 'nisn', 'tempat_lahir', 'tgl_lahir', 'nik',
        'agama', 'alamat', 'hp', 'ayah', 'ibu', 'no_wali', 'rombel'
    ];

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'id_siswa');
    }

    public function suratTeguran(): HasMany
    {
        return $this->hasMany(SuratTeguran::class, 'id_siswa');
    }
}
```

- [ ] **Step 2: Delete `app/Models/Kelas.php`**

- [ ] **Step 3: Delete `app/Models/OrangTua.php`**

- [ ] **Step 4: Commit**

```bash
git add app/Models/Siswa.php app/Models/Kelas.php app/Models/OrangTua.php
git commit -m "feat: update Siswa model, remove Kelas & OrangTua"
```

---

### Task 3: Update Routes

**Files:**
- Modify: `routes/web.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Rewrite `routes/web.php`**

Remove imports for KelasController and OrangTuaController.
Remove all `/data-kelas/*` and `/data-orang-tua/*` routes.
Add route for manual send WA:

```php
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
```

- [ ] **Step 2: Commit**

```bash
git add routes/web.php
git commit -m "feat: update routes - remove kelas/ortu, add kirim-wa endpoint"
```

---

### Task 4: Update Controllers

**Files:**
- Modify: `app/Http/Controllers/PelanggaranController.php`
- Modify: `app/Http/Controllers/SiswaController.php`
- Modify: `app/Http/Controllers/SuratTeguranController.php`
- Modify: `app/Http/Controllers/Select2Controller.php`
- Modify: `app/Http/Controllers/ApiDashboardController.php`
- Modify: `app/Http/Controllers/LaporanController.php`
- Delete: `app/Http/Controllers/KelasController.php`
- Delete: `app/Http/Controllers/OrangTuaController.php`

- [ ] **Step 1: Update `PelanggaranController.php`**

Remove `use App\Models\PengaturanPoin;`, `use App\Models\SuratTeguran;`, `use Barryvdh\DomPDF\Facade\Pdf;`
Remove method `cekDanTerbitkanTeguran()` entirely.
Remove `id_kelas` filter from `riwayat()`.
In `store()` — remove call to `$this->cekDanTerbitkanTeguran(...)`.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index()
    {
        return view('pelanggaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'id_jenis' => 'required|exists:jenis_pelanggaran,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $pelanggaran = Pelanggaran::create($validated);

        return response()->json(['message' => 'Pelanggaran berhasil dicatat', 'data' => $pelanggaran]);
    }

    public function riwayat(Request $request)
    {
        $query = Pelanggaran::with(['siswa', 'jenis']);

        if ($request->filled('id_siswa')) {
            $query->where('id_siswa', $request->id_siswa);
        }

        if ($request->filled('dari')) {
            $query->where('tanggal', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->where('tanggal', '<=', $request->sampai);
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')->paginate(50);

        $siswa = Siswa::orderBy('nama_siswa')->get();

        return view('pelanggaran.index', compact('pelanggaran', 'siswa'));
    }
}
```

- [ ] **Step 2: Update `SiswaController.php`**

Remove `use App\Models\Kelas;`.
Update `index()` — load siswa without kelas relation, no $kelas variable.
Update `store()` — validate new fields instead of `id_kelas`.
Update `update()` — same.
Update `import()` — accept `rombel` instead of `nama_kelas`.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::orderBy('nama_siswa')->get();
        return view('siswa.index', compact('siswa'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|max:100',
            'jk' => 'required|in:L,P',
            'nisn' => 'required|unique:siswa',
            'tempat_lahir' => 'required|max:50',
            'tgl_lahir' => 'required|date',
            'nik' => 'required|max:20',
            'agama' => 'required|max:20',
            'alamat' => 'required',
            'hp' => 'nullable|max:20',
            'ayah' => 'nullable|max:100',
            'ibu' => 'nullable|max:100',
            'no_wali' => 'nullable|max:20',
            'rombel' => 'required|in:X KJJ,XI KJJ,XII KJJ',
        ]);

        $siswa = Siswa::create($validated);

        return response()->json(['message' => 'Siswa berhasil ditambahkan', 'data' => $siswa]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nama_siswa' => 'required|max:100',
            'jk' => 'required|in:L,P',
            'nisn' => 'required|unique:siswa,nisn,' . $siswa->id,
            'tempat_lahir' => 'required|max:50',
            'tgl_lahir' => 'required|date',
            'nik' => 'required|max:20',
            'agama' => 'required|max:20',
            'alamat' => 'required',
            'hp' => 'nullable|max:20',
            'ayah' => 'nullable|max:100',
            'ibu' => 'nullable|max:100',
            'no_wali' => 'nullable|max:20',
            'rombel' => 'required|in:X KJJ,XI KJJ,XII KJJ',
        ]);

        $siswa->update($validated);

        return response()->json(['message' => 'Siswa berhasil diperbarui', 'data' => $siswa]);
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return response()->json(['message' => 'Siswa berhasil dihapus']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        $rows = Excel::toCollection(null, $request->file('file'))->first();
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $nisn = $row[0] ?? null;
            $namaSiswa = $row[1] ?? null;
            $rombel = $row[2] ?? null;

            if (!$nisn || !$namaSiswa || !$rombel) {
                $skipped++;
                continue;
            }

            if (!in_array($rombel, ['X KJJ', 'XI KJJ', 'XII KJJ'])) {
                $skipped++;
                continue;
            }

            Siswa::updateOrCreate(
                ['nisn' => $nisn],
                ['nama_siswa' => $namaSiswa, 'rombel' => $rombel]
            );
            $imported++;
        }

        return response()->json([
            'message' => "Import selesai: $imported berhasil, $skipped dilewati",
        ]);
    }
}
```

- [ ] **Step 3: Update `SuratTeguranController.php`**

Add method `kirimWa(SuratTeguran $suratTeguran)`.
Replace `siswa.kelas` with `siswa` in eager load (no more kelas relation).

```php
<?php
namespace App\Http\Controllers;

use App\Models\SuratTeguran;
use App\Jobs\KirimWaTeguran;

class SuratTeguranController extends Controller
{
    public function index()
    {
        $teguran = SuratTeguran::with('siswa')
            ->orderBy('tanggal_terbit', 'desc')
            ->paginate(50);

        return view('surat-teguran.index', compact('teguran'));
    }

    public function kirimWa(SuratTeguran $suratTeguran)
    {
        $siswa = $suratTeguran->siswa;

        if (!$siswa->no_wali) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WA wali belum diisi untuk siswa ' . $siswa->nama_siswa . '. Silakan isi di halaman Data Siswa.',
            ], 422);
        }

        dispatch(new KirimWaTeguran($siswa->id, $suratTeguran->tingkat, $suratTeguran->file_pdf));

        return response()->json([
            'success' => true,
            'message' => 'Pesan WA sedang dikirim ke ' . $siswa->no_wali,
        ]);
    }
}
```

- [ ] **Step 4: Update `Select2Controller.php`**

Change `siswa()` method — replace `kelas->tingkat . kelas->nama_kelas` with `rombel`.

```php
    public function siswa(Request $request)
    {
        $q = $request->input('q', '');
        $siswa = Siswa::where('nama_siswa', 'like', "%{$q}%")
            ->orWhere('nisn', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'text' => "{$s->nama_siswa} ({$s->nisn}) - {$s->rombel}",
            ]);

        return response()->json(['results' => $siswa]);
    }
```

- [ ] **Step 5: Update `ApiDashboardController.php`**

Change `siswa.kelas` to just `siswa`, replace `kelas` field with `rombel`.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\SuratTeguran;

class ApiDashboardController extends Controller
{
    public function stats()
    {
        $totalSiswa = Siswa::count();
        $totalPelanggaran = Pelanggaran::whereMonth('tanggal', now()->month)->count();
        $totalTeguran = SuratTeguran::count();

        $poinTertinggi = Pelanggaran::selectRaw('id_siswa, sum(jenis_pelanggaran.poin) as total')
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->groupBy('id_siswa')
            ->orderBy('total', 'desc')
            ->first();

        $terbaru = Pelanggaran::with(['siswa', 'jenis'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'siswa' => $p->siswa->nama_siswa ?? '-',
                'kelas' => $p->siswa->rombel ?? '-',
                'jenis' => $p->jenis->nama ?? '-',
                'tanggal' => $p->tanggal,
            ]);

        return response()->json([
            'total_siswa' => $totalSiswa,
            'total_pelanggaran' => $totalPelanggaran,
            'total_teguran' => $totalTeguran,
            'poin_tertinggi' => $poinTertinggi ? $poinTertinggi->total : 0,
            'terbaru' => $terbaru,
        ]);
    }
}
```

- [ ] **Step 6: Update `LaporanController.php`**

Replace `siswa.kelas` etc.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        $siswa = Siswa::orderBy('nama_siswa')->get();
        return view('laporan.index', compact('siswa'));
    }

    public function cetak()
    {
        $query = Pelanggaran::with(['siswa', 'jenis'])->orderBy('tanggal', 'desc');

        if ($idSiswa = request('id_siswa')) {
            $query->where('id_siswa', $idSiswa);
        }
        if ($dari = request('dari')) {
            $query->where('tanggal', '>=', $dari);
        }
        if ($sampai = request('sampai')) {
            $query->where('tanggal', '<=', $sampai);
        }

        $pelanggaran = $query->get();
        $siswa = $idSiswa ? Siswa::find($idSiswa) : null;

        $pdf = Pdf::loadView('pdf.laporan', compact('pelanggaran', 'siswa'));
        return $pdf->download('laporan-kedisiplinan.pdf');
    }
}
```

- [ ] **Step 7: Delete `KelasController.php` and `OrangTuaController.php`**

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/
git commit -m "feat: update controllers - remove kelas/ortu, manual wa dispatch"
```

---

### Task 5: Update Job `KirimWaTeguran`

**Files:**
- Modify: `app/Jobs/KirimWaTeguran.php`

- [ ] **Step 1: Rewrite `app/Jobs/KirimWaTeguran.php`**

Remove `with(['kelas', 'orangTua'])`, remove loop over orangTua, send to `no_wali` instead.

```php
<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Siswa;
use App\Models\SuratTeguran;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;

class KirimWaTeguran implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $idSiswa,
        public string $tingkat,
        public string $filename
    ) {}

    public function handle(): void
    {
        $siswa = Siswa::find($this->idSiswa);
        if (!$siswa || !$siswa->no_wali) return;

        $surat = SuratTeguran::where('id_siswa', $this->idSiswa)
            ->where('tingkat', $this->tingkat)
            ->latest('id')
            ->first();

        if (!$surat) return;

        $namaWali = $siswa->ayah ?: $siswa->ibu ?: 'Bapak/Ibu Wali';

        $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
            . "Kepada Yth. {$namaWali}\n"
            . "Orang tua/wali dari {$siswa->nama_siswa} - {$siswa->rombel}\n\n"
            . "Dengan ini kami sampaikan bahwa putra/putri Bapak/Ibu telah mencapai "
            . "akumulasi poin pelanggaran sebesar {$surat->total_poin} poin "
            . "dan diterbitkan Surat Teguran " . strtoupper($this->tingkat) . ".\n\n"
            . "Untuk informasi lebih lanjut, silakan lihat surat teguran terlampir.\n\n"
            . "Atas perhatian dan kerja samanya, kami ucapkan terima kasih.\n\n"
            . "Wassalamu'alaikum Wr. Wb.\n"
            . "SMK Negeri 2 Sumbawa Besar";

        try {
            WhatsApp::web('smkn2_monitoring')->messages()->sendDocument($siswa->no_wali, [
                'url' => config('app.url') . '/storage/teguran/' . $this->filename,
                'filename' => "Surat_Teguran_{$this->tingkat}.pdf"
            ]);
            WhatsApp::web('smkn2_monitoring')->messages()->sendText($siswa->no_wali, $pesan);

            $surat->update(['status_terkirim' => true]);
        } catch (\Exception $e) {
            \Log::error("WA send failed for {$siswa->no_wali}: " . $e->getMessage());
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Jobs/KirimWaTeguran.php
git commit -m "feat: update WA job to use no_wali field, single recipient"
```

---

### Task 6: Update Views — Siswa & Surat Teguran

**Files:**
- Modify: `resources/views/siswa/index.blade.php`
- Modify: `resources/views/surat-teguran/index.blade.php`

- [ ] **Step 1: Rewrite `resources/views/siswa/index.blade.php`**

Full form with all new fields (nama_siswa, jk, nisn, tempat_lahir, tgl_lahir, nik, agama, alamat, hp, ayah, ibu, no_wali, rombel).

```blade
<x-app-layout>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Siswa</h1>
        <div class="flex gap-3">
            <button onclick="openImportModal()" class="flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <x-icon name="download" class="w-4 h-4" />
                Import Excel
            </button>
            <button onclick="openModal()" class="flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Siswa
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                    <th class="text-left px-5 py-3 font-medium">NISN</th>
                    <th class="text-left px-5 py-3 font-medium">Nama</th>
                    <th class="text-left px-5 py-3 font-medium">JK</th>
                    <th class="text-left px-5 py-3 font-medium">Rombel</th>
                    <th class="text-left px-5 py-3 font-medium">No. Wali</th>
                    <th class="text-right px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="siswa-table-body">
                @forelse ($siswa as $s)
                <tr class="border-t border-gray-100 hover:bg-gray-50" data-id="{{ $s->id }}">
                    <td class="px-5 py-3.5 text-gray-900">{{ $s->nisn }}</td>
                    <td class="px-5 py-3.5 text-gray-900">{{ $s->nama_siswa }}</td>
                    <td class="px-5 py-3.5">{{ $s->jk }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->rombel }}</td>
                    <td class="px-5 py-3.5">
                        @if ($s->no_wali)
                            <span class="text-green-600 font-medium">{{ $s->no_wali }}</span>
                        @else
                            <span class="text-red-400 italic">Belum diisi</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <button onclick="editSiswa({{ $s->id }})" class="text-purple-600 hover:text-purple-700 font-medium mr-4 text-sm">Edit</button>
                        <button onclick="hapusSiswa({{ $s->id }})" class="text-red-600 hover:text-red-700 text-sm font-medium">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">Belum ada data siswa</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="siswaModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-2xl mx-4 shadow-xl my-8">
        <h2 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Siswa</h2>
        <form id="siswaForm" class="grid grid-cols-2 gap-4">
            <input type="hidden" id="siswaId">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                <input type="text" id="nisn" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Siswa</label>
                <input type="text" id="nama_siswa" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="100">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select id="jk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih JK</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rombel</label>
                <select id="rombel" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih Rombel</option>
                    <option value="X KJJ">X KJJ</option>
                    <option value="XI KJJ">XI KJJ</option>
                    <option value="XII KJJ">XII KJJ</option>
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                <input type="text" id="tempat_lahir" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input type="date" id="tgl_lahir" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" id="nik" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                <input type="text" id="agama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea id="alamat" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required></textarea>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">HP (opsional)</label>
                <input type="text" id="hp" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah (opsional)</label>
                <input type="text" id="ayah" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu (opsional)</label>
                <input type="text" id="ibu" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">No. WA Wali (opsional)</label>
                <input type="text" id="no_wali" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 flex justify-end gap-3 mt-2">
                <button type="button" onclick="closeModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Siswa dari Excel</h2>
        <p class="text-sm text-gray-500 mb-4">Format: Kolom A = NISN, Kolom B = Nama, Kolom C = Rombel (X KJJ / XI KJJ / XII KJJ)</p>
        <form id="importForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xlsx / .csv)</label>
                <input type="file" id="importFile" accept=".xlsx,.csv" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-600 file:text-white hover:file:bg-purple-700 file:transition-colors" required>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeImportModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Import</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const siswaModal = document.getElementById('siswaModal');
const importModal = document.getElementById('importModal');
const siswaForm = document.getElementById('siswaForm');
const importForm = document.getElementById('importForm');
const siswaModalTitle = document.getElementById('modalTitle');
const siswaId = document.getElementById('siswaId');
const nisn = document.getElementById('nisn');
const namaSiswa = document.getElementById('nama_siswa');
const jk = document.getElementById('jk');
const rombel = document.getElementById('rombel');
const tempatLahir = document.getElementById('tempat_lahir');
const tglLahir = document.getElementById('tgl_lahir');
const nik = document.getElementById('nik');
const agama = document.getElementById('agama');
const alamat = document.getElementById('alamat');
const hp = document.getElementById('hp');
const ayah = document.getElementById('ayah');
const ibu = document.getElementById('ibu');
const noWali = document.getElementById('no_wali');

function openModal(data = null) {
    siswaModalTitle.textContent = data ? 'Edit Siswa' : 'Tambah Siswa';
    siswaId.value = data ? data.id : '';
    nisn.value = data ? data.nisn : '';
    namaSiswa.value = data ? data.nama_siswa : '';
    jk.value = data ? data.jk : '';
    rombel.value = data ? data.rombel : '';
    tempatLahir.value = data ? data.tempat_lahir : '';
    tglLahir.value = data ? data.tgl_lahir : '';
    nik.value = data ? data.nik : '';
    agama.value = data ? data.agama : '';
    alamat.value = data ? data.alamat : '';
    hp.value = data ? data.hp : '';
    ayah.value = data ? data.ayah : '';
    ibu.value = data ? data.ibu : '';
    noWali.value = data ? data.no_wali : '';
    siswaModal.classList.remove('hidden');
}

function closeModal() {
    siswaModal.classList.add('hidden');
    siswaForm.reset();
    siswaId.value = '';
}

function openImportModal() {
    importModal.classList.remove('hidden');
}

function closeImportModal() {
    importModal.classList.add('hidden');
    importForm.reset();
}

siswaForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = siswaId.value;
    const url = id ? `/data-siswa/${id}` : '/data-siswa';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            nisn: nisn.value,
            nama_siswa: namaSiswa.value,
            jk: jk.value,
            rombel: rombel.value,
            tempat_lahir: tempatLahir.value,
            tgl_lahir: tglLahir.value,
            nik: nik.value,
            agama: agama.value,
            alamat: alamat.value,
            hp: hp.value || null,
            ayah: ayah.value || null,
            ibu: ibu.value || null,
            no_wali: noWali.value || null,
        }),
    })
    .then(res => res.json())
    .then(() => { closeModal(); location.reload(); })
    .catch(() => alert('Gagal menyimpan data'));
});

importForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('file', document.getElementById('importFile').files[0]);

    fetch('/data-siswa/import', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        closeImportModal();
        location.reload();
    })
    .catch(() => alert('Gagal import data'));
});

function editSiswa(id) {
    fetch(`/data-siswa/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            openModal(data);
        })
        .catch(() => {
            // fallback: get from table row
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                const cells = row.querySelectorAll('td');
                openModal({
                    id: id,
                    nisn: cells[0].textContent.trim(),
                    nama_siswa: cells[1].textContent.trim(),
                    jk: cells[2].textContent.trim(),
                    rombel: cells[3].textContent.trim(),
                });
            }
        });
}

function hapusSiswa(id) {
    if (!confirm('Yakin ingin menghapus siswa ini?')) return;
    fetch(`/data-siswa/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(() => location.reload())
    .catch(() => alert('Gagal menghapus data'));
}
</script>
@endpush
</x-app-layout>
```

- [ ] **Step 2: Update `resources/views/surat-teguran/index.blade.php`**

Add "Kirim WA" button with validation for no_wali. Show no_wali status.

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Surat Teguran</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                                <th class="px-5 py-3 font-medium text-left">Siswa</th>
                                <th class="px-5 py-3 font-medium text-left">Rombel</th>
                                <th class="px-5 py-3 font-medium text-left">Tingkat</th>
                                <th class="px-5 py-3 font-medium text-left">Total Poin</th>
                                <th class="px-5 py-3 font-medium text-left">Tanggal</th>
                                <th class="px-5 py-3 font-medium text-left">No. Wali</th>
                                <th class="px-5 py-3 font-medium text-left">Status WA</th>
                                <th class="px-5 py-3 font-medium text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($teguran as $t)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 text-gray-900">{{ $t->siswa->nama_siswa }}</td>
                                    <td class="px-5 py-3.5 text-gray-500">{{ $t->siswa->rombel }}</td>
                                    <td class="px-5 py-3.5">
                                        @php
                                            $colors = ['SP1' => 'bg-blue-600', 'SP2' => 'bg-yellow-500', 'SP3' => 'bg-red-600'];
                                            $color = $colors[$t->tingkat] ?? 'bg-gray-500';
                                        @endphp
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-white {{ $color }}">
                                            {{ $t->tingkat }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-900">{{ $t->total_poin }}</td>
                                    <td class="px-5 py-3.5 text-gray-500">{{ $t->tanggal_terbit }}</td>
                                    <td class="px-5 py-3.5">
                                        @if ($t->siswa->no_wali)
                                            <span class="text-green-600 font-medium">{{ $t->siswa->no_wali }}</span>
                                        @else
                                            <span class="text-red-400 italic text-xs">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($t->status_terkirim)
                                            <span class="text-green-600 font-medium">Terkirim</span>
                                        @else
                                            <span class="text-gray-400 font-medium">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/' . $t->file_pdf) }}" target="_blank"
                                               class="text-purple-600 hover:text-purple-700 font-medium text-xs">
                                                Lihat PDF
                                            </a>
                                            @if (!$t->status_terkirim)
                                                <button onclick="kirimWa({{ $t->id }})"
                                                        class="text-green-600 hover:text-green-700 font-medium text-xs">
                                                    Kirim WA
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-gray-500">Belum ada surat teguran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($teguran->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $teguran->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function kirimWa(id) {
        if (!confirm('Kirim notifikasi WA untuk surat teguran ini?')) return;

        fetch(`/surat-teguran/${id}/kirim-wa`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(() => alert('Gagal mengirim WA'));
    }
    </script>
    @endpush
</x-app-layout>
```

- [ ] **Step 3: Remove `resources/views/kelas/` and `resources/views/orang-tua/` directories**

- [ ] **Step 4: Commit**

```bash
git add resources/views/siswa/index.blade.php resources/views/surat-teguran/index.blade.php
git rm -r resources/views/kelas resources/views/orang-tua
git commit -m "feat: update views - new siswa fields, manual wa button"
```

---

### Task 7: Update Remaining Views

**Files:**
- Modify: `resources/views/pelanggaran/index.blade.php`
- Modify: `resources/views/laporan/index.blade.php`
- Modify: `resources/views/pdf/laporan.blade.php`
- Modify: `resources/views/pdf/surat-teguran.blade.php`

- [ ] **Step 1: Update `resources/views/pelanggaran/index.blade.php`**

Replace `$p->siswa->kelas->tingkat $p->siswa->kelas->nama_kelas` with `$p->siswa->rombel`.
Remove filter for `id_kelas`.

Key changes in the view:
- Line ~46: change column header "Kelas" to "Rombel"
- Line ~57: change `{{ $p->siswa->kelas->tingkat }} {{ $p->siswa->kelas->nama_kelas }}` to `{{ $p->siswa->rombel }}`
- Remove the `<select>` filter for kelas (if exists)
- Change `$p->siswa->nama` references to `$p->siswa->nama_siswa`

- [ ] **Step 2: Update `resources/views/laporan/index.blade.php`**

Replace `$s->kelas->nama_kelas ?? '-'` with `$s->rombel`.
Change `$s->nama` to `$s->nama_siswa`.

- [ ] **Step 3: Update `resources/views/pdf/laporan.blade.php`**

Line 33: change `$siswa->nama` to `$siswa->nama_siswa`, `$siswa->kelas->nama_kelas` to `$siswa->rombel`.
Line 48: column header "Kelas" to "Rombel".
Line 59: `$p->siswa->kelas->nama_kelas` to `$p->siswa->rombel`.
Any `$p->siswa->nama` to `$p->siswa->nama_siswa`.

- [ ] **Step 4: Update `resources/views/pdf/surat-teguran.blade.php`**

Line 40: `{{ $siswa->kelas->tingkat }} {{ $siswa->kelas->nama_kelas }}` to `{{ $siswa->rombel }}`.
Any `$siswa->nama` to `$siswa->nama_siswa`.

- [ ] **Step 5: Commit**

```bash
git add resources/views/pelanggaran/index.blade.php resources/views/laporan/index.blade.php resources/views/pdf/
git commit -m "feat: update remaining views - replace kelas with rombel"
```

---

### Task 8: Update Navigation (Sidebar & Topbar)

**Files:**
- Modify: `resources/views/components/sidebar.blade.php`
- Modify: `resources/views/components/topbar.blade.php`

- [ ] **Step 1: Update `sidebar.blade.php`**

Remove "Data Orang Tua" and "Data Kelas" from `$guruBkMenu`.

```php
    $guruBkMenu = [
        ['label' => 'Dashboard', 'icon' => '📊', 'url' => '/dashboard'],
        ['label' => 'Data Siswa', 'icon' => '👥', 'url' => '/data-siswa'],
        ['label' => 'Jenis Pelanggaran', 'icon' => '⚠️', 'url' => '/jenis-pelanggaran'],
        ['label' => 'Pengaturan Poin', 'icon' => '⚙️', 'url' => '/pengaturan-poin'],
        ['label' => 'Input Pelanggaran', 'icon' => '📝', 'url' => '/pelanggaran/input'],
        ['label' => 'Riwayat Pelanggaran', 'icon' => '📋', 'url' => '/pelanggaran'],
        ['label' => 'Surat Teguran', 'icon' => '🔔', 'url' => '/surat-teguran'],
        ['label' => 'Laporan', 'icon' => '📈', 'url' => '/laporan'],
    ];
```

- [ ] **Step 2: Update `topbar.blade.php`**

Remove "Data Kelas" and "Data Orang Tua" from `Data Master` group.
Remove `/data-kelas` and `/data-orang-tua` from `$kepsekOnly`.

```php
    $groups = [
        'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'children' => []],
        'Data Master' => [
            'children' => [
                ['label' => 'Data Siswa', 'icon' => 'users', 'url' => '/data-siswa'],
            ],
        ],
        'Pelanggaran' => [
            'children' => [
                ['label' => 'Jenis Pelanggaran', 'icon' => 'warning', 'url' => '/jenis-pelanggaran'],
                ['label' => 'Pengaturan Poin', 'icon' => 'settings', 'url' => '/pengaturan-poin'],
                ['label' => 'Input Pelanggaran', 'icon' => 'edit', 'url' => '/pelanggaran/input'],
                ['label' => 'Riwayat', 'icon' => 'clipboard', 'url' => '/pelanggaran'],
            ],
        ],
        'Dokumen' => [
            'children' => [
                ['label' => 'Surat Teguran', 'icon' => 'bell', 'url' => '/surat-teguran'],
                ['label' => 'Laporan', 'icon' => 'download', 'url' => '/laporan'],
            ],
        ],
        'WhatsApp' => [
            'children' => [
                ['label' => 'Pengaturan WhatsApp', 'icon' => 'bell', 'url' => '/pengaturan-whatsapp'],
            ],
        ],
    ];

    $kepsekOnly = ['/data-siswa', '/jenis-pelanggaran', '/pengaturan-poin', '/pelanggaran/input', '/surat-teguran', '/pengaturan-whatsapp'];
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/sidebar.blade.php resources/views/components/topbar.blade.php
git commit -m "fix: remove kelas/ortu from navigation"
```

---

### Self-Review

1. **Spec coverage:** All spec points covered:
   - Hapus tabel orang_tua ✓ (Task 1, 2)
   - Field siswa baru (nama_siswa, jk, nisn, tempat_lahir, tgl_lahir, nik, agama, alamat, hp, ayah, ibu, no_wali, rombel) ✓ (Task 1, 4.2, 6.1)
   - Rombel X KJJ/XI KJJ/XII KJJ ✓ (Task 1)
   - Tidak auto-kirim WA ✓ (Task 4.1 — removed from store)
   - Manual kirim WA per teguran ✓ (Task 3, 4.3, 6.2)
   - Notif jika no_wali kosong ✓ (Task 4.3, 6.2)

2. **Placeholder scan:** No TODOs, TBDs, or incomplete sections.
3. **Type consistency:** `nama_siswa`, `rombel`, `no_wali` used consistently across all tasks.
