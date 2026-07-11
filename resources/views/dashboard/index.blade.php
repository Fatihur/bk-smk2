<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-[#fafafa] mb-6">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#71717a] text-sm">Total Siswa</p>
                        <p id="stat-total-siswa" class="text-2xl font-bold text-[#fafafa] mt-1">-</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#0C5CAB]/20 flex items-center justify-center text-2xl">👥</div>
                </div>
            </div>
            <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#71717a] text-sm">Pelanggaran Bulan Ini</p>
                        <p id="stat-pelanggaran-bulan" class="text-2xl font-bold text-[#fafafa] mt-1">-</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#0C5CAB]/20 flex items-center justify-center text-2xl">⚠️</div>
                </div>
            </div>
            <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#71717a] text-sm">Surat Teguran Terbit</p>
                        <p id="stat-surat-teguran" class="text-2xl font-bold text-[#fafafa] mt-1">-</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#0C5CAB]/20 flex items-center justify-center text-2xl">📄</div>
                </div>
            </div>
            <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[#71717a] text-sm">Poin Tertinggi</p>
                        <p id="stat-poin-tertinggi" class="text-2xl font-bold text-[#fafafa] mt-1">-</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-[#0C5CAB]/20 flex items-center justify-center text-2xl">🏆</div>
                </div>
            </div>
        </div>

        <div class="bg-[#121215] border border-[#1c1c22] rounded-xl">
            <div class="px-5 py-4 border-b border-[#1c1c22]">
                <h2 class="text-lg font-semibold text-[#fafafa]">Riwayat Pelanggaran Terbaru</h2>
            </div>
            <div class="p-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[#71717a] border-b border-[#1c1c22]">
                            <th class="text-left py-3 px-2">Siswa</th>
                            <th class="text-left py-3 px-2">Kelas</th>
                            <th class="text-left py-3 px-2">Pelanggaran</th>
                            <th class="text-left py-3 px-2">Poin</th>
                            <th class="text-left py-3 px-2">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody id="recent-violations">
                        <tr>
                            <td colspan="5" class="text-center py-8 text-[#71717a]">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                document.getElementById('stat-total-siswa').textContent = data.total_siswa ?? '-';
                document.getElementById('stat-pelanggaran-bulan').textContent = data.pelanggaran_bulan_ini ?? '-';
                document.getElementById('stat-surat-teguran').textContent = data.surat_teguran_terbit ?? '-';
                document.getElementById('stat-poin-tertinggi').textContent = data.poin_tertinggi ?? '-';

                const tbody = document.getElementById('recent-violations');
                if (data.recent_violations && data.recent_violations.length) {
                    tbody.innerHTML = data.recent_violations.map(v => `
                        <tr class="border-b border-[#1c1c22] hover:bg-[#1c1c22]/50">
                            <td class="py-3 px-2 text-[#fafafa]">${v.siswa}</td>
                            <td class="py-3 px-2 text-[#a1a1aa]">${v.kelas}</td>
                            <td class="py-3 px-2 text-[#a1a1aa]">${v.pelanggaran}</td>
                            <td class="py-3 px-2 text-[#a1a1aa]">${v.poin}</td>
                            <td class="py-3 px-2 text-[#a1a1aa]">${v.tanggal}</td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-[#71717a]">Belum ada data pelanggaran</td></tr>`;
                }
            })
            .catch(() => {
                document.getElementById('recent-violations').innerHTML = `<tr><td colspan="5" class="text-center py-8 text-[#71717a]">Gagal memuat data</td></tr>`;
            });
    </script>
    @endpush
</x-app-layout>
