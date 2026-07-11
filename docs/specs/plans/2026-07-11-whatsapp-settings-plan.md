# WhatsApp Settings Page — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a WhatsApp settings page to the dashboard with QR pairing, session management, and fix the existing WA job.

**Architecture:** New controller + API endpoints + one Blade/Alpine.js view. Uses `WhatsApp::web()` from `kstmostofa/laravel-whatsapp` package to manage Web Sidecar sessions. Fix job `KirimWaTeguran` to use correct API methods.

**Tech Stack:** Laravel 13, Blade, Alpine.js, Tailwind CSS (TypeUI dark), kstmostofa/laravel-whatsapp

## Global Constraints

- Hanya role `guru_bk` yang bisa akses halaman ini
- Session name: `smkn2_monitoring` (from .env `WHATSAPP_SESSION_NAME`)
- Bahasa Indonesia untuk semua label & pesan di UI
- Dark theme TypeUI (warna surface, primary, dll)
- Sidecar harus running agar WhatsApp bisa kirim pesan

---

### Task 1: Fix Existing WA Job + Enable Sidecar

**Files:**
- Modify: `app/Jobs/KirimWaTeguran.php`
- Modify: `.env`
- Modify: `config/laravel-whatsapp.php` (opsional)

- [ ] **Step 1: Fix KirimWaTeguran job**

In `app/Jobs/KirimWaTeguran.php`, ganti method call yang tidak ada:

```php
<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Siswa;
use App\Models\SuratTeguran;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;
use Illuminate\Support\Facades\Storage;

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
        $siswa = Siswa::with(['kelas', 'orangTua'])->find($this->idSiswa);
        if (!$siswa) return;

        $surat = SuratTeguran::where('id_siswa', $this->idSiswa)
            ->where('tingkat', $this->tingkat)
            ->latest('id')
            ->first();

        $filePath = storage_path('app/public/teguran/' . $this->filename);

        $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
            . "Kepada Yth. Bapak/Ibu {nama}\n"
            . "Orang tua/wali dari {$siswa->nama} - Kelas {$siswa->kelas->nama_kelas}\n\n"
            . "Dengan ini kami sampaikan bahwa putra/putri Bapak/Ibu telah mencapai "
            . "akumulasi poin pelanggaran sebesar {$surat->total_poin} poin "
            . "dan diterbitkan Surat Teguran " . strtoupper($this->tingkat) . ".\n\n"
            . "Untuk informasi lebih lanjut, silakan lihat surat teguran terlampir.\n\n"
            . "Atas perhatian dan kerja samanya, kami ucapkan terima kasih.\n\n"
            . "Wassalamu'alaikum Wr. Wb.\n"
            . "SMK Negeri 2 Sumbawa Besar";

        foreach ($siswa->orangTua as $ortu) {
            try {
                WhatsApp::web('smkn2_monitoring')->messages()->sendDocument($ortu->nomor_wa, [
                    'url' => Storage::url('teguran/' . $this->filename),
                    'filename' => "Surat_Teguran_{$this->tingkat}.pdf"
                ]);
                WhatsApp::web('smkn2_monitoring')->messages()->sendText($ortu->nomor_wa, str_replace('{nama}', $ortu->nama, $pesan));
            } catch (\Exception $e) {
                \Log::error("WA send failed for {$ortu->nomor_wa}: " . $e->getMessage());
            }
        }

        if ($surat) {
            $surat->update(['status_terkirim' => true]);
        }
    }
}
```

- [ ] **Step 2: Enable sidecar in `.env`**

Tambah/edit di `.env`:
```
WHATSAPP_WEB_ENABLED=true
WHATSAPP_SESSION_NAME=smkn2_monitoring
```

- [ ] **Step 3: Commit**

```bash
git add .
git commit -m "fix: update KirimWaTeguran job to use valid API, enable web sidecar"
```

---

### Task 2: Create Controller + Routes

