# Spesifikasi Sistem: Monitoring Kedisiplinan Siswa SMKN 2 Sumbawa Besar

**Tanggal:** 2026-07-11
**Stack:** Laravel 13 + MySQL + Tailwind CSS
**Metode:** Waterfall

---

## 1. Tujuan Sistem

Merancang dan membangun sistem monitoring kedisiplinan siswa berbasis web yang memudahkan Guru BK mencatat pelanggaran, menghitung poin secara otomatis, menerbitkan surat teguran, dan mengirimkannya ke orang tua via WhatsApp.

---

## 2. Aktor & Peran

| Aktor | Peran |
|-------|-------|
| **Guru BK** | Admin penuh: CRUD semua data, input pelanggaran, lihat laporan (merangkap admin) |
| **Kepala Sekolah** | View-only: lihat data pelanggaran, riwayat teguran, cetak laporan |
| **Orang Tua/Wali** | Pasif: terima notifikasi WA berisi PDF surat teguran (tidak login ke sistem) |

---

## 3. Alur Sistem

```
Input pelanggaran → hitung total poin siswa → cek threshold SP → jika ≥ batas,
generate surat teguran (PDF) → kirim WA ke orang tua dengan lampiran PDF
```

- Semua otomatis: input pelanggaran → sistem yang tentukan apakah surat teguran perlu diterbitkan
- Queue database agar pengiriman WA tidak nge-block

---

## 4. Struktur Database

**Aturan penamaan:** Bahasa Indonesia untuk semua tabel & kolom. Kecuali:
- `users` (tabel auth Laravel)
- `id` tetap sebagai primary key
- Tidak ada `created_at` / `updated_at`

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | Auto increment |
| nama | varchar(100) | Nama lengkap |
| email | varchar(100), unique | Username login |
| password | varchar(255) | Bcrypt hash |
| role | enum('guru_bk','kepala_sekolah') | Hak akses |

### Tabel `kelas`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| nama_kelas | varchar(50) | Contoh: XI RPL 1 |
| tingkat | enum('X','XI','XII') | Tingkat kelas |

### Tabel `siswa`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| nisn | varchar(20), unique | Nomor induk siswa nasional |
| nama | varchar(100) | Nama lengkap siswa |
| id_kelas | bigint, FK → kelas.id | |

### Tabel `orang_tua`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| id_siswa | bigint, FK → siswa.id | |
| nama | varchar(100) | Nama orang tua/wali |
| nomor_wa | varchar(20) | Nomor WhatsApp (dengan kode negara) |
| hubungan | enum('ayah','ibu','wali') | Hubungan dengan siswa |

### Tabel `jenis_pelanggaran`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| nama | varchar(100) | Nama pelanggaran (Terlambat, Bolos, dll) |
| poin | integer | Bobot poin untuk pelanggaran ini |

### Tabel `pelanggaran`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| id_siswa | bigint, FK → siswa.id | |
| id_jenis | bigint, FK → jenis_pelanggaran.id | |
| tanggal | date | Tanggal kejadian |
| keterangan | text, nullable | Catatan tambahan |

### Tabel `surat_teguran`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| id_siswa | bigint, FK → siswa.id | |
| tingkat | enum('sp1','sp2','sp3') | Tingkat surat teguran |
| total_poin | integer | Total poin saat teguran diterbitkan |
| file_pdf | varchar(255) | Path file PDF yang tersimpan |
| tanggal_terbit | date | Tanggal teguran diterbitkan |
| status_terkirim | boolean, default false | Status WA terkirim atau belum |

### Tabel `pengaturan_poin`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint, PK | |
| tingkat | enum('sp1','sp2','sp3') | Level teguran |
| batas_poin | integer | Batas poin minimal untuk level ini |

---

## 5. Halaman & Routing

### Guru BK
| Route | Halaman | Fitur |
|-------|---------|-------|
| `/dashboard` | Dashboard | Rekap & grafik pelanggaran |
| `/data-siswa` | Data Siswa | Import Excel + CRUD manual |
| `/data-orang-tua` | Data Orang Tua | Input nomor WA per siswa |
| `/data-kelas` | Data Kelas | CRUD kelas |
| `/jenis-pelanggaran` | Jenis Pelanggaran | CRUD jenis + poin |
| `/pengaturan-poin` | Pengaturan Poin | Atur batas SP1/SP2/SP3 |
| `/pelanggaran/input` | Input Pelanggaran | Catat pelanggaran baru |
| `/pelanggaran` | Riwayat | Filter per siswa/kelas/tanggal |
| `/surat-teguran` | Surat Teguran | Daftar teguran, status kirim |
| `/laporan` | Laporan | Cetak rekap |

