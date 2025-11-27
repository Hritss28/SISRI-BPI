<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SISRI') }} - Sistem Informasi Skripsi</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-white p-4">
            {{ $slot }}
        </div>
        
        <!-- Footer -->
        <div class="fixed bottom-0 left-0 right-0 text-center py-3 text-sm text-gray-500 bg-white/80">
            Copyright © {{ date('Y') }} FT-UTM. All rights reserved.
        </div>
    </body>
</html>