**Files:**
- Create: `app/Http/Controllers/WhatsappSettingController.php`
- Modify: `routes/web.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Create WhatsappSettingController**

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kstmostofa\LaravelWhatsApp\Facades\WhatsApp;
use Kstmostofa\LaravelWhatsApp\Commands\SidecarStatusCommand;

class WhatsappSettingController extends Controller
{
    protected string $sessionId;

    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
        $this->sessionId = config('laravel-whatsapp.sidecar.default_session', 'smkn2_monitoring');
    }

    public function index()
    {
        return view('pengaturan-whatsapp.index');
    }

    public function status()
    {
        $sidecarRunning = false;
        $sidecarInstalled = false;
        try {
            // ponytail: simple HTTP check to see if sidecar is reachable
            $web = WhatsApp::web($this->sessionId);
            $state = $web->state();
            $sidecarRunning = true;
            $sidecarInstalled = true;
        } catch (\Exception $e) {
            // sidecar not reachable or not installed
        }

        $session = null;
        if ($sidecarRunning) {
            try {
                $web = WhatsApp::web($this->sessionId);
                $state = $web->state();
                $info = null;
                $qr = null;
                if ($state === 'qr') {
                    $qrData = $web->qr();
                    $qr = $qrData['qr'] ?? null;
                }
                if (in_array($state, ['authenticated', 'ready'])) {
                    $info = $web->info();
                }

                $session = [
                    'id' => $this->sessionId,
                    'status' => $state,
                    'qr' => $qr,
                    'phone_number' => $info['phone_number'] ?? $info['me']['user'] ?? null,
                    'push_name' => $info['push_name'] ?? $info['me']['name'] ?? null,
                    'ready_at' => null,
                ];
            } catch (\Exception $e) {
                $session = [
                    'id' => $this->sessionId,
                    'status' => 'error',
                    'qr' => null,
                    'phone_number' => null,
                    'push_name' => null,
                    'ready_at' => null,
                ];
            }
        }

        return response()->json([
            'sidecar' => [
                'installed' => $sidecarInstalled,
                'running' => $sidecarRunning,
            ],
            'session' => $session,
        ]);
    }

    public function start()
    {
        try {
            $web = WhatsApp::web($this->sessionId);
            $result = $web->start();

            // after start, get QR
            $state = $web->state();
            $qr = null;
            if ($state === 'qr') {
                $qrData = $web->qr();
                $qr = $qrData['qr'] ?? null;
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $this->sessionId,
                    'status' => $state,
                    'qr' => $qr,
                    'phone_number' => null,
                    'push_name' => null,
                    'ready_at' => null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function stop()
    {
        try {
            WhatsApp::web($this->sessionId)->stop();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan session: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy()
    {
        try {
            WhatsApp::web($this->sessionId)->destroy();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus session: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

- [ ] **Step 2: Register web route**

In `routes/web.php`, tambahkan di dalam group middleware `auth` + `role:guru_bk`:

```php
Route::get('/pengaturan-whatsapp', [WhatsappSettingController::class, 'index'])->name('whatsapp.settings');
```

Jangan lupa tambah `use App\Http\Controllers\WhatsappSettingController;` di bagian atas.

- [ ] **Step 3: Register API routes**

In `routes/api.php`, tambahkan:

```php
use App\Http\Controllers\WhatsappSettingController;

Route::middleware(['auth', 'role:guru_bk'])->prefix('whatsapp')->group(function () {
    Route::get('/status', [WhatsappSettingController::class, 'status']);
    Route::post('/start', [WhatsappSettingController::class, 'start']);
    Route::post('/stop', [WhatsappSettingController::class, 'stop']);
    Route::post('/destroy', [WhatsappSettingController::class, 'destroy']);
});
```

- [ ] **Step 4: Commit**

```bash
git add .
git commit -m "feat: add WhatsappSettingController and API routes"
```

---

### Task 3: Create Frontend View + Sidebar Menu

**Files:**
- Create: `resources/views/pengaturan-whatsapp/index.blade.php`
- Modify: `resources/views/components/sidebar.blade.php`

- [ ] **Step 1: Create view**

`resources/views/pengaturan-whatsapp/index.blade.php`:

```blade
@extends('layouts.app')
@section('title', 'Pengaturan WhatsApp')

