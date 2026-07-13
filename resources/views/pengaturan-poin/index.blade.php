<x-app-layout>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Poin</h1>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('pengaturan-poin.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                @foreach ($pengaturan as $p)
                <div class="flex items-center gap-4">
                    <label class="text-gray-900 font-medium w-20 text-sm">{{ strtoupper($p->tingkat) }}</label>
                    <input type="number" name="batas[{{ $p->id }}]" value="{{ $p->batas_poin }}"
                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none" required min="1">
                    <span class="text-gray-500 text-sm">poin</span>
                </div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>