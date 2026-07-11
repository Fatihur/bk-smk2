@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Jenis Pelanggaran</h1>
        <button onclick="openModal()" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">+ Tambah Jenis</button>
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[#71717a] border-b border-[#1c1c22]">
                    <th class="text-left py-3 px-4">Nama Pelanggaran</th>
                    <th class="text-left py-3 px-4">Poin</th>
                    <th class="text-right py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody id="jenis-table-body">
                @forelse ($jenis as $j)
                <tr class="border-b border-[#1c1c22] hover:bg-[#1c1c22]/50" data-id="{{ $j->id }}">
                    <td class="py-3 px-4 text-[#fafafa]">{{ $j->nama }}</td>
                    <td class="py-3 px-4 text-[#a1a1aa]">{{ $j->poin }}</td>
                    <td class="py-3 px-4 text-right">
                        <button onclick="editJenis({{ $j->id }}, '{{ $j->nama }}', {{ $j->poin }})" class="text-[#0C5CAB] hover:underline mr-3">Edit</button>
                        <button onclick="hapusJenis({{ $j->id }})" class="text-red-400 hover:underline">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-8 text-[#71717a]">Belum ada data jenis pelanggaran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="jenisModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6 w-full max-w-md mx-4">
        <h2 id="modalTitle" class="text-lg font-semibold text-[#fafafa] mb-4">Tambah Jenis Pelanggaran</h2>
        <form id="jenisForm">
            <input type="hidden" id="jenisId">
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Nama Pelanggaran</label>
                <input type="text" id="nama" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Poin</label>
                <input type="number" id="poin" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required min="1">
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
const modal = document.getElementById('jenisModal');
const form = document.getElementById('jenisForm');
const modalTitle = document.getElementById('modalTitle');
const jenisId = document.getElementById('jenisId');
const nama = document.getElementById('nama');
const poin = document.getElementById('poin');

function openModal(data = null) {
    modalTitle.textContent = data ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran';
    jenisId.value = data ? data.id : '';
    nama.value = data ? data.nama : '';
    poin.value = data ? data.poin : '';
    modal.classList.remove('hidden');
}

function closeModal() {
    modal.classList.add('hidden');
    form.reset();
    jenisId.value = '';
}

form.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = jenisId.value;
    const url = id ? `/jenis-pelanggaran/${id}` : '/jenis-pelanggaran';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ nama: nama.value, poin: poin.value }),
    })
    .then(res => res.json())
    .then(data => {
        closeModal();
        location.reload();
    })
    .catch(err => alert('Gagal menyimpan data'));
});

function editJenis(id, nm, pn) {
    openModal({ id, nama: nm, poin: pn });
}

function hapusJenis(id) {
    if (!confirm('Yakin ingin menghapus jenis pelanggaran ini?')) return;
    fetch(`/jenis-pelanggaran/${id}`, {
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
