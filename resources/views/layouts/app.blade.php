<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css">
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        @auth
            <x-topbar />
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {{ $slot }}
            </main>
        @endauth

        @guest
            <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-purple-50 to-white">
                {{ $slot }}
            </div>
        @endguest
        <x-toast />
        @if (session('success'))
            <script>document.addEventListener('DOMContentLoaded', () => toast({{ json_encode(session('success')) }}, 'success'));</script>
        @elseif (session('error'))
            <script>document.addEventListener('DOMContentLoaded', () => toast({{ json_encode(session('error')) }}, 'error'));</script>
        @elseif (session('warning'))
            <script>document.addEventListener('DOMContentLoaded', () => toast({{ json_encode(session('warning')) }}, 'warning'));</script>
        @elseif (session('status') === 'profile-updated')
            <script>document.addEventListener('DOMContentLoaded', () => toast('Profil berhasil diperbarui', 'success'));</script>
        @elseif (session('status') === 'password-updated')
            <script>document.addEventListener('DOMContentLoaded', () => toast('Kata sandi berhasil diperbarui', 'success'));</script>
        @endif
        @stack('scripts')
    </body>
</html>