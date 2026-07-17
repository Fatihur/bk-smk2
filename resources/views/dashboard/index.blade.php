<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Siswa</p>
                        <p id="stat-total-siswa" class="text-3xl font-bold text-gray-900 mt-2">-</p>
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
                        <p id="stat-pelanggaran-bulan" class="text-3xl font-bold text-gray-900 mt-2">-</p>
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
                        <p id="stat-surat-teguran" class="text-3xl font-bold text-gray-900 mt-2">-</p>
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
                        <p id="stat-poin-tertinggi" class="text-3xl font-bold text-gray-900 mt-2">-</p>
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
                <tbody id="recent-violations">
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                document.getElementById('stat-total-siswa').textContent = data.total_siswa ?? '-';
                document.getElementById('stat-pelanggaran-bulan').textContent = data.total_pelanggaran ?? '-';
                document.getElementById('stat-surat-teguran').textContent = data.total_teguran ?? '-';
                document.getElementById('stat-poin-tertinggi').textContent = data.poin_tertinggi ?? '-';

                const tbody = document.getElementById('recent-violations');
                if (data.terbaru && data.terbaru.length) {
                    tbody.innerHTML = data.terbaru.map(v => `
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-gray-900">${v.siswa}</td>
                            <td class="px-5 py-3.5 text-gray-500">${v.kelas}</td>
                            <td class="px-5 py-3.5 text-gray-500">${v.jenis}</td>
                            <td class="px-5 py-3.5 text-gray-500">-</td>
                            <td class="px-5 py-3.5 text-gray-500">${v.tanggal}</td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-gray-500">Belum ada data pelanggaran</td></tr>`;
                }
            })
            .catch(() => {
                document.getElementById('recent-violations').innerHTML = `<tr><td colspan="5" class="text-center py-8 text-gray-500">Gagal memuat data</td></tr>`;
            });
    </script>
    @endpush
</x-app-layout>