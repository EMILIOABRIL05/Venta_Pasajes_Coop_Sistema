<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Carrito de Compra | {{ config('app.name', 'Cooperativa Ambato') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles (Vite + Livewire se inyectan aquí) -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">

        <!-- Sin max-w-md, sin centrado apretado. El componente ocupa el 100% -->
        <div class="min-h-screen w-full">
            {{ $slot }}
        </div>

    </body>
</html>
