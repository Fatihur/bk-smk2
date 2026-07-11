@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Data Kelas</h1>
        <button onclick="openModal()" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">+ Tambah Kelas</button>
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[#71717a] border-b border-[#1c1c22]">
                    <th class="text-left py-3 px-4">Nama Kelas</th>
                    <th class="text-left py-3 px-4">Tingkat</th>
                    <th class="text-right py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody id="kelas-table-body">
                @forelse ($kelas as $k)
                <tr class="border-b border-[#1c1c22] hover:bg-[#1c1c22]/50" data-id="{{ $k->id }}">
                    <td class="py-3 px-4 text-[#fafafa]">{{ $k->nama_kelas }}</td>
                    <td class="py-3 px-4 text-[#a1a1aa]">{{ $k->tingkat }}</td>
                    <td class="py-3 px-4 text-right">
                        <button onclick="editKelas({{ $k->id }}, '{{ $k->nama_kelas }}', '{{ $k->tingkat }}')" class="text-[#0C5CAB] hover:underline mr-3">Edit</button>
                        <button onclick="hapusKelas({{ $k->id }})" class="text-red-400 hover:underline">Hapus</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-8 text-[#71717a]">Belum ada data kelas</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="kelasModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-lg font-semibold text-[#fafafa] mb-4">Tambah Kelas</h2>
        <form id="kelasForm">
            <input type="hidden" id="kelasId">
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Nama Kelas</label>
                <input type="text" id="nama_kelas" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required maxlength="50">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Tingkat</label>
                <select id="tingkat" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
                    <option value="">Pilih Tingkat</option>
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm text-[#a1a1aa] hover:text-[#fafafa] transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Simpan</button>
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
