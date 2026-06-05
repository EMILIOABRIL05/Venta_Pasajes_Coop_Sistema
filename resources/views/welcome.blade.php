<x-layouts.app>
    <x-slot:title>Inicio</x-slot>

    {{-- ─── Hero Section con Slider Automático ──────────────────────────────── --}}
    <div x-data="{
            slides: [
                '{{ asset('img/hero/img1.webp') }}',
                '{{ asset('img/hero/img2.webp') }}',
                '{{ asset('img/hero/img3.webp') }}'
            ],
            active: 0,
            init() {
                setInterval(() => {
                    this.active = (this.active + 1) % this.slides.length;
                }, 5000);
            }
        }"
        class="relative h-[520px] md:h-[600px] overflow-hidden">

        {{-- Imágenes del slider (crossfade) --}}
        <template x-for="(img, index) in slides" :key="index">
            <div x-show="active === index"
                 x-transition.opacity.duration.1000ms
                 class="absolute inset-0">
                <img :src="img" alt="Hero slide" class="w-full h-full object-cover">
            </div>
        </template>

        {{-- Overlay oscuro para legibilidad --}}
        <div class="absolute inset-0 bg-black/60"></div>

        {{-- Contenido del Hero (texto + botón) --}}
        <div class="relative z-10 h-full flex flex-col items-center justify-center px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4 drop-shadow-lg">Viaja seguro, viaja con nosotros</h2>
            <p class="text-lg text-gray-200 mb-8 max-w-2xl mx-auto drop-shadow">Compra tus pasajes en línea de forma rápida y sin hacer filas en la terminal.</p>

            <a href="{{ route('login') }}" class="inline-block bg-[#CC0000] px-6 py-3 rounded-md font-bold text-white hover:bg-red-800 transition shadow-lg mt-4">
                Iniciar Sesión
            </a>
        </div>
    </div>

    {{-- ─── Buscador flotante ──────────────────────────────────────────────────── --}}
    <div class="-mt-12 mb-16 relative z-10 px-4">
        <livewire:buscador-pasajes />
    </div>

</x-layouts.app>