### Kepala Sekolah
| Route | Halaman | Fitur |
|-------|---------|-------|
| `/dashboard` | Dashboard | View-only |
| `/pelanggaran` | Riwayat | Lihat semua |
| `/laporan` | Laporan | Lihat & cetak |

> Tiap halaman CRUD menggunakan modal untuk form tambah/edit (vanilla JS), tanpa Livewire.

---

## 6. Tampilan Frontend (TypeUI Dashboard)

Mengacu pada design system TypeUI Dashboard — dark theme, cloud-platform aesthetic.

### Design Tokens
| Token | Value |
|-------|-------|
| Primary | `#0C5CAB` |
| Secondary | `#0a4a8a` |
| Success | `#10b981` |
| Warning | `#f59e0b` |
| Danger | `#ef4444` |
| Surface | `#09090b` |
| Text | `#fafafa` |

### Tipografi
- Font: **IBM Plex Sans** (dari Google Fonts) — semua weight 100-900
- Skala: 12 / 14 / 16 / 20 / 24 / 32 px
- Baseline grid: 8pt

### Komponen
- **Sidebar:** dark glass-like panel, icon + label, active state dengan primary glow
- **Card:** rounded `12px`, background slightly lighter dari surface, subtle border
- **Tabel:** striped rows, header dengan label-caps styling, search + filter bar
- **Button:** rounded `8px`, solid untuk primary, ghost/outline untuk secondary
- **Modal:** centered, backdrop blur, glass panel, animasi fade-in
- **Form input:** dark input field, border subtle, focus state primary ring

---

## 7. Teknologi & Package

| Komponen | Package | Catatan |
|----------|---------|---------|
| Framework | Laravel 13 | PHP 8.4+ |
| Database | MySQL | |
| CSS | Tailwind CSS | Bawaan Laravel 13 |
| Frontend | Blade + Tailwind (TypeUI tokens) | Dark theme, IBM Plex Sans |
| PDF | barryvdh/laravel-dompdf | Blade → PDF |
| WA | kstmostofa/laravel-whatsapp | Node.js sidecar, Puppeteer |
| Queue | Database driver | Laravel queue table |
| Import Excel | maatwebsite/laravel-excel | .xlsx siswa |
| Auth | Laravel Breeze (Blade) | Login minimal |

**Persyaratan WA Library:**
- PHP 8.4+
- Node.js 18+
- ~600 MB disk untuk Chromium

---

## 8. Format Notifikasi WA

Template pesan yang dikirim ke orang tua:

```
Assalamu'alaikum Wr. Wb.

Kepada Yth. Bapak/Ibu {nama_orang_tua}
Orang tua/wali dari {nama_siswa} - Kelas {kelas}

Dengan ini kami sampaikan bahwa putra/putri Bapak/Ibu telah mencapai akumulasi poin pelanggaran sebesar {total_poin} poin dan diterbitkan Surat Teguran {tingkat}.

Untuk informasi lebih lanjut, silakan lihat surat teguran terlampir.

Atas perhatian dan kerja samanya, kami ucapkan terima kasih.

Wassalamu'alaikum Wr. Wb.
SMK Negeri 2 Sumbawa Besar
```

File PDF surat teguran dilampirkan sebagai dokumen.

---

## 9. Catatan Implementasi

- Poin pelanggaran bersifat akumulatif (tidak di-reset per semester — sesuai aturan sekolah)
- Surat teguran diterbitkan otomatis ketika total poin siswa mencapai/melampaui `batas_poin` untuk suatu tingkat
- Satu siswa hanya memiliki 1 surat teguran aktif per tingkat (tidak duplikat SP1 jika sudah terbit SP1, tunggu SP2)
- WA dikirim ke SEMUA orang tua yang terdaftar untuk siswa tersebut (ayah + ibu jika keduanya ada)
- Queue job untuk pengiriman WA agar tidak blocking request
