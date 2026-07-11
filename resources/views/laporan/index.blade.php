<x-app-layout>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Laporan Kedisiplinan</h1>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="GET" action="{{ route('laporan.cetak') }}" target="_blank" class="space-y-5">
            <div>
                <label class="block text-xs text-gray-500 font-medium mb-1">Siswa (opsional)</label>
                <select name="id_siswa" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                    <option value="">Semua Siswa</option>
                    @foreach ($siswa as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }} - {{ $s->kelas->nama_kelas ?? '-' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 font-medium mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none">
                </div>
            </div>
            <div>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors inline-flex items-center gap-2">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>Cetak PDF</span>
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>