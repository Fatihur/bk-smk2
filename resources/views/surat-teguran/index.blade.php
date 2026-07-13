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
                                        <div class="flex items-center gap-2">
                                            <a href="{{ asset('storage/' . $t->file_pdf) }}" target="_blank"
                                               class="text-purple-600 hover:text-purple-700 font-medium text-xs">
                                                Lihat PDF
                                            </a>
                                            @if (!$t->status_terkirim)
                                                <button onclick="kirimWa({{ $t->id }})"
                                                        class="text-green-600 hover:text-green-700 font-medium text-xs">
                                                    Kirim WA
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
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(() => alert('Gagal mengirim WA'));
    }
    </script>
    @endpush
</x-app-layout>
