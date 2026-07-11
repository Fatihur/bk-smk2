<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-[#fafafa]">Surat Teguran</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4">
            <div class="bg-[#18181b] border border-[#27272a] rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#27272a] text-[#a1a1aa] text-left">
                                <th class="px-4 py-3 font-medium">Siswa</th>
                                <th class="px-4 py-3 font-medium">Kelas</th>
                                <th class="px-4 py-3 font-medium">Tingkat</th>
                                <th class="px-4 py-3 font-medium">Total Poin</th>
                                <th class="px-4 py-3 font-medium">Tanggal</th>
                                <th class="px-4 py-3 font-medium">Status WA</th>
                                <th class="px-4 py-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#27272a]">
                            @forelse ($teguran as $t)
                                <tr class="text-[#e4e4e7]">
                                    <td class="px-4 py-3">{{ $t->siswa->nama }}</td>
                                    <td class="px-4 py-3">{{ $t->siswa->kelas->nama_kelas }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $colors = ['SP1' => 'bg-blue-600', 'SP2' => 'bg-yellow-500', 'SP3' => 'bg-red-600'];
                                            $color = $colors[$t->tingkat] ?? 'bg-gray-500';
                                        @endphp
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold text-white {{ $color }}">
                                            {{ $t->tingkat }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $t->total_poin }}</td>
                                    <td class="px-4 py-3">{{ $t->tanggal_terbit }}</td>
                                    <td class="px-4 py-3">
                                        @if ($t->status_terkirim)
                                            <span class="text-green-500 font-medium">Terkirim</span>
                                        @else
                                            <span class="text-gray-400 font-medium">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ asset('storage/' . $t->file_pdf) }}" target="_blank"
                                           class="text-[#0C5CAB] hover:underline font-medium">
                                            Lihat PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-[#71717a]">Belum ada surat teguran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($teguran->hasPages())
                    <div class="px-4 py-3 border-t border-[#27272a]">
                        {{ $teguran->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
