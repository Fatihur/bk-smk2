# Task 2 Report

## STATUS
✅ Complete — all 7 migrations and 7 models created and verified.

## COMMITS
- `2026_07_11_000001_create_kelas_table.php` — Migration + Kelas model
- `2026_07_11_000002_create_siswa_table.php` — Migration + Siswa model
- `2026_07_11_000003_create_orang_tua_table.php` — Migration + OrangTua model
- `2026_07_11_000004_create_jenis_pelanggaran_table.php` — Migration + JenisPelanggaran model
- `2026_07_11_000005_create_pelanggaran_table.php` — Migration + Pelanggaran model
- `2026_07_11_000006_create_surat_teguran_table.php` — Migration + SuratTeguran model
- `2026_07_11_000007_create_pengaturan_poin_table.php` — Migration + PengaturanPoin model

## TESTS
- `php artisan migrate --pretend` — all SQL output verified correct
- `php artisan migrate` — all 7 migrations executed without errors
- `php -l` — all 14 files pass syntax check

## CONCERNS
- None. All FKs reference correct tables/columns, cascadeOnDelete applied where specified, `$timestamps = false` set on all models, FK `id_kelas` named per brief (not `kelas_id`), Pelanggaran's `jenis()` relationship uses the correct method name.
