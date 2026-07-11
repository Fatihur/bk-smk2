<x-app-layout>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Orang Tua</h1>
    </div>

    <div class="space-y-4">
        @forelse ($siswa as $s)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <span class="text-gray-900 font-medium">{{ $s->nama }}</span>
                    <span class="text-gray-500 text-sm ml-2">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                </div>
                @if ($s->orangTua->isEmpty())
                <button onclick="openModal({{ $s->id }}, '{{ $s->nama }}')" class="flex items-center gap-1 px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-medium hover:bg-purple-700 transition-colors">
                    <x-icon name="plus" class="w-3.5 h-3.5" />
                    Tambah Orang Tua
                </button>
                @endif
            </div>
            @if ($s->orangTua->isNotEmpty())
            <div class="p-0">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                            <th class="text-left px-5 py-3 font-medium">Nama</th>
                            <th class="text-left px-5 py-3 font-medium">Nomor WA</th>
                            <th class="text-left px-5 py-3 font-medium">Hubungan</th>
                            <th class="text-right px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($s->orangTua as $ot)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-gray-900">{{ $ot->nama }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $ot->nomor_wa }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $ot->hubungan }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <button onclick="editOrangTua({{ $ot->id }}, {{ $ot->id_siswa }}, '{{ $s->nama }}', '{{ $ot->nama }}', '{{ $ot->nomor_wa }}', '{{ $ot->hubungan }}')" class="text-purple-600 hover:text-purple-700 font-medium mr-4 text-sm">Edit</button>
                                <button onclick="hapusOrangTua({{ $ot->id }})" class="text-red-600 hover:text-red-700 text-sm font-medium">Hapus</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<div id="ortuModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-xl border border-gray-200 p-6 w-full max-w-md mx-4 shadow-xl">
        <h2 id="ortuModalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Orang Tua</h2>
        <form id="ortuForm">
            <input type="hidden" id="ortuId">
            <input type="hidden" id="ortuSiswaId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Siswa</label>
                <p id="ortuSiswaNama" class="text-gray-900 text-sm py-2"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Orang Tua</label>
                <input type="text" id="ortuNama" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WA</label>
                <input type="text" id="ortuNomorWa" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required maxlength="20">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Hubungan</label>
                <select id="ortuHubungan" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
                    <option value="">Pilih Hubungan</option>
                    <option value="ayah">Ayah</option>
                    <option value="ibu">Ibu</option>
                    <option value="wali">Wali</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeOrtuModal()" class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2 font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const ortuModal = document.getElementById('ortuModal');
const ortuForm = document.getElementById('ortuForm');
const ortuModalTitle = document.getElementById('ortuModalTitle');
const ortuId = document.getElementById('ortuId');
const ortuSiswaId = document.getElementById('ortuSiswaId');
const ortuSiswaNama = document.getElementById('ortuSiswaNama');
const ortuNama = document.getElementById('ortuNama');
const ortuNomorWa = document.getElementById('ortuNomorWa');
const ortuHubungan = document.getElementById('ortuHubungan');

function openModal(siswaId, siswaNama, data = null) {
    ortuModalTitle.textContent = data ? 'Edit Orang Tua' : 'Tambah Orang Tua';
    ortuId.value = data ? data.id : '';
    ortuSiswaId.value = siswaId;
    ortuSiswaNama.textContent = siswaNama;
    ortuNama.value = data ? data.nama : '';
    ortuNomorWa.value = data ? data.nomor_wa : '';
    ortuHubungan.value = data ? data.hubungan : '';
    ortuModal.classList.remove('hidden');
}

function closeOrtuModal() {
    ortuModal.classList.add('hidden');
    ortuForm.reset();
    ortuId.value = '';
}

ortuForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const id = ortuId.value;
    const url = id ? `/data-orang-tua/${id}` : '/data-orang-tua';
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            id_siswa: ortuSiswaId.value,
            nama: ortuNama.value,
            nomor_wa: ortuNomorWa.value,
            hubungan: ortuHubungan.value,
        }),
    })
    .then(res => res.json())
    .then(() => { closeOrtuModal(); location.reload(); })
    .catch(() => alert('Gagal menyimpan data'));
});

function editOrangTua(id, siswaId, siswaNama, nama, nomorWa, hubungan) {
    ortuModalTitle.textContent = 'Edit Orang Tua';
    ortuId.value = id;
    ortuSiswaId.value = siswaId;
    ortuSiswaNama.textContent = siswaNama;
    ortuNama.value = nama;
    ortuNomorWa.value = nomorWa;
    ortuHubungan.value = hubungan;
    ortuModal.classList.remove('hidden');
}

function hapusOrangTua(id) {
    if (!confirm('Yakin ingin menghapus orang tua ini?')) return;
    fetch(`/data-orang-tua/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(() => location.reload())
    .catch(() => alert('Gagal menghapus data'));
}
</script>
@endpush
</x-app-layout>