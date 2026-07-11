@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Data Orang Tua</h1>
    </div>

    <div class="space-y-4">
        @forelse ($siswa as $s)
        <div class="bg-[#121215] border border-[#1c1c22] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-[#1c1c22] flex items-center justify-between">
                <div>
                    <span class="text-[#fafafa] font-medium">{{ $s->nama }}</span>
                    <span class="text-[#71717a] text-sm ml-2">{{ $s->kelas->nama_kelas ?? '-' }}</span>
                </div>
                @if ($s->orangTua->isEmpty())
                <button onclick="openModal({{ $s->id }}, '{{ $s->nama }}')" class="px-3 py-1.5 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-xs hover:bg-[#0a4a8a] transition-colors">+ Tambah Orang Tua</button>
                @endif
            </div>
            @if ($s->orangTua->isNotEmpty())
            <div class="p-0">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[#71717a] border-b border-[#1c1c22]">
                            <th class="text-left py-3 px-4">Nama</th>
                            <th class="text-left py-3 px-4">Nomor WA</th>
                            <th class="text-left py-3 px-4">Hubungan</th>
                            <th class="text-right py-3 px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($s->orangTua as $ot)
                        <tr class="border-b border-[#1c1c22] hover:bg-[#1c1c22]/50">
                            <td class="py-3 px-4 text-[#fafafa]">{{ $ot->nama }}</td>
                            <td class="py-3 px-4 text-[#a1a1aa]">{{ $ot->nomor_wa }}</td>
                            <td class="py-3 px-4 text-[#a1a1aa]">{{ $ot->hubungan }}</td>
                            <td class="py-3 px-4 text-right">
                                <button onclick="editOrangTua({{ $ot->id }}, {{ $ot->id_siswa }}, '{{ $s->nama }}', '{{ $ot->nama }}', '{{ $ot->nomor_wa }}', '{{ $ot->hubungan }}')" class="text-[#0C5CAB] hover:underline mr-3">Edit</button>
                                <button onclick="hapusOrangTua({{ $ot->id }})" class="text-red-400 hover:underline">Hapus</button>
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

<div id="ortuModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6 w-full max-w-md mx-4">
        <h2 id="ortuModalTitle" class="text-lg font-semibold text-[#fafafa] mb-4">Tambah Orang Tua</h2>
        <form id="ortuForm">
            <input type="hidden" id="ortuId">
            <input type="hidden" id="ortuSiswaId">
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Siswa</label>
                <p id="ortuSiswaNama" class="text-[#fafafa] text-sm py-2"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Nama Orang Tua</label>
                <input type="text" id="ortuNama" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Nomor WA</label>
                <input type="text" id="ortuNomorWa" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required maxlength="20">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Hubungan</label>
                <select id="ortuHubungan" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
                    <option value="">Pilih Hubungan</option>
                    <option value="ayah">Ayah</option>
                    <option value="ibu">Ibu</option>
                    <option value="wali">Wali</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeOrtuModal()" class="px-4 py-2 text-sm text-[#a1a1aa] hover:text-[#fafafa] transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Simpan</button>
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
@endsection
