<x-app-layout>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Jenis Pelanggaran</h1>
        <button onclick="openModal()" class="flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
            <x-icon name="plus" class="w-4 h-4" />
            Tambah Jenis
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                    <th class="text-left px-5 py-3 font-medium">Nama Pelanggaran</th>
                    <th class="text-left px-5 py-3 font-medium">Poin</th>
                    <th class="text-right px-5 py-3 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody id="jenis-table-body">
                @forelse ($jenis as $j)
                <tr class="border-t border-gray-100 hover:bg-gray-50" data-id="{{ $j->id }}">
                    <td class="px-5 py-3.5 text-gray-900">{{ $j->nama }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $j->poin }}</td>
                    <td class="px-5 py-3.5 text-right">
                        <button onclick="editJenis({{ $j->id }}, '{{ $j->nama }}', {{ $j->poin }})" class="p-1.5 rounded-lg text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="Edit">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        </button>
                        <button onclick="hapusJenis({{ $j->id }})" class="p-1.5 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-8 text-gray-500">Belum ada data jenis pelanggaran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="jenisModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Jenis Pelanggaran</h2>
        <form id="jenisForm">
            <input type="hidden" id="jenisId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggaran</label>
                <input type="text" id="nama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Poin</label>
                <input type="number" id="poin" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required min="1">
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
        toast('Data berhasil disimpan', 'success');
        location.reload();
    })
    .catch(err => toast('Gagal menyimpan data', 'error'));
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
    .then(() => { toast('Data berhasil dihapus', 'success'); location.reload(); })
    .catch(() => toast('Gagal menghapus data', 'error'));
}
</script>
@endpush
</x-app-layout>