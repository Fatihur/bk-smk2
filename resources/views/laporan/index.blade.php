@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Laporan Kedisiplinan</h1>
    </div>

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6">
        <form method="GET" action="{{ route('laporan.cetak') }}" target="_blank" class="space-y-5">
            <div>
                <label class="block text-xs text-[#a1a1aa] mb-1">Siswa (opsional)</label>
                <select name="id_siswa" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]">
                    <option value="">Semua Siswa</option>
                    @foreach ($siswa as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} - {{ $s->kelas->nama_kelas ?? '-' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-[#a1a1aa] mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]">
                </div>
                <div>
                    <label class="block text-xs text-[#a1a1aa] mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="w-full bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]">
                </div>
            </div>
            <div>
                <button type="submit" class="px-6 py-2.5 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors inline-flex items-center gap-2">
                    <span>🖨️</span>
                    <span>Cetak PDF</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
