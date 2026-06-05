<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Portal Web | {{ config('app.name', 'Cooperativa Ambato') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">

        <nav class="bg-white shadow border-b border-[#003366]/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-xl font-bold text-[#003366]">
                            Cooperativa Ambato
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('mis-viajes') }}" class="text-[#1F2937] hover:text-[#CC0000] font-semibold transition">
                                Mis Viajes
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-gray-500 hover:text-gray-700 underline">
                                    Cerrar Sesión
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <div class="min-h-screen w-full">
            {{ $slot }}
        </div>

    </body>
</html>