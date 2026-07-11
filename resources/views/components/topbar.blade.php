@php
    $role = Auth::user()->role;
    $currentUrl = request()->path();

    $guruBkMenu = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => '/dashboard'],
        ['label' => 'Data Siswa', 'icon' => 'users', 'url' => '/data-siswa'],
        ['label' => 'Data Orang Tua', 'icon' => 'user-group', 'url' => '/data-orang-tua'],
        ['label' => 'Data Kelas', 'icon' => 'academic', 'url' => '/data-kelas'],
        ['label' => 'Jenis Pelanggaran', 'icon' => 'warning', 'url' => '/jenis-pelanggaran'],
        ['label' => 'Pengaturan Poin', 'icon' => 'settings', 'url' => '/pengaturan-poin'],
        ['label' => 'Input Pelanggaran', 'icon' => 'edit', 'url' => '/pelanggaran/input'],
        ['label' => 'Riwayat', 'icon' => 'clipboard', 'url' => '/pelanggaran'],
        ['label' => 'Surat Teguran', 'icon' => 'bell', 'url' => '/surat-teguran'],
        ['label' => 'Laporan', 'icon' => 'download', 'url' => '/laporan'],
    ];

    $kepsekMenu = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => '/dashboard'],
        ['label' => 'Riwayat', 'icon' => 'clipboard', 'url' => '/pelanggaran'],
        ['label' => 'Laporan', 'icon' => 'download', 'url' => '/laporan'],
    ];

    $menu = $role === 'kepala_sekolah' ? $kepsekMenu : $guruBkMenu;
@endphp

<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-purple-400 rounded-lg flex items-center justify-center">
                        <span class="text-white text-xs font-bold">S2</span>
                    </div>
                    <span class="font-semibold text-gray-900 hidden sm:block">SMKN 2 Sumbawa</span>
                </div>
                <div class="hidden md:flex items-center gap-1">
                    @foreach ($menu as $item)
                        @php
                            $isActive = $currentUrl === ltrim($item['url'], '/');
                            $isActive = $isActive || ($currentUrl === '/' && $item['url'] === '/dashboard');
                            $isActive = $isActive || ($item['url'] !== '/dashboard' && $currentUrl !== '' && str_starts_with($currentUrl, ltrim($item['url'], '/')));
                        @endphp
                        <a href="{{ $item['url'] }}"
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                  {{ $isActive ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            <x-icon :name="$item['icon']" class="w-4 h-4" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
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

<div class="md:hidden border-b border-gray-200 bg-white">
    <div class="flex overflow-x-auto gap-1 px-4 py-2 scrollbar-hide">
        @foreach ($menu as $item)
            @php
                $isActive = $currentUrl === ltrim($item['url'], '/');
            @endphp
            <a href="{{ $item['url'] }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                      {{ $isActive ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-100' }}">
                <x-icon :name="$item['icon']" class="w-3.5 h-3.5" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>