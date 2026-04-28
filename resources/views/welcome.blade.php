<x-layouts.app>
    <x-slot:title>Inicio</x-slot>

    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-2xl w-full bg-gray-100 rounded-lg border border-[#003366]/10 p-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-semibold text-[#003366]">
                Cooperativa Ambato - Sistema de Pasajes
            </h1>

            <p class="mt-4 text-gray-800">
                Gestiona tus pasajes y operaciones en un solo lugar.
            </p>

            <div class="mt-6">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md bg-[#CC0000] px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#CC0000]/90 focus:outline-none focus:ring-2 focus:ring-[#CC0000]/40">
                    Iniciar sesion
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
