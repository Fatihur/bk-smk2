<x-app-layout>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Siswa</h1>
        <div class="flex gap-3">
            <button onclick="openImportModal()" class="flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                <x-icon name="download" class="w-4 h-4" />
                Import Excel
            </button>
            <button onclick="openModal()" class="flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Siswa
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                    <th class="text-left px-5 py-3 font-medium">NISN</th>
                    <th class="text-left px-5 py-3 font-medium">Nama</th>
                    <th class="text-left px-5 py-3 font-medium">JK</th>
                    <th class="text-left px-5 py-3 font-medium">Rombel</th>
                    <th class="text-left px-5 py-3 font-medium">No. Wali</th>
                    <th class="text-right px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="siswa-table-body">
                @forelse ($siswa as $s)
                <tr class="border-t border-gray-100 hover:bg-gray-50" data-id="{{ $s->id }}">
                    <td class="px-5 py-3.5 text-gray-900">{{ $s->nisn }}</td>
                    <td class="px-5 py-3.5 text-gray-900">{{ $s->nama_siswa }}</td>
                    <td class="px-5 py-3.5">{{ $s->jk }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->rombel }}</td>
                    <td class="px-5 py-3.5">
                        @if ($s->no_wali)
                            <span class="text-green-600 font-medium">{{ $s->no_wali }}</span>
                        @else
                            <span class="text-red-400 italic">Belum diisi</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <button onclick="editSiswa({{ $s->id }})" class="text-purple-600 hover:text-purple-700 font-medium mr-4 text-sm">Edit</button>
                        <button onclick="hapusSiswa({{ $s->id }})" class="text-red-600 hover:text-red-700 text-sm font-medium">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">Belum ada data siswa</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="siswaModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-2xl mx-4 shadow-xl my-8">
        <h2 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Siswa</h2>
        <form id="siswaForm" class="grid grid-cols-2 gap-4">
            <input type="hidden" id="siswaId">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                <input type="text" id="nisn" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Siswa</label>
                <input type="text" id="nama_siswa" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="100">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <select id="jk" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih JK</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rombel</label>
                <select id="rombel" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih Rombel</option>
                    <option value="X KJJ">X KJJ</option>
                    <option value="XI KJJ">XI KJJ</option>
                    <option value="XII KJJ">XII KJJ</option>
                </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                <input type="text" id="tempat_lahir" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input type="date" id="tgl_lahir" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input type="text" id="nik" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Agama</label>
                <input type="text" id="agama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea id="alamat" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required></textarea>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">HP (opsional)</label>
                <input type="text" id="hp" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ayah (opsional)</label>
                <input type="text" id="ayah" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ibu (opsional)</label>
                <input type="text" id="ibu" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">No. WA Wali (opsional)</label>
                <input type="text" id="no_wali" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="col-span-2 flex justify-end gap-3 mt-2">
                <button type="button" onclick="closeModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Siswa dari Excel</h2>
        <p class="text-sm text-gray-500 mb-4">Upload file Excel (Dapodik atau template). Hanya siswa KJJ yang diimport.</p>
        <form id="importForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xls / .xlsx)</label>
                <input type="file" id="importFile" accept=".xls,.xlsx" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-600 file:text-white hover:file:bg-purple-700 file:transition-colors" required>
            </div>
            <div class="flex items-center justify-between mt-6">
                <a href="/data-siswa/template" class="text-sm text-purple-600 hover:text-purple-700 font-medium underline">Download Template Excel</a>
                <div class="flex gap-3">
                    <button type="button" onclick="closeImportModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const siswaModal = document.getElementById('siswaModal');
const importModal = document.getElementById('importModal');
const siswaForm = document.getElementById('siswaForm');
const importForm = document.getElementById('importForm');
const siswaModalTitle = document.getElementById('modalTitle');
const siswaId = document.getElementById('siswaId');
const nisn = document.getElementById('nisn');
const namaSiswa = document.getElementById('nama_siswa');
const jk = document.getElementById('jk');
const rombel = document.getElementById('rombel');
const tempatLahir = document.getElementById('tempat_lahir');
const tglLahir = document.getElementById('tgl_lahir');
const nik = document.getElementById('nik');
const agama = document.getElementById('agama');
const alamat = document.getElementById('alamat');
const hp = document.getElementById('hp');
const ayah = document.getElementById('ayah');
const ibu = document.getElementById('ibu');
const noWali = document.getElementById('no_wali');

function openModal(data = null) {
    siswaModalTitle.textContent = data ? 'Edit Siswa' : 'Tambah Siswa';
    siswaId.value = data ? data.id : '';
    nisn.value = data ? data.nisn : '';
    namaSiswa.value = data ? data.nama_siswa : '';
    jk.value = data ? data.jk : '';
    rombel.value = data ? data.rombel : '';
    tempatLahir.value = data ? data.tempat_lahir : '';
    tglLahir.value = data ? data.tgl_lahir : '';
    nik.value = data ? data.nik : '';
    agama.value = data ? data.agama : '';
    alamat.value = data ? data.alamat : '';
    hp.value = data ? data.hp : '';
    ayah.value = data ? data.ayah : '';
    ibu.value = data ? data.ibu : '';
    noWali.value = data ? data.no_wali : '';
    siswaModal.classList.remove('hidden');
}

function closeModal() {
    siswaModal.classList.add('hidden');
    siswaForm.reset();
    siswaId.value = '';
}

function openImportModal() {
    importModal.classList.remove('hidden');
}

function closeImportModal() {
    importModal.classList.add('hidden');
    importForm.reset();
}

siswaForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = siswaId.value;
    const url = id ? `/data-siswa/${id}` : '/data-siswa';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            nisn: nisn.value,
            nama_siswa: namaSiswa.value,
            jk: jk.value,
            rombel: rombel.value,
            tempat_lahir: tempatLahir.value,
            tgl_lahir: tglLahir.value,
            nik: nik.value,
            agama: agama.value,
            alamat: alamat.value,
            hp: hp.value || null,
            ayah: ayah.value || null,
            ibu: ibu.value || null,
            no_wali: noWali.value || null,
        }),
    })
    .then(res => res.json())
    .then(() => { closeModal(); location.reload(); })
    .catch(() => alert('Gagal menyimpan data'));
});

importForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('file', document.getElementById('importFile').files[0]);

    fetch('/data-siswa/import', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData,
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        closeImportModal();
        location.reload();
    })
    .catch(() => alert('Gagal import data'));
});

function editSiswa(id) {
    fetch(`/data-siswa/${id}/edit`)
        .then(res => res.json())
        .then(data => {
            openModal(data);
        })
        .catch(() => {
            // fallback: get from table row
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                const cells = row.querySelectorAll('td');
                openModal({
                    id: id,
                    nisn: cells[0].textContent.trim(),
                    nama_siswa: cells[1].textContent.trim(),
                    jk: cells[2].textContent.trim(),
                    rombel: cells[3].textContent.trim(),
                });
            }
        });
}

function hapusSiswa(id) {
    if (!confirm('Yakin ingin menghapus siswa ini?')) return;
    fetch(`/data-siswa/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(() => location.reload())
    .catch(() => alert('Gagal menghapus data'));
}
</script>
@endpush
</x-app-layout>
