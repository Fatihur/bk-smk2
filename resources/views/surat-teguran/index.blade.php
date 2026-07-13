<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">Surat Teguran</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-500 text-xs uppercase tracking-wider bg-gray-50">
                                <th class="px-5 py-3 font-medium text-left">Siswa</th>
                                <th class="px-5 py-3 font-medium text-left">Rombel</th>
                                <th class="px-5 py-3 font-medium text-left">Tingkat</th>
                                <th class="px-5 py-3 font-medium text-left">Total Poin</th>
                                <th class="px-5 py-3 font-medium text-left">Tanggal</th>
                                <th class="px-5 py-3 font-medium text-left">No. Wali</th>
                                <th class="px-5 py-3 font-medium text-left">Status WA</th>
                                <th class="px-5 py-3 font-medium text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($teguran as $t)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3.5 text-gray-900">{{ $t->siswa->nama_siswa }}</td>
                                    <td class="px-5 py-3.5 text-gray-500">{{ $t->siswa->rombel }}</td>
                                    <td class="px-5 py-3.5">
                                        @php
                                            $colors = ['SP1' => 'bg-blue-600', 'SP2' => 'bg-yellow-500', 'SP3' => 'bg-red-600'];
                                            $color = $colors[$t->tingkat] ?? 'bg-gray-500';
                                        @endphp
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-white {{ $color }}">
                                            {{ $t->tingkat }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-900">{{ $t->total_poin }}</td>
                                    <td class="px-5 py-3.5 text-gray-500">{{ $t->tanggal_terbit }}</td>
                                    <td class="px-5 py-3.5">
                                        @if ($t->siswa->no_wali)
                                            <span class="text-green-600 font-medium">{{ $t->siswa->no_wali }}</span>
                                        @else
                                            <span class="text-red-400 italic text-xs">Belum diisi</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($t->status_terkirim)
                                            <span class="text-green-600 font-medium">Terkirim</span>
                                        @else
                                            <span class="text-gray-400 font-medium">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ asset('storage/' . $t->file_pdf) }}" target="_blank"
                                               class="p-1.5 rounded-lg text-gray-500 hover:text-purple-600 hover:bg-purple-50 transition-colors" title="Lihat PDF">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            </a>
                                            @if (!$t->status_terkirim)
                                                <button onclick="kirimWa({{ $t->id }})"
                                                        class="p-1.5 rounded-lg text-gray-500 hover:text-green-600 hover:bg-green-50 transition-colors" title="Kirim WA">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-gray-500">Belum ada surat teguran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($teguran->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $teguran->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
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
