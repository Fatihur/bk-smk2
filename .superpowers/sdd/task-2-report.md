# Task 2 Report: Update Models

## What was implemented

- Rewrote `app/Models/Siswa.php` — removed `BelongsTo` Kelas relation and `orangTua` HasMany relation, removed `$timestamps = false`, replaced fillable with new fields (`nama_siswa`, `jk`, `nisn`, `tempat_lahir`, `tgl_lahir`, `nik`, `agama`, `alamat`, `hp`, `ayah`, `ibu`, `no_wali`, `rombel`), removed `BelongsTo` import.
- Deleted `app/Models/Kelas.php`
- Deleted `app/Models/OrangTua.php`

## Files changed

- `app/Models/Siswa.php` (rewritten)
- `app/Models/Kelas.php` (deleted)
- `app/Models/OrangTua.php` (deleted)

## Self-review findings

1. The new Siswa model matches the updated `siswa` table schema (no `id_kelas`, parent info inlined directly as columns).
2. Removed `$timestamps = false` — the migration doesn't disable timestamps.
3. No `kelas()` or `orangTua()` relations remain (Kelas/OrangTua tables are dropped).
4. Downstream code referencing `$siswa->kelas` or `Kelas`/`OrangTua` models will break until the controller tasks are applied (handled in later tasks).

## Issues

- `app/Jobs/KirimWaTeguran.php` references `$siswa->kelas->nama_kelas` — will break if run before controllers are updated.
- `app/Http/Controllers/KelasController.php`, `OrangTuaController.php`, `SiswaController.php`, `PelanggaranController.php` still reference Kelas/OrangTua models — expected, handled in subsequent tasks.
