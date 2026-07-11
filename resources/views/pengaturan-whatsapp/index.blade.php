<x-app-layout>
    <div x-data="whatsappSettings()" x-init="init()" class="space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengaturan WhatsApp</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola koneksi WhatsApp untuk notifikasi surat teguran</p>
            </div>
            <span x-show="loading" class="text-sm text-gray-500">Memuat...</span>
        </div>

        {{-- Status Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Status Sidecar</p>
                <div class="flex items-center gap-2">
                    <span x-show="sidecar?.running" class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span x-show="!sidecar?.running" class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="text-gray-900 text-sm font-medium" x-text="sidecar?.running ? 'Running' : 'Offline'"></span>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-show="!sidecar?.running">Jalankan: php artisan whatsapp:sidecar:start</p>
            </div>

            <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-2">Status Session</p>
                <div class="flex items-center gap-2">
                    <span x-show="session?.status === 'ready'" class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span x-show="session?.status === 'qr' || session?.status === 'initializing'" class="w-2 h-2 rounded-full bg-yellow-500"></span>
                    <span x-show="session?.status === 'disconnected' || session?.status === 'auth_failure' || session?.status === 'error'" class="w-2 h-2 rounded-full bg-red-500"></span>
                    <span class="text-gray-900 text-sm font-medium" x-text="labelStatus(session?.status)"></span>
                </div>
            </div>
        </div>

        {{-- QR Code Area --}}
        <div x-show="session?.status === 'qr'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex flex-col items-center">
                <p class="text-sm text-gray-500 mb-4">Scan QR ini dengan WhatsApp Anda</p>
                <div class="bg-white p-3 rounded-lg border border-gray-200 mb-4">
                    <img :src="session.qr" alt="QR Code" class="w-64 h-64">
                </div>
                <div class="text-xs text-gray-500 text-center space-y-1">
                    <p>1. Buka WhatsApp di HP</p>
                    <p>2. Tap titik tiga (menu) &gt; Perangkat Tertaut</p>
                    <p>3. Tap "Tautkan Perangkat"</p>
                    <p>4. Arahkan kamera ke QR ini</p>
                </div>
            </div>
        </div>

        {{-- Ready / Connected Info --}}
        <div x-show="session?.status === 'ready' || session?.status === 'authenticated'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Info Session</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Session ID:</span>
                    <span class="text-gray-900 ml-2" x-text="session?.id"></span>
                </div>
                <div>
                    <span class="text-gray-500">Nomor:</span>
                    <span class="text-gray-900 ml-2" x-text="session?.phone_number || '-'"></span>
                </div>
                <div>
                    <span class="text-gray-500">Nama:</span>
                    <span class="text-gray-900 ml-2" x-text="session?.push_name || '-'"></span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="text-green-600 ml-2 font-medium">Tersambung</span>
                </div>
            </div>
        </div>

        {{-- Initializing --}}
        <div x-show="session?.status === 'initializing'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-center">
            <p class="text-sm text-gray-500">Memulai session...</p>
            <div class="mt-2 inline-block w-6 h-6 border-2 border-purple-600 border-t-transparent rounded-full animate-spin"></div>
        </div>

        {{-- Disconnected / Error --}}
        <div x-show="session?.status === 'disconnected' || session?.status === 'auth_failure' || session?.status === 'error'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <p class="text-sm text-red-600 font-medium">
                <span x-text="session?.status === 'disconnected' ? 'Session terputus.' : (session?.status === 'auth_failure' ? 'Gagal autentikasi. Pairing ulang.' : 'Terjadi kesalahan.')"></span>
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 flex-wrap" x-show="sidecar?.running">
            <button x-show="!session?.status || session?.status === 'disconnected' || session?.status === 'auth_failure' || session?.status === 'error'"
                    @click="startPairing"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                Mulai Pairing
            </button>

            <button x-show="session?.status === 'ready' || session?.status === 'authenticated'"
                    @click="stopSession"
                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                Stop Session
            </button>

            <button x-show="session?.status === 'ready' || session?.status === 'authenticated'"
                    @click="destroySession"
                    class="bg-red-100 hover:bg-red-200 text-red-700 px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                Hapus Session
            </button>
        </div>

        {{-- Sidecar offline notice --}}
        <div x-show="!sidecar?.running" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <p class="text-sm text-yellow-700 font-medium">Sidecar tidak berjalan.</p>
            <p class="text-xs text-gray-500 mt-2">Jalankan di terminal: <code class="text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">php artisan whatsapp:sidecar:start</code></p>
        </div>
    </div>

    @push('scripts')
    <script>
        function whatsappSettings() {
            return {
                sidecar: null,
                session: null,
                loading: true,
                pollingTimer: null,

                init() {
                    this.fetchStatus();
                },

                fetchStatus() {
                    fetch('/api/whatsapp/status')
                        .then(r => r.json())
                        .then(d => {
                            this.sidecar = d.sidecar;
                            this.session = d.session;
                            this.loading = false;

                            if (this.pollingTimer) clearTimeout(this.pollingTimer);

                            if (this.session?.status === 'initializing' || this.session?.status === 'qr') {
                                this.pollingTimer = setTimeout(() => this.fetchStatus(), 3000);
                            }
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                },

                startPairing() {
                    this.loading = true;
                    fetch('/api/whatsapp/start', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                this.session = d.session;
                                this.pollingTimer = setTimeout(() => this.fetchStatus(), 2000);
                            }
                            this.loading = false;
                        })
                        .catch(() => { this.loading = false; });
                },

                stopSession() {
                    if (!confirm('Hentikan session? Autentikasi tetap tersimpan.')) return;
                    fetch('/api/whatsapp/stop', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                        .then(r => r.json())
                        .then(() => this.fetchStatus());
                },

                destroySession() {
                    if (!confirm('Hapus session? Anda harus pairing ulang nantinya.')) return;
                    fetch('/api/whatsapp/destroy', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                        .then(r => r.json())
                        .then(() => this.fetchStatus());
                },

                labelStatus(status) {
                    const labels = {
                        'initializing': 'Initializing...',
                        'qr': 'Menunggu Scan QR',
                        'authenticated': 'Terautentikasi',
                        'ready': 'Tersambung',
                        'disconnected': 'Terputus',
                        'auth_failure': 'Gagal Autentikasi',
                        'error': 'Error',
                    };
                    return labels[status] || status || '-';
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
