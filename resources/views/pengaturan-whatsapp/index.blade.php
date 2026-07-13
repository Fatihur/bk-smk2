<x-app-layout>
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan WhatsApp</h1>

    {{-- Status Card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Koneksi</h2>
        <div id="connectionStatus" class="flex items-center gap-2 mb-4">
            <div class="w-3 h-3 rounded-full bg-gray-400" id="statusDot"></div>
            <span class="text-sm text-gray-600" id="statusText">Memeriksa...</span>
        </div>

        {{-- QR Code --}}
        <div id="qrContainer" class="hidden flex flex-col items-center mb-4">
            <p class="text-sm text-gray-500 mb-3">Scan QR ini dengan WhatsApp Anda</p>
            <div class="bg-white p-3 rounded-lg border border-gray-200">
                <img id="qrImage" src="" alt="QR Code" class="w-64 h-64">
            </div>
            <div class="text-xs text-gray-500 mt-3 text-center space-y-1">
                <p>1. Buka WhatsApp di HP</p>
                <p>2. Tap titik tiga (menu) &gt; Perangkat Tertaut</p>
                <p>3. Tap "Tautkan Perangkat"</p>
                <p>4. Arahkan kamera ke QR ini</p>
            </div>
        </div>

        {{-- Info --}}
        <div id="infoContainer" class="hidden text-sm space-y-1 mb-4">
            <p><span class="text-gray-500">Nomor:</span> <span class="text-gray-900 font-medium" id="phoneNumber">-</span></p>
            <p><span class="text-gray-500">Nama:</span> <span class="text-gray-900 font-medium" id="pushName">-</span></p>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <button id="btnStart" onclick="startConnection()"
                class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                Mulai Koneksi
            </button>
            <button id="btnLogout" onclick="if(confirm('Hapus session? Anda harus scan ulang nantinya.')){fetch('{{ route('whatsapp.logout') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload())}"
                class="px-4 py-2 bg-red-100 text-red-700 text-sm font-medium rounded-lg hover:bg-red-200 transition-colors hidden">
                Hapus Session
            </button>
        </div>
    </div>

    {{-- Test Send --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Kirim Pesan</h2>
        <form id="testForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Target</label>
                <input type="text" id="target" value="087758962661"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"
                    placeholder="08xxxxxxxxxx">
            </div>
            <button type="submit" id="btnTest"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                Kirim Test
            </button>
        </form>
        <div id="testResult" class="mt-3 text-sm"></div>
    </div>
</div>

@push('scripts')
<script>
let pollingTimer = null;

function fetchStatus() {
    fetch('{{ route("whatsapp.status") }}')
        .then(r => r.json())
        .then(d => updateUI(d))
        .catch(() => {});
}

function updateUI(data) {
    const dot = document.getElementById('statusDot');
    const text = document.getElementById('statusText');
    const qrContainer = document.getElementById('qrContainer');
    const qrImage = document.getElementById('qrImage');
    const infoContainer = document.getElementById('infoContainer');
    const phoneNumber = document.getElementById('phoneNumber');
    const pushName = document.getElementById('pushName');
    const btnStart = document.getElementById('btnStart');
    const btnLogout = document.getElementById('btnLogout');

    const status = data.status;
    document.getElementById('connectionStatus').classList.remove('hidden');

    if (status === 'connected') {
        dot.className = 'w-3 h-3 rounded-full bg-green-500';
        text.textContent = 'Tersambung';
        qrContainer.classList.add('hidden');
        infoContainer.classList.remove('hidden');
        phoneNumber.textContent = data.phone || '-';
        pushName.textContent = data.pushName || '-';
        btnStart.classList.add('hidden');
        btnLogout.classList.remove('hidden');
        if (pollingTimer) { clearTimeout(pollingTimer); pollingTimer = null; }
    } else if (status === 'qr') {
        dot.className = 'w-3 h-3 rounded-full bg-yellow-500';
        text.textContent = 'Menunggu scan QR';
        qrContainer.classList.remove('hidden');
        qrImage.src = data.qr;
        infoContainer.classList.add('hidden');
        btnStart.classList.add('hidden');
        btnLogout.classList.add('hidden');
        if (!pollingTimer) pollingTimer = setTimeout(() => fetchStatus(), 3000);
    } else if (status === 'connecting') {
        dot.className = 'w-3 h-3 rounded-full bg-yellow-500';
        text.textContent = 'Menghubungkan...';
        qrContainer.classList.add('hidden');
        infoContainer.classList.add('hidden');
        btnStart.classList.add('hidden');
        btnLogout.classList.add('hidden');
        if (!pollingTimer) pollingTimer = setTimeout(() => fetchStatus(), 2000);
    } else {
        dot.className = 'w-3 h-3 rounded-full bg-red-500';
        text.textContent = 'Terputus';
        qrContainer.classList.add('hidden');
        infoContainer.classList.add('hidden');
        btnStart.classList.remove('hidden');
        btnLogout.classList.add('hidden');
        if (pollingTimer) { clearTimeout(pollingTimer); pollingTimer = null; }
    }
}

function startConnection() {
    const btn = document.getElementById('btnStart');
    btn.disabled = true;
    btn.textContent = 'Memulai...';

    fetch('{{ route("whatsapp.start") }}')
        .then(r => r.json())
        .then(d => {
            updateUI(d);
            if (d.status === 'qr') {
                if (!pollingTimer) pollingTimer = setTimeout(() => fetchStatus(), 3000);
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Mulai Koneksi';
        });
}

document.getElementById('testForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnTest');
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = 'Mengirim...';
    result.innerHTML = '';

    fetch('{{ route("whatsapp.test-send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ target: document.getElementById('target').value }),
    })
    .then(res => res.json().then(d => ({ ok: res.ok, data: d })))
    .then(({ ok, data }) => {
        result.innerHTML = ok
            ? '<span class="text-green-600">' + data.message + '</span>'
            : '<span class="text-red-600">' + data.message + '</span>';
    })
    .catch(() => {
        result.innerHTML = '<span class="text-red-600">Gagal menghubungi server</span>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Kirim Test';
    });
});

fetchStatus();
</script>
@endpush
</x-app-layout>
