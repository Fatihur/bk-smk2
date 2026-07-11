# WhatsApp Settings Page — Design Spec

**Tanggal:** 2026-07-11
**Stack:** Laravel 13 + Blade + Alpine.js + Tailwind (TypeUI Dark)
**Backend WA:** Web Sidecar (whatsapp-web.js via `kstmostofa/laravel-whatsapp`)

---

## 1. Tujuan

Menyediakan halaman di dashboard untuk mengelola koneksi WhatsApp Web Sidecar — mulai pairing via QR code, lihat status koneksi, stop/destroy session, dan restart sidecar — tanpa harus akses terminal.

---

## 2. Backend

### Controller: `WhatsappSettingController`

Middleware: `auth`, `role:guru_bk`

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| `index()` | `GET /pengaturan-whatsapp` | Tampilkan halaman |
| `status()` | `GET /api/whatsapp/status` | Status sidecar + session |
| `start()` | `POST /api/whatsapp/start` | Mulai pairing, return QR |
| `stop()` | `POST /api/whatsapp/stop` | Stop session (auth tetap) |
| `destroy()` | `POST /api/whatsapp/destroy` | Hapus auth, butuh pairing ulang |

### Route

```php
// routes/web.php
Route::middleware(['auth', 'role:guru_bk'])->group(function () {
    Route::get('/pengaturan-whatsapp', [WhatsappSettingController::class, 'index']);
});

// routes/api.php
Route::middleware(['auth', 'role:guru_bk'])->prefix('whatsapp')->group(function () {
    Route::get('/status', [WhatsappSettingController::class, 'status']);
    Route::post('/start', [WhatsappSettingController::class, 'start']);
    Route::post('/stop', [WhatsappSettingController::class, 'stop']);
    Route::post('/destroy', [WhatsappSettingController::class, 'destroy']);
});
```

### API Response Format

**GET /api/whatsapp/status**
```json
{
  "sidecar": {
    "installed": true,
    "running": true
  },
  "session": {
    "id": "smkn2_monitoring",
    "status": "qr",
    "qr": "data:image/png;base64,...",
    "phone_number": null,
    "push_name": null,
    "ready_at": null
  }
}
```

Status values: `initializing`, `qr`, `authenticated`, `ready`, `disconnected`, `auth_failure`, `error`

**POST /api/whatsapp/start** — return `{success: true, session: {...}}` dengan QR langsung.
**POST /api/whatsapp/stop** — return `{success: true}`.
**POST /api/whatsapp/destroy** — return `{success: true}`.

### Package API yang dipakai

Menggunakan `WhatsApp::web($sessionId)` yang return `WebSession`:

| Method | Untuk |
|--------|-------|
| `state()` | Cek status session |
| `qr()` | Ambil QR data URI |
| `start()` | Mulai pairing |
| `stop()` | Stop session |
| `destroy()` | Hapus auth |
| `info()` | Info lengkap (phone, push name) |

### Fix KirimWaTeguran Job

Ganti method call yang tidak ada:

```php
// Sebelum (RUSAK):
WhatsApp::sendDocument($nomor, $file, $filename);
WhatsApp::sendMessage($nomor, $pesan);

// Sesudah:
WhatsApp::web('smkn2_monitoring')->messages()->sendDocument($nomor, [
    'url' => Storage::url($filePath),
    'filename' => "Surat_Teguran_{$this->tingkat}.pdf"
]);
WhatsApp::web('smkn2_monitoring')->messages()->sendText($nomor, $pesan);
```

---

## 3. Frontend

### View: `resources/views/pengaturan-whatsapp/index.blade.php`

Satu halaman dengan layout `layouts.app`, stack Blade + Alpine.js.

### Layout (4 card)

```
┌───────────────────────────────────────────────┐
│  Pengaturan WhatsApp                           │
│  Kelola koneksi WhatsApp untuk notifikasi      │
│                                                │
│  ┌──────────────┐  ┌──────────────────────┐   │
│  │ Status Sidecar │  │ Status Session       │   │
│  │ ● Running      │  │ ● Connected          │   │
│  └──────────────┘  └──────────────────────┘   │
│                                                │
│  ┌──────────────────────────────────────┐      │
│  │           QR CODE                     │      │
│  │        [image 250x250]                │      │
│  │   Scan dengan WhatsApp >              │      │
│  │   Linked Devices > Scan QR            │      │
│  └──────────────────────────────────────┘      │
│                                                │
│  [Mulai Pairing] [Stop] [Hapus Session]        │
│                                                │
│  ┌──────────────────────────────────────┐      │
│  │ Info Session                          │      │
│  │ Nomor     : 628123456789              │      │
│  │ Nama      : SMKN 2 Sumbawa            │      │
│  │ Status    : Ready                     │      │
│  │ Terhubung : 11 Jul 2026 14:30         │      │
│  └──────────────────────────────────────┘      │
└───────────────────────────────────────────────┘
```

### Alur Interaksi (Alpine.js)

- `x-data="{ session: null, sidecar: null, loading: true, polling: null }"`
- `init()` → fetch status → start polling tiap 3s
- Status machine:
  - `initializing`: spinner, "Memulai session..."
  - `qr`: tampilkan QR image + instruksi scan
  - `authenticated`/`ready`: sembunyikan QR, tampilkan info + tombol Stop/Destroy
  - `disconnected`/`auth_failure`/`error`: tampilkan pesan error + tombol Start
- Polling berhenti saat status `ready` (tidak perlu update terus)
- Tombol Start → POST `/api/whatsapp/start`, polling lanjut untuk update QR
- Tombol Stop → POST `/api/whatsapp/stop`, polling cek status
- Tombol Destroy → konfirmasi dulu, POST `/api/whatsapp/destroy`

### Integrasi Sidebar

Tambah menu di sidebar (guru_bk):
```php
['label' => 'Pengaturan WA', 'url' => '/pengaturan-whatsapp', 'icon' => '💬'],
```

---

## 4. Konfigurasi Environment

Tambahkan ke `.env`:
```
WHATSAPP_WEB_ENABLED=true
WHATSAPP_SESSION_NAME=smkn2_monitoring
```

Dan di `config/laravel-whatsapp.php` sesuaikan middleware admin UI jika perlu.

---

## 5. Perintah Terminal (sekali setup)

```bash
# Install Node dependencies sidecar
php artisan whatsapp:sidecar:install

# Start sidecar process
php artisan whatsapp:sidecar:start

# Start web listener (daemon)
php artisan whatsapp:web:listen &
```

Perintah-perintah ini bisa dijalankan dari halaman setting nantinya (future).

---

## 6. Catatan

- Session WA tetap tersimpan selama tidak di-destroy (pairing cukup sekali)
- Sidecar harus jalan agar WA bisa kirim pesan
- Halaman hanya untuk role `guru_bk` — Kepala Sekolah tidak perlu akses
- Tampilan mengikuti TypeUI dark theme yang sudah ada
