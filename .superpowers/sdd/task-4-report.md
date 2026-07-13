# Task 4: Update Controllers — Report

## Status: DONE

## Changes Made

| File | Action |
|------|--------|
| `PelanggaranController.php` | Rewritten — removed `cekDanTerbitkanTeguran()`, `id_kelas` filter, unused imports; changed `siswa.kelas` → `siswa`, `nama` → `nama_siswa` |
| `SiswaController.php` | Rewritten — removed `Kelas` import/relation; new validation fields (`nama_siswa`, `jk`, `nisn`, `tempat_lahir`, `tgl_lahir`, `nik`, `agama`, `alamat`, `hp`, `ayah`, `ibu`, `no_wali`, `rombel`); import by `rombel` instead of `nama_kelas` |
| `SuratTeguranController.php` | Rewritten — added `kirimWa()` method; changed `siswa.kelas` → `siswa`; removed `Request` import |
| `Select2Controller.php` | Updated `siswa()` — removed `with('kelas')`, uses `nama_siswa` + `rombel` directly |
| `ApiDashboardController.php` | Rewritten — changed `siswa.kelas` → `siswa`, `nama` → `nama_siswa`, `kelas` key now returns `rombel` |
| `LaporanController.php` | Rewritten — removed `with('kelas')`, `with(['siswa.kelas'])` → `with(['siswa'])`, `nama` → `nama_siswa` |
| `KelasController.php` | **Deleted** |
| `OrangTuaController.php` | **Deleted** |

## Verification
- `php -l` — no syntax errors in any modified file
- `php artisan route:list` — all routes resolve correctly, no missing controller errors
- `kelas` and `orang-tua` routes already removed from `routes/web.php` in prior tasks

## Commit
`5f75214` — `feat: update controllers - remove kelas/ortu, manual wa dispatch`
