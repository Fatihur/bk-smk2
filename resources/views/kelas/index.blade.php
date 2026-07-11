@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Kelas</h1>
        <button onclick="openModal()" class="flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
            <x-icon name="plus" class="w-4 h-4" />
            Tambah Kelas
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                    <th class="text-left px-5 py-3 font-medium">Nama Kelas</th>
                    <th class="text-left px-5 py-3 font-medium">Tingkat</th>
                    <th class="text-right px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="kelas-table-body">
                @forelse ($kelas as $k)
                <tr class="border-t border-gray-100 hover:bg-gray-50" data-id="{{ $k->id }}">
                    <td class="px-5 py-3.5 text-gray-900">{{ $k->nama_kelas }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $k->tingkat }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <button onclick="editKelas({{ $k->id }}, '{{ $k->nama_kelas }}', '{{ $k->tingkat }}')" class="text-purple-600 hover:text-purple-700 font-medium mr-4 text-sm">Edit</button>
                        <button onclick="hapusKelas({{ $k->id }})" class="text-red-600 hover:text-red-700 text-sm font-medium">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-8 text-gray-500">Belum ada data kelas</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="kelasModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Kelas</h2>
        <form id="kelasForm">
            <input type="hidden" id="kelasId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                <input type="text" id="nama_kelas" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="50">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat</label>
                <select id="tingkat" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih Tingkat</option>
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const modal = document.getElementById('kelasModal');
const form = document.getElementById('kelasForm');
const modalTitle = document.getElementById('modalTitle');
const kelasId = document.getElementById('kelasId');
const namaKelas = document.getElementById('nama_kelas');
const tingkat = document.getElementById('tingkat');

function openModal(data = null) {
    modalTitle.textContent = data ? 'Edit Kelas' : 'Tambah Kelas';
    kelasId.value = data ? data.id : '';
    namaKelas.value = data ? data.nama_kelas : '';
    tingkat.value = data ? data.tingkat : '';
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
    form.reset();
    kelasId.value = '';
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = kelasId.value;
    const url = id ? `/data-kelas/${id}` : '/data-kelas';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ nama_kelas: namaKelas.value, tingkat: tingkat.value }),
    })
    .then(res => res.json())
    .then(data => {
        closeModal();
        location.reload();
    })
    .catch(err => alert('Gagal menyimpan data'));
});

function editKelas(id, nama, tkt) {
    openModal({ id, nama_kelas: nama, tingkat: tkt });
}

function hapusKelas(id) {
    if (!confirm('Yakin ingin menghapus kelas ini?')) return;
    fetch(`/data-kelas/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(res => res.json())
    .then(() => location.reload())
    .catch(() => alert('Gagal menghapus data'));
}
</script>
@endpush
@endsection