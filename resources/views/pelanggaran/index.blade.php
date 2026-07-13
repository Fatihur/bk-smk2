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

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                    <th class="text-left px-5 py-3 font-medium">Siswa</th>
                    <th class="text-left px-5 py-3 font-medium">Rombel</th>
                    <th class="text-left px-5 py-3 font-medium">Pelanggaran</th>
                    <th class="text-left px-5 py-3 font-medium">Poin</th>
                    <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                    <th class="text-left px-5 py-3 font-medium">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pelanggaran as $p)
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="px-5 py-3.5 text-gray-900">{{ $p->siswa->nama_siswa }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $p->siswa->rombel }}</td>
                    <td class="px-5 py-3.5 text-gray-900">{{ $p->jenis->nama }}</td>
                    <td class="px-5 py-3.5 text-gray-900">{{ $p->jenis->poin }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $p->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">Belum ada data pelanggaran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pelanggaran->links() }}
    </div>
</div>
</x-app-layout>