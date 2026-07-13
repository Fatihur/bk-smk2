@php
    $role = Auth::user()->role;
    $currentUrl = request()->path();

    $groups = [
        'dashboard' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'children' => []],
        'Data Master' => [
            'children' => [
                ['label' => 'Data Siswa', 'icon' => 'users', 'url' => '/data-siswa'],
            ],
        ],
        'Pelanggaran' => [
            'children' => [
                ['label' => 'Jenis Pelanggaran', 'icon' => 'warning', 'url' => '/jenis-pelanggaran'],
                ['label' => 'Pengaturan Poin', 'icon' => 'settings', 'url' => '/pengaturan-poin'],
                ['label' => 'Input Pelanggaran', 'icon' => 'edit', 'url' => '/pelanggaran/input'],
                ['label' => 'Riwayat', 'icon' => 'clipboard', 'url' => '/pelanggaran'],
            ],
        ],
        'Dokumen' => [
            'children' => [
                ['label' => 'Laporan', 'icon' => 'download', 'url' => '/laporan'],
            ],
        ],
        'WhatsApp' => [
            'children' => [
                ['label' => 'Pengaturan WhatsApp', 'icon' => 'bell', 'url' => '/pengaturan-whatsapp'],
            ],
        ],
    ];

    $kepsekOnly = ['/data-siswa', '/jenis-pelanggaran', '/pengaturan-poin', '/pelanggaran/input', '/pengaturan-whatsapp'];
    if ($role === 'kepala_sekolah') {
        foreach ($groups as $key => $g) {
            if ($key === 'dashboard') continue;
            $groups[$key]['children'] = array_values(array_filter($g['children'], fn($c) => !in_array($c['url'], $kepsekOnly)));
        }
        $groups = array_filter($groups, fn($g) => $g === 'dashboard' || count($g['children']));
    }
@endphp

<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-8 w-auto">
                <span class="font-semibold text-gray-900">SMKN 2 Sumbawa</span>
            </div>
            <div class="hidden md:flex items-center gap-1">
                @foreach ($groups as $key => $group)
                    @if (empty($group['children']))
                        @php $active = $currentUrl === 'dashboard'; @endphp
                        <a href="/dashboard"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $active ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            <x-icon :name="$group['icon']" class="w-4 h-4" />
                            {{ $group['label'] }}
                        </a>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                                <span>{{ $key }}</span>
                                <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                            </button>
                            <div x-show="open" @click.outside="open = false" class="absolute left-0 mt-1 min-w-full whitespace-nowrap bg-white rounded-xl border border-gray-200 shadow-lg py-1 z-50">
                                @foreach ($group['children'] as $item)
                                    @php $active = $currentUrl === ltrim($item['url'], '/'); @endphp
                                    <a href="{{ $item['url'] }}" @click="open = false"
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm {{ $active ? 'text-purple-700 bg-purple-50 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                                        <x-icon :name="$item['icon']" class="w-4 h-4" />
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 hidden sm:block">{{ Auth::user()->nama }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <x-icon name="logout" class="w-4 h-4" />
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>