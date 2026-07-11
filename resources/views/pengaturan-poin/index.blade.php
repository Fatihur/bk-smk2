@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-[#fafafa]">Pengaturan Poin</h1>
    </div>

    @if (session('success'))
    <div class="mb-4 p-3 bg-green-900/30 border border-green-700/50 rounded-lg text-green-300 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-[#121215] border border-[#1c1c22] rounded-xl p-6">
        <form method="POST" action="{{ route('pengaturan-poin.update') }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                @foreach ($pengaturan as $p)
                <div class="flex items-center gap-4">
                    <label class="text-[#fafafa] font-medium w-20 text-sm">{{ strtoupper($p->tingkat) }}</label>
                    <input type="number" name="batas[{{ $p->id }}]" value="{{ $p->batas_poin }}"
                        class="flex-1 bg-[#09090b] border border-[#1c1c22] rounded-lg px-3 py-2 text-[#fafafa] text-sm focus:outline-none focus:border-[#0C5CAB]" required min="1">
                    <span class="text-[#71717a] text-sm">poin</span>
                </div>
                @endforeach
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[#0C5CAB] text-[#fafafa] rounded-lg text-sm hover:bg-[#0a4a8a] transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
