# Bulk Pelanggaran Input — Design Spec

## 1. Tujuan

Mengganti halaman `/pelanggaran/input` dari form single-entry dengan Select2 menjadi halaman
workflow yang mendukung input pelanggaran massal: pilih banyak siswa + 1 jenis pelanggaran dalam
satu submit.

---

## 2. Alur Pengguna

1. Buka `/pelanggaran/input`
2. **Pilih Siswa** — klik tombol → muncul modal DataTable siswa dengan checkbox → centang beberapa → klik "Pilih"
3. Siswa terpilih muncul sebagai chips/badge di area form (masing-masing bisa dihapus klik ×)
4. **Pilih Pelanggaran** — klik tombol → muncul modal daftar jenis pelanggaran (cukup list dengan radio, tanpa Search/Select2) → pilih satu → klik "Pilih"
5. Isi **Tanggal** (default: hari ini) dan **Keterangan** (opsional, textarea)
6. Klik **Simpan** → POST `/pelanggaran/bulk` → toast sukses → reset form siap input baru

---

## 3. Komponen Halaman

### 3.1. Form Utama (tidak pakai modal)

| Bagian | Elemen |
|---|---|
| **Selected Siswa** | Chips/badge container, masing-masing badge: `[Nama] ×` |
| **Selected Pelanggaran** | Chip/badge 1 buah: `[Nama Pelanggaran] ×` |
| **Tanggal** | `<input type="date">`, default `date('Y-m-d')` |
| **Keterangan** | `<textarea>` opsional |
| **Tombol Simpan** | `bg-purple-600` — disabled jika siswa/pelanggaran belum dipilih |

### 3.2. Modal Pilih Siswa

- DataTable dengan checkbox di setiap baris (orderable false)
- Kolom: checkbox, NISN, Nama, JK, Rombel
- Search, sort, pagination via DataTables (sudah ada dependency)
- Tombol "Pilih" di footer modal → tutup modal, update chips

### 3.3. Modal Pilih Jenis Pelanggaran

- List sederhana tanpa search (jumlah jenis pelanggaran < 50)
- Radio button per baris: `○ Nama Pelanggaran (X poin)`
- Tombol "Pilih" di footer → tutup modal, update chip

---

## 4. Backend

### 4.1. Route Baru

```
POST /pelanggaran/bulk → PelanggaranController@bulkStore
```

### 4.2. Controller Method

```php
public function bulkStore(Request $request)
{
    $validated = $request->validate([
        'id_siswa' => 'required|array|min:1',
        'id_siswa.*' => 'exists:siswa,id',
        'id_jenis' => 'required|exists:jenis_pelanggaran,id',
        'tanggal' => 'required|date',
        'keterangan' => 'nullable|string',
    ]);

    $data = [];
    foreach ($validated['id_siswa'] as $id) {
        $data[] = [
            'id_siswa' => $id,
            'id_jenis' => $validated['id_jenis'],
            'tanggal' => $validated['tanggal'],
            'keterangan' => $validated['keterangan'],
        ];
    }

    Pelanggaran::insert($data);

    return response()->json([
        'message' => count($data) . ' pelanggaran berhasil dicatat',
    ]);
}
```

- `Pelanggaran::insert()` → bulk insert, 1 query
- Tidak dispatch WA otomatis (sudah dihapus dari desain sebelumnya)
- Return JSON → toast di frontend

### 4.3. Route yang Dihapus

`GET /pelanggaran/input` tetap pakai `PelanggaranController@index` — view saja.
Route `/select2/siswa` dan `/select2/jenis` bisa dihapus karena Select2 sudah tidak dipakai
di halaman ini. Tapi dicek dulu apakah masih dipakai di halaman lain → hanya dipakai di
halaman input pelanggaran, jadi aman dihapus.

---

## 5. States & Edge Cases

| State | Penanganan |
|---|---|
| **Belum pilih siswa** | Tombol Simpan disabled, badge "Belum ada siswa dipilih" |
| **Belum pilih pelanggaran** | Tombol Simpan disabled |
| **Submit loading** | Tombol Simpan disabled + spinner |
| **Sukses** | Toast `"N pelanggaran berhasil dicatat"` + reset form |
| **Gagal** | Toast error + data tetap di form |
| **0 siswa valid** | Validasi `min:1` di backend → error JSON |

---

## 6. File yang Diubah

| File | Perubahan |
|---|---|
| `routes/web.php` | Tambah POST `/pelanggaran/bulk`, hapus select2 routes |
| `app/Http/Controllers/PelanggaranController.php` | Tambah method `bulkStore`, index tetap return view |
| `resources/views/pelanggaran/create.blade.php` | Tulis ulang total: hapus Select2, form workflow baru, 2 modal |
| `app/Http/Controllers/Select2Controller.php` | Bisa dihapus (tidak dipakai lagi) |

---

## 7. Tidak Diubah

- Model Pelanggaran, Siswa, JenisPelanggaran — tetap
- Halaman riwayat `/pelanggaran` (index) — tetap pakai route `riwayat`
- Halaman lain — tidak tersentuh
