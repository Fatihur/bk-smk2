@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Data Siswa</h1>
        <div class="flex gap-3">
            <button onclick="openImportModal()" class="px-4 py-2 bg-[#121215] border border-[#1c1c22] text-[#fafafa] rounded-lg text-sm hover:bg-[#1c1c22] transition-colors">Import Excel</button>
            <button onclick="openModal()" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">+ Tambah Siswa</button>
        </div>
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[#71717a] border-b border-[#1c1c22]">
                    <th class="text-left py-3 px-4">NISN</th>
                    <th class="text-left py-3 px-4">Nama</th>
                    <th class="text-left py-3 px-4">Kelas</th>
                    <th class="text-right py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody id="siswa-table-body">
                @forelse ($siswa as $s)
                <tr class="border-b border-[#1c1c22] hover:bg-[#1c1c22]/50" data-id="{{ $s->id }}">
                    <td class="py-3 px-4 text-[#fafafa]">{{ $s->nisn }}</td>
                    <td class="py-3 px-4 text-[#fafafa]">{{ $s->nama }}</td>
                    <td class="py-3 px-4 text-[#a1a1aa]">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                    <td class="py-3 px-4 text-right">
                        <button onclick="editSiswa({{ $s->id }}, '{{ $s->nisn }}', '{{ $s->nama }}', {{ $s->id_kelas }})" class="text-[#0C5CAB] hover:underline mr-3">Edit</button>
                        <button onclick="hapusSiswa({{ $s->id }})" class="text-red-400 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-8 text-[#71717a]">Belum ada data siswa</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="siswaModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-lg font-semibold text-[#fafafa] mb-4">Tambah Siswa</h2>
        <form id="siswaForm">
            <input type="hidden" id="siswaId">
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">NISN</label>
                <input type="text" id="nisn" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Nama</label>
                <input type="text" id="nama" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Kelas</label>
                <select id="id_kelas" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
                    <option value="">Pilih Kelas</option>
                    @foreach ($kelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm text-[#a1a1aa] hover:text-[#fafafa] transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="importModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-semibold text-[#fafafa] mb-4">Import Siswa dari Excel</h2>
        <form id="importForm" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">File Excel (.xlsx / .csv)</label>
                <input type="file" id="importFile" accept=".xlsx,.csv" class="w-full text-sm text-[#a1a1aa] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-[#0C5CAB] file:text-[#fafafa] hover:file:bg-[#0a4a8a]" required>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-sm text-[#a1a1aa] hover:text-[#fafafa] transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Import</button>
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
@endsection
