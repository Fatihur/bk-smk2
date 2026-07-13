# Redesign Halaman Riwayat Pelanggaran — Design Doc

> Integrasi surat teguran ke halaman riwayat pelanggaran dengan tampilan group per-siswa.

## Tujuan

- Hapus menu/halaman Surat Teguran terpisah
- Tampilkan data riwayat pelanggaran per-siswa (satu baris per siswa, bukan per pelanggaran)
- Tampilkan Surat Teguran (SP) yang terbit di dalam accordion siswa
- Auto-kirim WA ketika SP baru terbit
- Tetap ada tombol manual kirim WA

## Perubahan Data / Query

**PelanggaranController::riwayat():**
- Query dari `Siswa` (bukan `Pelanggaran`), eager load:
  - `pelanggaran` → dengan `jenis` (untuk nama & poin)
  - `suratTeguran` (untuk status SP & WA)
- Pagination per-siswa: 50 siswa/halaman
- Filter siswa (by nama) dan range tanggal tetap via `whereHas('pelanggaran', ...)`
- `total_poin` = sum `jenis_pelanggaran.poin` dari semua pelanggaran siswa tsb
- `jumlah_pelanggaran` = count pelanggaran

**Filter:**
- Dropdown siswa tetap (tidak berubah)
- Input date range tetap
- Filter diterapkan di `whereHas('pelanggaran')` bukan di model Pelanggaran langsung

## UI / Tampilan

### Tabel Ringkasan (per-siswa)

| Nama Siswa | Rombel | Total Poin | Jml Pelanggaran | Status SP |
|------------|--------|------------|-----------------|-----------|
| ➤ Farel Anugrah Adha | XI KJJ | 70 | 3 | SP2 |
| ➤ ... | ... | ... | ... | ... |

- Sorting default: total poin descending
- Warna status SP: SP1 = biru, SP2 = kuning, SP3 = merah
- Status SP: tidak tampil jika belum ada SP

### Accordion (expand per siswa)

Klik baris → expand slide-down:

```
┌─ Detail Pelanggaran ──────────────────────┐
│ Tanggal    │ Jenis         │ Poin │ Ket    │
│ 13/07/2026 │ Terlambat     │ 25    │ -      │
│ 10/07/2026 │ Bolos         │ 30    │ -      │
│ ...        │ ...           │ ...   │ ...    │
├─ Surat Teguran ────────────────────────────┤
│ SP1  [Lihat PDF]  ✅ Terkirim             │
│ SP2  [Lihat PDF]  📤 Kirim WA             │
└────────────────────────────────────────────┘
```

**Tombol kirim WA:** hanya muncul jika `status_terkirim = false`. Konfirmasi dulu via `confirm()`.

### DataTable / Fitur Interaktif

Implementasi pakai **accordion vanilla JS** — tanpa Alpine/Livewire. Cukup class `.accordion-item` dengan toggle click.

## Auto-Kirim WA

Di `PelanggaranController::cekDanTerbitkanTeguran()`, setelah `SuratTeguran::create()`:

```php
SuratTeguran::create([...]);

// Auto dispatch WA
if ($siswa->no_wali) {
    dispatch(new KirimWaTeguran($idSiswa, $p->tingkat, $filename));
}
```

WA terkirim otomatis tanpa klik manual. Gagal? Tombol kirim manual di accordion tetap bisa dipakai.

## File Affected

| File | Aksi |
|------|------|
| `app/Http/Controllers/PelanggaranController.php` | Ubah method `riwayat()` → query group per siswa. Tambah dispatch WA di `cekDanTerbitkanTeguran()`. |
| `app/Http/Controllers/SuratTeguranController.php` | Hapus method `index()` dan view-nya. Simpan `show()` (PDF) dan `kirimWa()`. |
| `resources/views/pelanggaran/index.blade.php` | Rewrite total → accordion per-siswa dengan detail + SP. |
| `resources/views/surat-teguran/index.blade.php` | Hapus. |
| `routes/web.php` | Hapus `GET /surat-teguran` (index). Simpan `GET /surat-teguran/{id}` (show PDF) dan `POST /surat-teguran/{id}/kirim-wa`. |
| `resources/views/components/sidebar.blade.php` | Hapus link "Surat Teguran". |
| `resources/views/components/topbar.blade.php` | Hapus link "Surat Teguran" dari daftar menu. |

## Tidak Berubah

- `SuratTeguranController::show()` — tetap serving PDF
- `SuratTeguranController::kirimWa()` — tetap dispatch job
- `KirimWaTeguran` job — tidak berubah
- `FonnteService` — tidak berubah
- Database schema — tidak ada migrasi baru
