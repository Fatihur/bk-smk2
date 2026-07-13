<x-app-layout>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Riwayat Pelanggaran</h1>
        @if (Auth::user()->role === 'guru_bk')
        <a href="{{ route('pelanggaran.input') }}" class="flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
            <x-icon name="plus" class="w-4 h-4" />
            Input Pelanggaran
        </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('pelanggaran.riwayat') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Siswa</label>
                <select name="id_siswa" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach ($siswa as $s)
                    <option value="{{ $s->id }}" {{ request('id_siswa') == $s->id ? 'selected' : '' }}>{{ $s->nama_siswa }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Filter</button>
                <a href="{{ route('pelanggaran.riwayat') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:text-gray-800 transition-colors">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                    <th class="text-left px-5 py-3 font-medium">Siswa</th>
                    <th class="text-left px-5 py-3 font-medium">Rombel</th>
                    <th class="text-left px-5 py-3 font-medium">Total Poin</th>
                    <th class="text-left px-5 py-3 font-medium">Jml</th>
                    <th class="text-left px-5 py-3 font-medium">Status SP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftarSiswa as $s)
                <tr class="accordion-toggle border-t border-gray-100 hover:bg-gray-50 cursor-pointer"
                    data-target="detail-{{ $s->id }}"
                    onclick="toggleAccordion(this)">
                    <td class="px-5 py-3.5 text-gray-900 font-medium">
                        <span class="accordion-arrow mr-2 text-gray-400 transition-transform inline-block">▶</span>
                        {{ $s->nama_siswa }}
                    </td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->rombel }}</td>
                    <td class="px-5 py-3.5 font-semibold {{ $s->total_poin > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ $s->total_poin }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $s->pelanggaran->count() }}</td>
                    <td class="px-5 py-3.5">
                        @php
                            $latestSp = $s->suratTeguran->sortByDesc('tingkat')->first();
                            $colors = ['sp1' => 'bg-blue-600', 'sp2' => 'bg-yellow-500', 'sp3' => 'bg-red-600'];
                        @endphp
                        @if ($latestSp)
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-white {{ $colors[$latestSp->tingkat] ?? 'bg-gray-500' }}">
                                {{ strtoupper($latestSp->tingkat) }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
                <tr class="accordion-body" id="detail-{{ $s->id }}">
                    <td colspan="5" class="px-5 py-0 bg-gray-50">
                        <div class="accordion-content max-h-0 overflow-hidden transition-all duration-300">
                            <div class="py-4 space-y-4">
                                {{-- Detail Pelanggaran --}}
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Detail Pelanggaran</h4>
                                    <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                                        <thead>
                                            <tr class="bg-gray-100 text-gray-500 uppercase tracking-wider">
                                                <th class="text-left px-3 py-2 font-medium">Tanggal</th>
                                                <th class="text-left px-3 py-2 font-medium">Jenis</th>
                                                <th class="text-left px-3 py-2 font-medium">Poin</th>
                                                <th class="text-left px-3 py-2 font-medium">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse ($s->pelanggaran as $p)
                                            <tr>
                                                <td class="px-3 py-2 text-gray-600">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                                <td class="px-3 py-2 text-gray-900">{{ $p->jenis->nama }}</td>
                                                <td class="px-3 py-2 text-gray-900">{{ $p->jenis->poin }}</td>
                                                <td class="px-3 py-2 text-gray-500">{{ $p->keterangan ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Tidak ada pelanggaran dalam rentang filter</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Surat Teguran --}}
                                @if ($s->suratTeguran->isNotEmpty())
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Surat Teguran</h4>
                                    <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
                                        <thead>
                                            <tr class="bg-gray-100 text-gray-500 uppercase tracking-wider">
                                                <th class="text-left px-3 py-2 font-medium">Tingkat</th>
                                                <th class="text-left px-3 py-2 font-medium">Poin</th>
                                                <th class="text-left px-3 py-2 font-medium">Tanggal</th>
                                                <th class="text-left px-3 py-2 font-medium">Status WA</th>
                                                <th class="text-left px-3 py-2 font-medium">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach ($s->suratTeguran->sortBy('tingkat') as $sp)
                                            <tr>
                                                <td class="px-3 py-2">
                                                    @php $c = $colors[$sp->tingkat] ?? 'bg-gray-500'; @endphp
                                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-white {{ $c }}">{{ strtoupper($sp->tingkat) }}</span>
                                                </td>
                                                <td class="px-3 py-2 text-gray-900">{{ $sp->total_poin }}</td>
                                                <td class="px-3 py-2 text-gray-500">{{ $sp->tanggal_terbit }}</td>
                                                <td class="px-3 py-2">
                                                    @if ($sp->status_terkirim)
                                                        <span class="text-green-600 font-medium">✅ Terkirim</span>
                                                    @else
                                                        <span class="text-gray-400">Menunggu</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="flex items-center gap-2">
                                                        <a href="{{ route('teguran.show', $sp) }}" target="_blank"
                                                            class="text-purple-600 hover:text-purple-800 font-medium">Lihat PDF</a>
                                                        @if (!$sp->status_terkirim)
                                                            <button onclick="kirimWa({{ $sp->id }})"
                                                                class="text-green-600 hover:text-green-800 font-medium">📤 Kirim WA</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-500">Belum ada data pelanggaran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $daftarSiswa->links() }}
    </div>
</div>

@push('scripts')
<script>
function toggleAccordion(el) {
    const targetId = el.dataset.target;
    const body = document.getElementById(targetId);
    const content = body.querySelector('.accordion-content');
    const arrow = el.querySelector('.accordion-arrow');

    const isOpen = body.classList.contains('open');

    document.querySelectorAll('.accordion-body.open').forEach(b => {
        if (b.id !== targetId) {
            b.classList.remove('open');
            b.querySelector('.accordion-content').style.maxHeight = '0';
            const toggle = document.querySelector(`tr[data-target="${b.id}"]`);
            if (toggle) toggle.querySelector('.accordion-arrow').style.transform = 'rotate(0deg)';
        }
    });

    if (isOpen) {
        body.classList.remove('open');
        content.style.maxHeight = '0';
        arrow.style.transform = 'rotate(0deg)';
    } else {
        body.classList.add('open');
        content.style.maxHeight = content.scrollHeight + 'px';
        arrow.style.transform = 'rotate(90deg)';
    }
}

function kirimWa(id) {
    if (!confirm('Kirim notifikasi WA untuk surat teguran ini?')) return;

    fetch(`/surat-teguran/${id}/kirim-wa`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(res => res.json().then(data => ({ ok: res.ok, data })))
    .then(({ ok, data }) => {
        if (ok) {
            toast(data.message, 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            toast(data.message, 'error');
        }
    })
    .catch(() => toast('Gagal mengirim WA', 'error'));
}
</script>
@endpush
</x-app-layout>
