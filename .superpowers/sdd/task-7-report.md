# Task 7: Update Remaining Views

**Status:** DONE

## Changes Made

| File | Changes |
|------|---------|
| `resources/views/pelanggaran/index.blade.php` | `$s->nama` → `$s->nama_siswa`, column "Kelas" → "Rombel", `$p->siswa->kelas->tingkat + nama_kelas` → `$p->siswa->rombel` |
| `resources/views/laporan/index.blade.php` | `$s->nama` → `$s->nama_siswa`, `$s->kelas->nama_kelas` → `$s->rombel` |
| `resources/views/pdf/laporan.blade.php` | `$siswa->nama` → `$siswa->nama_siswa`, `$siswa->kelas->nama_kelas` → `$siswa->rombel`, column "Kelas" → "Rombel", `$p->siswa->nama` → `$p->siswa->nama_siswa`, `$p->siswa->kelas->nama_kelas` → `$p->siswa->rombel` |
| `resources/views/pdf/surat-teguran.blade.php` | `$siswa->nama` → `$siswa->nama_siswa`, `$siswa->kelas->tingkat + nama_kelas` → `$siswa->rombel` |

## Commit

`610a25e` - feat: update remaining views - replace kelas with rombel

## Concerns

- `pdf/laporan.blade.php:59` removed the `?? '-'` null fallback since `rombel` is a direct column (no intermediate relation).
