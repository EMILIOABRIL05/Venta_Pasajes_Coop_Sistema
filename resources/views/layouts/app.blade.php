<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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

            <div class="relative flex min-h-screen min-w-0 flex-col transition-[padding] duration-300 {{ auth()->check() ? 'sm:pl-64' : '' }}">
                <div class="relative z-20">
                    <x-ui.navbar />
                </div>

                <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    <x-ui.alert />
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="relative z-10 border-b border-[#003366]/10 bg-gray-100">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                @hasSection('header')
                    <header class="relative z-10 border-b border-[#003366]/10 bg-gray-100">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="relative z-0 flex-1 min-w-0 overflow-x-hidden py-6">
                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot ?? '' }}
                    @endif
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
