# Refactor Sistem Monitoring Kedisiplinan Siswa SMKN 2 Sumbawa Besar

## 1. Tujuan

Menyederhanakan struktur data dan alur notifikasi berdasarkan masukan pengguna.

## 2. Perubahan Database

### 2.1 Tabel `siswa` — diubah

**Migration `update_siswa_table`:**

| Kolom Lama | Kolom Baru | Tipe | Keterangan |
|---|---|---|---|
| `nama` | `nama_siswa` | string(100) | |
| `id_kelas` | _(hapus)_ | — | foreign key dihapus |
| — | `jk` | enum('L','P') | |
| `nisn` | `nisn` | string(20) unique | tetap |
| — | `tempat_lahir` | string(50) | |
| — | `tgl_lahir` | date | |
| — | `nik` | string(20) | NIK |
| — | `agama` | string(20) | |
| — | `alamat` | text | |
| — | `hp` | string(20) nullable | |
| — | `ayah` | string(100) nullable | |
| — | `ibu` | string(100) nullable | |
| — | `no_wali` | string(20) nullable | nomor WA wali |
| — | `rombel` | enum('X KJJ','XI KJJ','XII KJJ') | |

### 2.2 Tabel `orang_tua` — dihapus

**Migration `remove_orang_tua_kelas_tables`:**
- Drop tabel `orang_tua`
- Drop tabel `kelas`

## 3. Perubahan Model

### 3.1 `Siswa` — diubah
- Hapus relasi `kelas()`, `orangTua()`
- Hapus `$timestamps = false` — tambah timestamps
- Hapus trait `HasFactory`
- Ubah `$fillable` sesuai field baru
- Tambah accessor `rombel_label`

### 3.2 Dihapus
- Model `Kelas`
- Model `OrangTua`

### 3.3 Model Lain — tidak berubah
- `JenisPelanggaran`
- `PengaturanPoin`
- `SuratTeguran`
- `User`

## 4. Perubahan Controller

### 4.1 `PelanggaranController`
- `store()` — hapus panggilan `cekDanTerbitkanTeguran()`, simpan pelanggaran saja
- `riwayat()` — hapus filter `id_kelas`
- Hapus method `cekDanTerbitkanTeguran()`
- Hapus `use App\Models\PengaturanPoin;`
- Hapus `use App\Models\SuratTeguran;`

### 4.2 `SuratTeguranController`
- `index()` — ubah `$teguran` agar `status_terkirim` + `status_hp` ikut: tampilkan siswa yang belum punya `no_wali`
- Tambah `send($id)` — kirim WA manual untuk satu surat teguran, validasi `no_wali` dulu
- Hapus `use Illuminate\Http\Request` jika tidak perlu

### 4.3 `SiswaController`
- Ubah `store()` / `update()` — validasi sesuai field baru
- Ubah `index()` — hapus `Kelas` model, pakai `rombel` langsung

### 4.4 `Select2Controller`
- `siswa()` — ubah format text jadi `"{nama_siswa} ({nisn}) - {rombel}"`

### 4.5 `ApiDashboardController`
- Ubah query yang sebelumnya join ke `kelas` — pakai field `rombel`

### 4.6 Controller lain
- `KelasController` — **hapus** (tabel kelas dihapus)
- `OrangTuaController` — **hapus**
- `LaporanController` — sesuaikan jika ada dependensi ke `kelas`
- `WhatsappSettingController` — tidak berubah
- `DashboardController` — tidak berubah
- ProfileController — tidak berubah
- `JenisPelanggaranController` — tidak berubah
- `PengaturanPoinController` — tidak berubah

## 5. Perubahan Job

### 5.1 `KirimWaTeguran`
- `handle()` — ambil `no_wali` dari `$siswa->no_wali`, bukan dari `$siswa->orangTua`
- Ubah template teks: `{nama}` diganti jadi nama ayah/ibu atau fallback
- Hapus loop `foreach ($siswa->orangTua as $ortu)` — kirim ke satu nomor wali
- Jika `$siswa->no_wali` kosong, log error & return

## 6. Perubahan Routes

### 6.1 Hapus route
- Semua route `/data-kelas/*`
- Semua route `/data-orang-tua/*`
- Route `POST /select2/jenis` → tidak berubah

### 6.2 Tambah route
- `POST /surat-teguran/{id}/kirim-wa` → kirim manual

Di halaman siswa (halaman index atau detail) — route baru untuk kirim WA teguran per siswa:
- `POST /siswa/{id}/kirim-wa` → kirim semua teguran yang belum terkirim

### 6.3 Route berubah
- Route Select2 siswa — nama route tetap, output berubah (format text)

## 7. Perubahan View

### 7.1 Halaman yang dihapus
- `kelas/index.blade.php`
- `orang-tua/index.blade.php`

### 7.2 `surat-teguran/index.blade.php`
- Kolom "Status WA" diubah jadi: jika `no_wali` kosong → tampilkan "No WA tidak ada" + link untuk isi
- Tambah kolom tombol "Kirim WA" per baris (hanya jika `status_terkirim` = false dan `no_wali` ada)
- Modal/konfirmasi sebelum kirim

### 7.3 `siswa/index.blade.php`
- Ubah form CRUD sesuai field baru
- Tambah tab/kolom "Aksi" → tombol "Kirim WA" jika siswa punya teguran belum terkirim

### 7.4 `pelanggaran/index.blade.php` (riwayat)
- Hapus filter `id_kelas`
- Sesuaikan kolom tampilan kelas jadi `rombel`

### 7.5 `dashboard/index.blade.php`
- Tidak berubah (data via API, API diubah di langkah 4.5)

### 7.6 Form input pelanggaran (`pelanggaran/create.blade.php`)
- Tidak berubah (Select2 tetap)

### 7.7 `laporan/index.blade.php`
- Hapus filter kelas jika ada
- Sesuaikan output

### 7.8 `pengaturan-poin/index.blade.php`
- Tidak berubah

### 7.9 `pengaturan-whatsapp/index.blade.php`
- Tidak berubah

## 8. Alur Manual Kirim WA

1. Guru BK buka halaman Surat Teguran
2. Lihat daftar teguran, status WA, dan status `no_wali`
3. Klik "Kirim WA" per baris teguran
4. Backend validasi: jika `siswa->no_wali` kosong → return error "Nomor WA wali belum diisi. Silakan isi di halaman siswa."
5. Jika `siswa->no_wali` ada → dispatch `KirimWaTeguran` job → return sukses
6. Tombol yang sama di halaman siswa untuk kirim semua teguran sekaligus (batch)

## 9. Yang Tidak Berubah

- Auth (Breeze)
- Role: guru_bk, kepala_sekolah
- Dashboard layout + navigasi sidebar (navbar link ke kelas & orang tua dihapus)
- WhatsApp integration (QR scan, start/stop)
- Export laporan PDF
- Generate surat teguran PDF
- Pengaturan poin batas SP1/SP2/SP3
