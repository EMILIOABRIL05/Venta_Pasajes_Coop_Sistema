<div class="min-h-screen bg-[#F3F4F6] text-gray-800">
    <header class="bg-[#003366] px-4 py-4 shadow-md">
        <h1 class="text-2xl font-extrabold tracking-wide text-white">
            Panel del Chofer
        </h1>
        <p class="mt-1 text-base font-semibold text-blue-100">
            Operacion de ruta en tiempo real
        </p>
    </header>

    <main class="mx-auto w-full max-w-md space-y-4 px-4 py-4 sm:max-w-2xl sm:px-6">
        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Hoja de Ruta Actual
            </h2>

            <div class="mt-3 grid grid-cols-1 gap-3 text-base sm:grid-cols-2">
                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Trayecto</p>
                    <p class="mt-1 text-xl font-black text-gray-900">{{ $origen }} - {{ $destino }}</p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Hora de salida</p>
                    <p class="mt-1 text-2xl font-black text-gray-900">{{ $horaSalida }}</p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Numero de bus</p>
                    <p class="mt-1 text-xl font-black text-gray-900">{{ $numeroBus }}</p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Placa</p>
                    <p class="mt-1 text-xl font-black text-gray-900">{{ $placaBus }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Estado del Viaje
            </h2>

            <div class="mt-3 grid grid-cols-1 gap-3">
                <button
                    type="button"
                    wire:click="cambiarEstado('en_terminal')"
                    class="w-full rounded-xl px-4 py-4 text-left text-xl font-extrabold text-white shadow transition focus:outline-none focus:ring-4 focus:ring-blue-300 {{ $estadoActual === 'en_terminal' ? 'bg-[#003366]' : 'bg-[#2f4f75]' }}"
                >
                    En Terminal
                </button>

                <button
                    type="button"
                    wire:click="cambiarEstado('en_curso')"
                    class="w-full rounded-xl px-4 py-4 text-left text-xl font-extrabold text-white shadow transition focus:outline-none focus:ring-4 focus:ring-blue-300 {{ $estadoActual === 'en_curso' ? 'bg-[#003366]' : 'bg-[#2f4f75]' }}"
                >
                    En Curso
                </button>

                <button
                    type="button"
                    wire:click="cambiarEstado('finalizada')"
                    class="w-full rounded-xl px-4 py-4 text-left text-xl font-extrabold text-white shadow transition focus:outline-none focus:ring-4 focus:ring-red-300 {{ $estadoActual === 'finalizada' ? 'bg-[#CC0000]' : 'bg-[#a03333]' }}"
                >
                    Finalizada
                </button>
            </div>
        </section>

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Pasajeros
            </h2>

            <div class="mt-3 rounded-lg bg-gray-50 p-4 text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    Pasajeros a bordo / Capacidad total
                </p>
                <p class="mt-2 text-4xl font-black text-[#003366]">
                    {{ $pasajerosAbordo }} / {{ $capacidadTotal }}
                </p>
            </div>
        </section>

        <section class="rounded-xl border-2 border-dashed border-[#003366] bg-white p-4 shadow-sm">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Acceso Rapido
            </h2>
            <button
                type="button"
                disabled
                class="mt-3 w-full cursor-not-allowed rounded-xl bg-[#003366] px-4 py-4 text-xl font-extrabold text-white opacity-80"
            >
                Escaneo QR (Sprint 5)
            </button>
        </section>
    </main>
</div>
