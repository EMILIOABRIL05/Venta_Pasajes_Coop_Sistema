<x-layouts.app>
    <x-slot:title>Inicio</x-slot>

    <div class="bg-[#003366] py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Viaja seguro, viaja con nosotros</h2>
            <p class="text-lg text-gray-200 mb-8 max-w-2xl mx-auto">Compra tus pasajes en línea de forma rápida y sin hacer filas en la terminal.</p>
            
            <a href="{{ route('login') }}" class="inline-block bg-[#CC0000] px-6 py-3 rounded-md font-bold text-white hover:bg-red-800 transition shadow-sm mt-4">
                Iniciar Sesión
            </a>
        </div>
    </div>

    <div class="-mt-12 mb-16 relative z-10 px-4">
        <livewire:buscador-pasajes />
    </div>

</x-layouts.app>