@php
    $role = Auth::user()->role;
    $currentUrl = request()->path();

    $guruBkMenu = [
        ['label' => 'Dashboard', 'icon' => '📊', 'url' => '/dashboard'],
        ['label' => 'Data Siswa', 'icon' => '👥', 'url' => '/data-siswa'],
        ['label' => 'Jenis Pelanggaran', 'icon' => '⚠️', 'url' => '/jenis-pelanggaran'],
        ['label' => 'Pengaturan Poin', 'icon' => '⚙️', 'url' => '/pengaturan-poin'],
        ['label' => 'Input Pelanggaran', 'icon' => '📝', 'url' => '/pelanggaran/input'],
        ['label' => 'Riwayat Pelanggaran', 'icon' => '📋', 'url' => '/pelanggaran'],
        ['label' => 'Laporan', 'icon' => '📈', 'url' => '/laporan'],
    ];

    $kepsekMenu = [
        ['label' => 'Dashboard', 'icon' => '📊', 'url' => '/dashboard'],
        ['label' => 'Riwayat Pelanggaran', 'icon' => '📋', 'url' => '/pelanggaran'],
        ['label' => 'Laporan', 'icon' => '📈', 'url' => '/laporan'],
    ];

    $menu = $role === 'kepala_sekolah' ? $kepsekMenu : $guruBkMenu;
@endphp

<aside class="w-64 min-h-screen bg-[#0c0c0f] border-r border-[#1c1c22] flex flex-col">
    <div class="p-5 border-b border-[#1c1c22] flex items-center gap-3">
        <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-9 w-auto rounded">
        <div>
            <h1 class="text-[#fafafa] text-lg font-semibold tracking-tight">SMKN 2 Sumbawa</h1>
            <p class="text-[#71717a] text-xs mt-0.5">Sistem Disiplin Siswa</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @foreach ($menu as $item)
            @php
                $isActive = $currentUrl === ltrim($item['url'], '/') || request()->routeIs(ltrim($item['url'], '/'));
                $isActive = $isActive || ($currentUrl === '/' && $item['url'] === '/dashboard');
                $isActive = $isActive || ($item['url'] !== '/dashboard' && str_starts_with($currentUrl, ltrim($item['url'], '/')));
            @endphp
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors duration-150
                      {{ $isActive ? 'bg-[#0C5CAB] text-[#fafafa]' : 'text-[#a1a1aa] hover:text-[#fafafa] hover:bg-[#1c1c22]' }}">
                <span class="text-base">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="p-4 border-t border-[#1c1c22]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm text-[#a1a1aa] hover:text-[#fafafa] hover:bg-[#1c1c22] transition-colors duration-150">
                <span>🚪</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
