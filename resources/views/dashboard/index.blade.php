<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Siswa</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalSiswa }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                        <x-icon name="users" class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Pelanggaran Bulan Ini</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPelanggaran }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                        <x-icon name="warning" class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Surat Teguran Terbit</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalTeguran }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                        <x-icon name="bell" class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Poin Tertinggi</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $poinTertinggi }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                        <x-icon name="dashboard" class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">10 Pelanggaran Terakhir</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                        <th class="text-left px-5 py-3 font-medium">Siswa</th>
                        <th class="text-left px-5 py-3 font-medium">Kelas</th>
                        <th class="text-left px-5 py-3 font-medium">Pelanggaran</th>
                        <th class="text-left px-5 py-3 font-medium">Poin</th>
                        <th class="text-left px-5 py-3 font-medium">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($terbaru as $v)
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-gray-900">{{ $v['siswa'] }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $v['kelas'] }}</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $v['jenis'] }}</td>
                            <td class="px-5 py-3.5 text-gray-500">-</td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $v['tanggal'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">Belum ada data pelanggaran</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
