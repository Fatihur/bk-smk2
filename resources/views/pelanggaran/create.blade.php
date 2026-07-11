@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Input Pelanggaran</h1>
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6">
        <form id="pelanggaranForm">
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Siswa</label>
                <select id="id_siswa" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
                    <option value="">Pilih Siswa</option>
                    @foreach ($siswa as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nisn }}) - {{ $s->kelas->tingkat }} {{ $s->kelas->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Jenis Pelanggaran</label>
                <select id="id_jenis" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
                    <option value="">Pilih Pelanggaran</option>
                    @foreach ($jenis as $j)
                    <option value="{{ $j->id }}">{{ $j->nama }} ({{ $j->poin }} poin)</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Tanggal</label>
                <input type="date" id="tanggal" value="{{ date('Y-m-d') }}"
                    class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-[#a1a1aa] mb-1">Keterangan</label>
                <textarea id="keterangan" rows="3"
                    class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Simpan</button>
            </div>
        </form>
        <div id="notifikasi" class="mt-4 hidden"></div>
    </div>
</div>

@push('scripts')
<script>
const form = document.getElementById('pelanggaranForm');
const notifikasi = document.getElementById('notifikasi');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('/pelanggaran', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            id_siswa: document.getElementById('id_siswa').value,
            id_jenis: document.getElementById('id_jenis').value,
            tanggal: document.getElementById('tanggal').value,
            keterangan: document.getElementById('keterangan').value,
        }),
    })
    .then(res => res.json())
    .then(data => {
        notifikasi.className = 'mt-4 p-3 rounded-lg text-sm';
        notifikasi.classList.add('bg-green-900/30', 'border', 'border-green-700/50', 'text-green-300');
        notifikasi.textContent = data.message;
        notifikasi.classList.remove('hidden');
        form.reset();
        document.getElementById('tanggal').value = '{{ date('Y-m-d') }}';
    })
    .catch(err => {
        notifikasi.className = 'mt-4 p-3 rounded-lg text-sm';
        notifikasi.classList.add('bg-red-900/30', 'border', 'border-red-700/50', 'text-red-300');
        notifikasi.textContent = 'Gagal menyimpan pelanggaran';
        notifikasi.classList.remove('hidden');
    });
});
</script>
@endpush
@endsection
