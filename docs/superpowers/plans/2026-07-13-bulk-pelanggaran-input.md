# Bulk Pelanggaran Input Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) for tracking.

**Goal:** Replace the single-entry Select2-based pelanggaran input page with a bulk workflow: select multiple students + one violation type, submit once.

**Architecture:** Server-side: one new `POST /pelanggaran/bulk` route + `bulkStore()` method using `Pelanggaran::insert()`. Client-side: rewrite `create.blade.php` with 2 modals (DataTable siswa with checkboxes, jenis radio list), chips for selected items, and a JSON submit handler. Remove Select2 entirely.

**Tech Stack:** Laravel 13, jQuery 3.7.1 (already loaded via layout CDN), DataTables 2.2.2 (already loaded via layout CDN), Tailwind CSS, no Select2.

---

### Task 1: Backend — Route, Controller, Select2 Cleanup

**Files:**
- Modify: `routes/web.php:27-48`
- Modify: `app/Http/Controllers/PelanggaranController.php:16-52`
- Create: (no new file)
- Delete: `app/Http/Controllers/Select2Controller.php`
- Delete: `resources/views/layouts/app.blade.php:11,14`

- [ ] **Step 1: Add bulkStore method to PelanggaranController**

```php
// app/Http/Controllers/PelanggaranController.php — add after store()

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

- [ ] **Step 2: Register new route, remove select2 routes**

Replace the pelanggaran/select2 routes in `routes/web.php`:

```php
// In routes/web.php, in the 'role:guru_bk' group:
// ADD before the select2 routes:
Route::post('/pelanggaran/bulk', [PelanggaranController::class, 'bulkStore']);

// DELETE these two lines:
// Route::get('/select2/siswa', [Select2Controller::class, 'siswa'])->name('select2.siswa');
// Route::get('/select2/jenis', [Select2Controller::class, 'jenis'])->name('select2.jenis');
```

- [ ] **Step 3: Remove Select2 CDN from layout**

In `resources/views/layouts/app.blade.php`:
- Delete line 11: `<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">`
- Delete line 14: `<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>`

Also remove the unused import in `routes/web.php`:
```php
// Delete this line:
use App\Http\Controllers\Select2Controller;
```

- [ ] **Step 4: Delete Select2Controller.php file**

Delete `app/Http/Controllers/Select2Controller.php`.

- [ ] **Step 5: Commit**

```bash
git add routes/web.php app/Http/Controllers/PelanggaranController.php resources/views/layouts/app.blade.php
git rm app/Http/Controllers/Select2Controller.php
git commit -m "feat: add bulkStore for pelanggaran, remove select2"
```

---

### Task 2: Frontend — Rewrite create.blade.php

**Files:**
- Rewrite: `resources/views/pelanggaran/create.blade.php` (full replacement)

**Interfaces:**
- Consumes: `POST /pelanggaran/bulk` (JSON) from Task 1
- Produces: the input page with bulk workflow

- [ ] **Step 1: Write the new create.blade.php**

Full rewrite of `resources/views/pelanggaran/create.blade.php`:

