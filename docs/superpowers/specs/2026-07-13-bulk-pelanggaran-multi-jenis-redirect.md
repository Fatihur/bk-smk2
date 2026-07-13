# Multi-Jenis Pelanggaran + Redirect — Design Spec

## Perubahan

### 1. Jenis Pelanggaran Multi-Select

**Frontend** (`create.blade.php`):
- Modal jenis: radio → checkbox (sama pola seperti siswa)
- `selectedJenis`: dari `null` jadi `[]` (array)
- `renderJenisList()`: render checkbox bukan radio
- `confirmJenis()`: collect semua checkbox checked
- `renderJenisChip()`: loop array
- `removeJenis(index)`: splice array
- `updateSubmitButton()`: cek `selectedJenis.length > 0`

**Backend** (`PelanggaranController@bulkStore`):
- `id_jenis` validasi: `required|array|min:1`, `id_jenis.* => exists`
- Double loop: tiap siswa × tiap jenis

### 2. Redirect Setelah Submit

- Ganti `resetForm()` di `.then()` dengan:
  ```
  setTimeout(() => location.href = '/pelanggaran', 1200)
  ```
