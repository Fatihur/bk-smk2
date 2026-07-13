# Multi-Jenis + Redirect Implementation Plan

**Goal:** Allow selecting multiple violation types per submission; redirect to history page after success.

**Architecture:** Backend: change `id_jenis` to array validation + double loop. Frontend: jenis modal radio → checkbox, `selectedJenis` null → array, success handler → redirect.

---
### Task 1: Backend + Frontend

**Files:**
- Modify: `app/Http/Controllers/PelanggaranController.php:30-55`
- Modify: `resources/views/pelanggaran/create.blade.php`

- [ ] **Step 1: Update bulkStore validation + loop**

```php
// PelanggaranController.php — change id_jenis from single to array
'id_jenis' => 'required|array|min:1',
'id_jenis.*' => 'exists:jenis_pelanggaran,id',

// Change the loop — double loop over siswa × jenis
$data = [];
foreach ($validated['id_siswa'] as $siswaId) {
    foreach ($validated['id_jenis'] as $jenisId) {
        $data[] = [
            'id_siswa' => $siswaId,
            'id_jenis' => $jenisId,
            'tanggal' => $validated['tanggal'],
            'keterangan' => $validated['keterangan'],
        ];
    }
}
```

- [ ] **Step 2: Update create.blade.php — jenis multi-select + redirect**

Changes:
1. `selectedJenis` → `[]`, check `selectedJenis.length`
2. `renderJenisList()` → checkboxes instead of radio, add select-all
3. `confirmJenis()` → collect checkboxes
4. `renderJenisChip()` → loop array, × per index
5. `removeJenis(index)` → splice
6. `updateSubmitButton()` → `selectedJenis.length > 0`
7. Submit sends `id_jenis: selectedJenis.map(j => j.id)`
8. `.then()` → `setTimeout(() => location.href = '/pelanggaran', 1200)` instead of resetForm
9. `resetForm()` → `selectedJenis = []`

- [ ] **Step 3: Verify**

Run: `php artisan view:clear`
Open `/pelanggaran/input` — check multi-select jenis + redirect.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/PelanggaranController.php resources/views/pelanggaran/create.blade.php
git commit -m "feat: multi-select jenis pelanggaran, redirect to history after submit"
```
