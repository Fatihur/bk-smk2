<x-guest-layout>
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="text-center mb-8">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-purple-400 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <span class="text-white text-lg font-bold">S2</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">SMKN 2 Sumbawa</h2>
                <p class="text-sm text-gray-500 mt-1">Sistem Monitoring Kedisiplinan</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition-colors"
                               placeholder="admin@smkn2.sch.id">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="password" type="password" name="password" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 focus:outline-none transition-colors"
                               placeholder="••••••••">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-gray-600">Ingat saya</span>
                        </label>
                    </div>
                    <button type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors text-sm">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>