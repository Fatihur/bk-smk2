<x-app-layout>
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Pengaturan WhatsApp</h1>

    {{-- Token Form --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Token Fonnte</h2>
        <form method="POST" action="{{ route('whatsapp.token') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Token</label>
                <input type="text" name="token" value="{{ $token }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono"
                    placeholder="Masukkan token Fonnte">
                <p class="text-xs text-gray-500 mt-1">Dapatkan token dari <a href="https://fonnte.com" class="text-purple-600 underline" target="_blank">fonnte.com</a></p>
            </div>
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
                Simpan Token
            </button>
        </form>
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

    {{-- Status --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Status</h2>
        @if ($token)
            <p class="text-sm text-green-600">Token terkonfigurasi</p>
        @else
            <p class="text-sm text-red-500">Token belum diisi</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
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
</script>
@endpush
</x-app-layout>
