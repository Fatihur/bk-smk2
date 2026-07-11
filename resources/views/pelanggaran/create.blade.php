<x-app-layout>
<style>
.select2-container--classic .select2-selection--single {
    height: 38px;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    padding: 0.25rem 0.75rem;
}
.select2-container--classic .select2-selection--single:focus,
.select2-container--classic.select2-container--open .select2-selection--single {
    border-color: #9333ea;
    box-shadow: 0 0 0 2px #e9d5ff;
}
.select2-container--classic .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    color: #111827;
    font-size: 0.875rem;
}
.select2-container--classic .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-container--classic .select2-results__option--highlighted.select2-results__option--selectable {
    background: #9333ea;
}
.select2-container--classic .select2-results__option--selected {
    background: #f3e8ff;
    color: #7e22ce;
}
.select2-search--dropdown .select2-search__field:focus {
    border-color: #9333ea !important;
    box-shadow: 0 0 0 2px #e9d5ff !important;
}
.select2-container--classic .select2-dropdown {
    border-color: #d1d5db;
    border-radius: 0.5rem;
}
</style>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Input Pelanggaran</h1>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-lg">
        <form id="pelanggaranForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Siswa</label>
                <select id="id_siswa" class="w-full" required>
                    <option value=""></option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Pelanggaran</label>
                <select id="id_jenis" class="w-full" required>
                    <option value=""></option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" id="tanggal" value="{{ date('Y-m-d') }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea id="keterangan" rows="3"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
        <div id="notifikasi" class="mt-4 hidden"></div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#id_siswa').select2({
        theme: 'classic',
        width: '100%',
        placeholder: 'Cari siswa...',
        minimumInputLength: 1,
        ajax: {
            url: '{{ route("select2.siswa") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
        },
    });

    $('#id_jenis').select2({
        theme: 'classic',
        width: '100%',
        placeholder: 'Cari pelanggaran...',
        minimumInputLength: 0,
        ajax: {
            url: '{{ route("select2.jenis") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
        },
    });

    const form = document.getElementById('pelanggaranForm');
    const notifikasi = document.getElementById('notifikasi');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        fetch('/pelanggaran', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                id_siswa: document.getElementById('id_siswa').value,
                id_jenis: document.getElementById('id_jenis').value,
                tanggal: document.getElementById('tanggal').value,
                keterangan: document.getElementById('keterangan').value,
            }),
        })
        .then(res => res.json())
        .then(data => {
            notifikasi.className = 'mt-4 p-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700';
            notifikasi.textContent = data.message;
            notifikasi.classList.remove('hidden');
            form.reset();
            document.getElementById('tanggal').value = '{{ date('Y-m-d') }}';
        })
        .catch(err => {
            notifikasi.className = 'mt-4 p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
            notifikasi.textContent = 'Gagal menyimpan pelanggaran';
            notifikasi.classList.remove('hidden');
        });
    });
});
</script>
@endpush
</x-app-layout>