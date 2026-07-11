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
                    <th class="text-left px-5 py-3 font-medium">Kelas</th>
                    <th class="text-right px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="siswa-table-body">
                @forelse ($siswa as $s)
                <tr class="border-t border-gray-100 hover:bg-gray-50" data-id="{{ $s->id }}">
                    <td class="px-5 py-3.5 text-gray-900">{{ $s->nisn }}</td>
                    <td class="px-5 py-3.5 text-gray-900">{{ $s->nama }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <button onclick="editSiswa({{ $s->id }}, '{{ $s->nisn }}', '{{ $s->nama }}', {{ $s->id_kelas }})" class="text-purple-600 hover:text-purple-700 font-medium mr-4 text-sm">Edit</button>
                        <button onclick="hapusSiswa({{ $s->id }})" class="text-red-600 hover:text-red-700 text-sm font-medium">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-8 text-gray-500">Belum ada data siswa</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="siswaModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Siswa</h2>
        <form id="siswaForm">
            <input type="hidden" id="siswaId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                <input type="text" id="nisn" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" id="nama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                <select id="id_kelas" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih Kelas</option>
                    @foreach ($kelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Import Siswa dari Excel</h2>
        <form id="importForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">File Excel (.xlsx / .csv)</label>
                <input type="file" id="importFile" accept=".xlsx,.csv" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-600 file:text-white hover:file:bg-purple-700 file:transition-colors" required>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeImportModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Import</button>
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
const nama = document.getElementById('nama');
const idKelas = document.getElementById('id_kelas');

function openModal(data = null) {
    siswaModalTitle.textContent = data ? 'Edit Siswa' : 'Tambah Siswa';
    siswaId.value = data ? data.id : '';
    nisn.value = data ? data.nisn : '';
    nama.value = data ? data.nama : '';
    idKelas.value = data ? data.id_kelas : '';
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
        body: JSON.stringify({ nisn: nisn.value, nama: nama.value, id_kelas: idKelas.value }),
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

function editSiswa(id, nisnVal, namaVal, kelasId) {
    siswaModalTitle.textContent = 'Edit Siswa';
    siswaId.value = id;
    nisn.value = nisnVal;
    nama.value = namaVal;
    idKelas.value = kelasId;
    siswaModal.classList.remove('hidden');
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