@section('content')
<div x-data="whatsappSettings()" x-init="init()" class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-semibold text-white">Pengaturan WhatsApp</h2>
            <p class="text-sm text-zinc-400 mt-1">Kelola koneksi WhatsApp untuk notifikasi surat teguran</p>
        </div>
        <span x-show="loading" class="text-sm text-zinc-500">Memuat...</span>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
            <p class="text-xs text-zinc-400 uppercase tracking-wider mb-2">Status Sidecar</p>
            <div class="flex items-center gap-2">
                <span x-show="sidecar?.running" class="w-2 h-2 rounded-full bg-green-500"></span>
                <span x-show="!sidecar?.running" class="w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-white text-sm" x-text="sidecar?.running ? 'Running' : 'Offline'"></span>
            </div>
            <p class="text-xs text-zinc-500 mt-1" x-show="!sidecar?.running">Jalankan: php artisan whatsapp:sidecar:start</p>
        </div>

        <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
            <p class="text-xs text-zinc-400 uppercase tracking-wider mb-2">Status Session</p>
            <div class="flex items-center gap-2">
                <span x-show="session?.status === 'ready'" class="w-2 h-2 rounded-full bg-green-500"></span>
                <span x-show="session?.status === 'qr' || session?.status === 'initializing'" class="w-2 h-2 rounded-full bg-yellow-500"></span>
                <span x-show="session?.status === 'disconnected' || session?.status === 'auth_failure' || session?.status === 'error'" class="w-2 h-2 rounded-full bg-red-500"></span>
                <span class="text-white text-sm font-medium" x-text="labelStatus(session?.status)"></span>
            </div>
        </div>
    </div>

    {{-- QR Code Area --}}
    <div x-show="session?.status === 'qr'" class="bg-surface-light rounded-card border border-zinc-800 p-6">
        <div class="flex flex-col items-center">
            <p class="text-sm text-zinc-400 mb-4">Scan QR ini dengan WhatsApp Anda</p>
            <div class="bg-white p-3 rounded-lg mb-4">
                <img :src="session.qr" alt="QR Code" class="w-64 h-64">
            </div>
            <div class="text-xs text-zinc-500 text-center space-y-1">
                <p>1. Buka WhatsApp di HP</p>
                <p>2. Tap titik tiga (menu) > Perangkat Tertaut</p>
                <p>3. Tap "Tautkan Perangkat"</p>
                <p>4. Arahkan kamera ke QR ini</p>
            </div>
        </div>
    </div>

    {{-- Ready / Connected Info --}}
    <div x-show="session?.status === 'ready' || session?.status === 'authenticated'" class="bg-surface-light rounded-card border border-zinc-800 p-6">
        <h3 class="text-sm font-medium text-white mb-4">Info Session</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-zinc-400">Session ID:</span>
                <span class="text-white ml-2" x-text="session?.id"></span>
            </div>
            <div>
                <span class="text-zinc-400">Nomor:</span>
                <span class="text-white ml-2" x-text="session?.phone_number || '-'"></span>
            </div>
            <div>
                <span class="text-zinc-400">Nama:</span>
                <span class="text-white ml-2" x-text="session?.push_name || '-'"></span>
            </div>
            <div>
                <span class="text-zinc-400">Status:</span>
                <span class="text-green-400 ml-2">Tersambung</span>
            </div>
        </div>
    </div>

    {{-- Initializing --}}
    <div x-show="session?.status === 'initializing'" class="bg-surface-light rounded-card border border-zinc-800 p-6 text-center">
        <p class="text-sm text-zinc-400">Memulai session...</p>
        <div class="mt-2 inline-block w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    {{-- Disconnected / Error --}}
    <div x-show="session?.status === 'disconnected' || session?.status === 'auth_failure' || session?.status === 'error'" class="bg-surface-light rounded-card border border-zinc-800 p-6">
        <p class="text-sm text-red-400">
            <span x-text="session?.status === 'disconnected' ? 'Session terputus.' : (session?.status === 'auth_failure' ? 'Gagal autentikasi. Pairing ulang.' : 'Terjadi kesalahan.')"></span>
        </p>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3 flex-wrap" x-show="sidecar?.running">
        <button x-show="!session?.status || session?.status === 'disconnected' || session?.status === 'auth_failure' || session?.status === 'error'"
                @click="startPairing"
                class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-5 py-2 rounded-btn">
            Mulai Pairing
        </button>

        <button x-show="session?.status === 'ready' || session?.status === 'authenticated'"
                @click="stopSession"
                class="border border-zinc-700 text-zinc-300 hover:text-white text-sm px-5 py-2 rounded-btn">
            Stop Session
        </button>

        <button x-show="session?.status === 'ready' || session?.status === 'authenticated'"
                @click="destroySession"
                class="border border-red-800 text-red-400 hover:text-red-300 text-sm px-5 py-2 rounded-btn">
            Hapus Session
        </button>
    </div>

    {{-- Sidecar offline notice --}}
    <div x-show="!sidecar?.running" class="bg-surface-light rounded-card border border-zinc-800 p-6">
        <p class="text-sm text-yellow-400">Sidecar tidak berjalan.</p>
        <p class="text-xs text-zinc-500 mt-2">Jalankan di terminal: <code class="text-primary-400">php artisan whatsapp:sidecar:start</code></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function whatsappSettings() {
    return {
        sidecar: null,
        session: null,
        loading: true,
        pollingTimer: null,

        init() {
            this.fetchStatus();
        },

        fetchStatus() {
            fetch('/api/whatsapp/status')
                .then(r => r.json())
                .then(d => {
                    this.sidecar = d.sidecar;
                    this.session = d.session;
                    this.loading = false;

                    if (this.pollingTimer) clearTimeout(this.pollingTimer);

                    // terus polling selama masih initializing atau qr
                    if (this.session?.status === 'initializing' || this.session?.status === 'qr') {
                        this.pollingTimer = setTimeout(() => this.fetchStatus(), 3000);
                    }
                })
                .catch(() => {
                    this.loading = false;
                });
        },

        startPairing() {
            this.loading = true;
            fetch('/api/whatsapp/start', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        this.session = d.session;
                        // polling for QR / status updates
                        this.pollingTimer = setTimeout(() => this.fetchStatus(), 2000);
                    }
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
        },

        stopSession() {
            if (!confirm('Hentikan session? Autentikasi tetap tersimpan.')) return;
            fetch('/api/whatsapp/stop', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(r => r.json())
                .then(() => this.fetchStatus());
        },

        destroySession() {
            if (!confirm('Hapus session? Anda harus pairing ulang nantinya.')) return;
            fetch('/api/whatsapp/destroy', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                .then(r => r.json())
                .then(() => this.fetchStatus());
        },

        labelStatus(status) {
            const labels = {
                'initializing': 'Initializing...',
                'qr': 'Menunggu Scan QR',
                'authenticated': 'Terautentikasi',
                'ready': 'Tersambung',
                'disconnected': 'Terputus',
                'auth_failure': 'Gagal Autentikasi',
                'error': 'Error',
            };
            return labels[status] || status || '-';
        }
    };
}
</script>
@endpush
```

- [ ] **Step 2: Update sidebar menu**

Di `resources/views/components/sidebar.blade.php`, tambahkan item menu untuk role `guru_bk`:

Cari array `$menuGuruBk` (atau loop menu untuk guru_bk), tambahkan sebelum Logout:

```php
['label' => 'Pengaturan WA', 'url' => '/pengaturan-whatsapp', 'icon' => '💬'],
```

- [ ] **Step 3: Commit**

```bash
git add .
git commit -m "feat: add WhatsApp settings page with QR pairing UI"
```

---

### Task 4: Final Integration

- [ ] **Step 1: Install sidecar dependencies**

```bash
php artisan whatsapp:sidecar:install
```

- [ ] **Step 2: Start sidecar**

```bash
php artisan whatsapp:sidecar:start
```

- [ ] **Step 3: Start web listener (background)**

```bash
php artisan whatsapp:web:listen &
```

- [ ] **Step 4: Verify routes**

```bash
php artisan route:list | findstr whatsapp
```

Expected: show `/pengaturan-whatsapp`, `/api/whatsapp/status`, `/api/whatsapp/start`, `/api/whatsapp/stop`, `/api/whatsapp/destroy`

- [ ] **Step 5: Manual smoke test**

- Login as Guru BK
- Buka menu "Pengaturan WA" di sidebar
- Verifikasi sidecar status = Running
- Klik "Mulai Pairing" → QR muncul
- Scan QR dengan HP (WhatsApp > Linked Devices)
- Verifikasi status berubah jadi "Tersambung"
- Klik "Stop Session" → session stop
- Klik "Hapus Session" → auth terhapus

- [ ] **Step 6: Commit**

```bash
git add .
git commit -m "feat: final integration WhatsApp settings"
```
