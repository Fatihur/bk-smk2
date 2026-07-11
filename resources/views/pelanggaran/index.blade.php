@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Riwayat Pelanggaran</h1>
        @if (Auth::user()->role === 'guru_bk')
        <a href="{{ route('pelanggaran.input') }}" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">+ Input Pelanggaran</a>
        @endif
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-4 mb-6">
        <form method="GET" action="{{ route('pelanggaran.riwayat') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-[#a1a1aa] mb-1">Siswa</label>
                <select name="id_siswa" class="bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]">
                    <option value="">Semua</option>
                    @foreach ($siswa as $s)
                    <option value="{{ $s->id }}" {{ request('id_siswa') == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-[#a1a1aa] mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]">
            </div>
            <div>
                <label class="block text-xs text-[#a1a1aa] mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Filter</button>
                <a href="{{ route('pelanggaran.riwayat') }}" class="px-4 py-2 bg-[#1c1c22] text-[#a1a1aa] rounded-lg text-sm hover:text-[#fafafa] transition-colors">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[#71717a] border-b border-[#1c1c22]">
                    <th class="text-left py-3 px-4">Siswa</th>
                    <th class="text-left py-3 px-4">Kelas</th>
                    <th class="text-left py-3 px-4">Pelanggaran</th>
                    <th class="text-left py-3 px-4">Poin</th>
                    <th class="text-left py-3 px-4">Tanggal</th>
                    <th class="text-left py-3 px-4">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pelanggaran as $p)
                <tr class="border-b border-[#1c1c22] hover:bg-[#1c1c22]/50">
                    <td class="py-3 px-4 text-[#fafafa]">{{ $p->siswa->nama }}</td>
                    <td class="py-3 px-4 text-[#a1a1aa]">{{ $p->siswa->kelas->tingkat }} {{ $p->siswa->kelas->nama_kelas }}</td>
                    <td class="py-3 px-4 text-[#fafafa]">{{ $p->jenis->nama }}</td>
                    <td class="py-3 px-4 text-[#fafafa]">{{ $p->jenis->poin }}</td>
                    <td class="py-3 px-4 text-[#a1a1aa]">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-[#71717a]">{{ $p->keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-[#71717a]">Belum ada data pelanggaran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pelanggaran->links() }}
    </div>
</div>
@endsection
