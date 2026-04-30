<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Sistema de Pasajes' }} - Cooperativa Ambato</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-800">
        <div class="min-h-screen bg-gray-100">
            @auth
                @include('components.sidebar-navigation')
            @endauth

            <div class="flex flex-col {{ auth()->check() ? 'sm:ml-64' : '' }}">
                <x-ui.navbar />

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <x-ui.alert />
                </div>

                @isset($header)
                    <header class="bg-gray-100 border-b border-[#003366]/10">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