```blade
<x-app-layout>
@php
    $siswaData = \App\Models\Siswa::orderBy('nama_siswa')->get(['id', 'nisn', 'nama_siswa', 'jk', 'rombel']);
    $jenisData = \App\Models\JenisPelanggaran::orderBy('nama')->get(['id', 'nama', 'poin']);
@endphp
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Input Pelanggaran</h1>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form id="pelanggaranForm">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Siswa</label>
                <div id="selectedSiswa" class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                    <span class="text-sm text-gray-400" id="siswaPlaceholder">Belum ada siswa dipilih</span>
                </div>
                <button type="button" onclick="openSiswaModal()"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors">
                    + Pilih Siswa
                </button>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pelanggaran</label>
                <div id="selectedJenis" class="mb-2 min-h-[32px]">
                    <span class="text-sm text-gray-400" id="jenisPlaceholder">Belum dipilih</span>
                </div>
                <button type="button" onclick="openJenisModal()"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 transition-colors">
                    + Pilih Pelanggaran
                </button>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" id="tanggal" value="{{ date('Y-m-d') }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea id="keterangan" rows="3"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" id="btnSubmit" disabled
                    class="px-6 py-2 bg-purple-400 text-white text-sm font-medium rounded-lg cursor-not-allowed">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Pilih Siswa --}}
<div id="siswaModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-4xl mx-4 shadow-xl my-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Pilih Siswa</h2>
            <button type="button" onclick="closeSiswaModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <table id="siswaTable" class="w-full" style="width:100%">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllSiswa"></th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>JK</th>
                    <th>Rombel</th>
                </tr>
            </thead>
        </table>
        <div class="flex justify-end gap-2 mt-4">
            <button onclick="closeSiswaModal()" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Batal</button>
            <button onclick="confirmSiswa()" class="px-4 py-2 text-sm text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">Pilih</button>
        </div>
    </div>
</div>

{{-- Modal Pilih Jenis Pelanggaran --}}
<div id="jenisModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-lg mx-4 shadow-xl my-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Pilih Jenis Pelanggaran</h2>
            <button type="button" onclick="closeJenisModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="jenisList" class="max-h-[60vh] overflow-y-auto space-y-2">
            {{-- populated via JS --}}
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <button onclick="closeJenisModal()" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Batal</button>
            <button onclick="confirmJenis()" class="px-4 py-2 text-sm text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">Pilih</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedSiswa = [];
let selectedJenis = null;
let jenisData = @json($jenisData);

document.addEventListener('DOMContentLoaded', function() {
    renderJenisList(jenisData);

    new DataTable('#siswaTable', {
        data: @json($siswaData),
        columns: [
            {
                data: null,
                orderable: false,
                render: function(row) {
                    const checked = selectedSiswa.some(s => s.id == row.id) ? 'checked' : '';
                    return `<input type="checkbox" class="siswa-checkbox" value="${row.id}" data-nama="${row.nama_siswa}" ${checked}>`;
                },
            },
            { data: 'nisn' },
            { data: 'nama_siswa' },
            { data: 'jk' },
            { data: 'rombel' },
        ],
        order: [[2, 'asc']],
        pageLength: 10,
        language: { url: '//cdn.datatables.net/plug-ins/2.2.2/i18n/id.json' },
        drawCallback: function() {
            document.querySelectorAll('.siswa-checkbox').forEach(cb => {
                cb.checked = selectedSiswa.some(s => s.id == cb.value);
            });
        },
    });

    document.getElementById('selectAllSiswa').addEventListener('change', function() {
        document.querySelectorAll('.siswa-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.querySelector('#siswaTable tbody').addEventListener('change', function(e) {
        if (e.target.classList.contains('siswa-checkbox')) {
            const all = document.querySelectorAll('.siswa-checkbox');
            const checked = document.querySelectorAll('.siswa-checkbox:checked');
            document.getElementById('selectAllSiswa').checked = all.length > 0 && all.length === checked.length;
        }
    });

    document.getElementById('pelanggaranForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (selectedSiswa.length === 0 || !selectedJenis) return;

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = 'Menyimpan...';
        btn.className = 'px-6 py-2 bg-purple-400 text-white text-sm font-medium rounded-lg cursor-not-allowed';

        fetch('/pelanggaran/bulk', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({
                id_siswa: selectedSiswa.map(s => s.id),
                id_jenis: selectedJenis.id,
                tanggal: document.getElementById('tanggal').value,
                keterangan: document.getElementById('keterangan').value,
            }),
        })
        .then(res => res.json())
        .then(data => {
            window.toast(data.message, 'success');
            resetForm();
        })
        .catch(() => {
            window.toast('Gagal menyimpan pelanggaran', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Simpan';
            btn.className = 'px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors';
        });
    });
});

function renderJenisList(data) {
    const container = document.getElementById('jenisList');
    container.innerHTML = data.map(j => `
        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors ${selectedJenis?.id == j.id ? 'border-purple-500 bg-purple-50' : ''}">
            <input type="radio" name="id_jenis" value="${j.id}" data-nama="${j.nama}" data-poin="${j.poin}" ${selectedJenis?.id == j.id ? 'checked' : ''}>
            <span class="text-sm text-gray-800">${j.nama} <span class="text-gray-400">(${j.poin} poin)</span></span>
        </label>
    `).join('');
}

function openSiswaModal() {
    document.getElementById('siswaModal').classList.remove('hidden');
    document.getElementById('siswaModal').scrollTop = 0;
}

function closeSiswaModal() {
    document.getElementById('siswaModal').classList.add('hidden');
}

function confirmSiswa() {
    selectedSiswa = [];
    document.querySelectorAll('.siswa-checkbox:checked').forEach(cb => {
        selectedSiswa.push({ id: parseInt(cb.value), nama: cb.dataset.nama });
    });
    renderSiswaChips();
    closeSiswaModal();
    updateSubmitButton();
}

function renderSiswaChips() {
    const container = document.getElementById('selectedSiswa');
    const placeholder = document.getElementById('siswaPlaceholder');
    container.innerHTML = '';
    if (selectedSiswa.length === 0) {
        container.appendChild(placeholder);
        return;
    }
    selectedSiswa.forEach((s, i) => {
        const chip = document.createElement('span');
        chip.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full';
        chip.innerHTML = `${s.nama} <button type="button" onclick="removeSiswa(${i})" class="text-purple-400 hover:text-purple-600">&times;</button>`;
        container.appendChild(chip);
    });
}

function removeSiswa(index) {
    selectedSiswa.splice(index, 1);
    renderSiswaChips();
    updateSubmitButton();
}

function openJenisModal() {
    renderJenisList(jenisData);
    document.getElementById('jenisModal').classList.remove('hidden');
}

function closeJenisModal() {
    document.getElementById('jenisModal').classList.add('hidden');
}

function confirmJenis() {
    const checked = document.querySelector('input[name="id_jenis"]:checked');
    if (checked) {
        selectedJenis = {
            id: parseInt(checked.value),
            nama: checked.dataset.nama,
            poin: checked.dataset.poin,
        };
    }
    renderJenisChip();
    closeJenisModal();
    updateSubmitButton();
}

function renderJenisChip() {
    const container = document.getElementById('selectedJenis');
    const placeholder = document.getElementById('jenisPlaceholder');
    container.innerHTML = '';
    if (!selectedJenis) {
        container.appendChild(placeholder);
        return;
    }
    const chip = document.createElement('span');
    chip.className = 'inline-flex items-center gap-1.5 px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full';
    chip.innerHTML = `${selectedJenis.nama} (${selectedJenis.poin} poin) <button type="button" onclick="removeJenis()" class="text-purple-400 hover:text-purple-600">&times;</button>`;
    container.appendChild(chip);
}

function removeJenis() {
    selectedJenis = null;
    renderJenisChip();
    updateSubmitButton();
}

function updateSubmitButton() {
    const btn = document.getElementById('btnSubmit');
    const enabled = selectedSiswa.length > 0 && selectedJenis !== null;
    btn.disabled = !enabled;
    btn.className = enabled
        ? 'px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors'
        : 'px-6 py-2 bg-purple-400 text-white text-sm font-medium rounded-lg cursor-not-allowed';
}

function resetForm() {
    selectedSiswa = [];
    selectedJenis = null;
    renderSiswaChips();
    renderJenisChip();
    document.getElementById('keterangan').value = '';
    document.getElementById('tanggal').value = '{{ date('Y-m-d') }}';
    updateSubmitButton();
}
</script>
@endpush
</x-app-layout>
```

- [ ] **Step 2: Verify the view renders correctly**

Run: `php artisan route:list | findstr pelanggaran`
Expected: shows `POST pelanggaran/bulk` and `GET pelanggaran/input`

Run: `php artisan view:clear` then open `/pelanggaran/input` in browser.
Check: page loads without errors, no select2 references in console.

- [ ] **Step 3: Commit**

```bash
git add resources/views/pelanggaran/create.blade.php
git commit -m "feat: rewrite pelanggaran input page with bulk workflow"
```