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
        @if (!$viajeActual)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900 shadow-sm">
                <p class="text-sm font-semibold">Sin hoja de ruta asignada</p>
                <p class="mt-1 text-sm">
                    Operativa debe generar el viaje de hoy y asignarte como chofer. Si eres administrador de prueba, abre esta pantalla con
                    <span class="font-mono text-xs">?viaje_id=ID</span>.
                </p>
            </div>
        @endif

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Hoja de Ruta Actual
            </h2>

            <div class="mt-3 grid grid-cols-1 gap-3 text-base sm:grid-cols-2">
                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Trayecto</p>
                    <p class="mt-1 text-xl font-black text-gray-900">
                        {{ $viajeActual?->frecuencia?->ruta?->origen?->nombre ?? '—' }}
                        -
                        {{ $viajeActual?->frecuencia?->ruta?->destino?->nombre ?? '—' }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Hora de salida</p>
                    <p class="mt-1 text-2xl font-black text-gray-900">
                        @if ($viajeActual?->frecuencia?->hora_salida)
                            {{ substr($viajeActual->frecuencia->hora_salida, 0, 5) }}
                        @else
                            —
                        @endif
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Placa</p>
                    <p class="mt-1 text-xl font-black text-gray-900">
                        {{ $viajeActual?->bus?->placa ?? '—' }}
                    </p>
                </div>

                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Fecha viaje</p>
                    <p class="mt-1 text-xl font-black text-gray-900">
                        {{ $viajeActual?->fecha?->format('d/m/Y') ?? '—' }}
                    </p>
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
                    @disabled(!$viajeActual)
                    class="w-full rounded-xl px-4 py-4 text-left text-xl font-extrabold text-white shadow transition focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50 {{ $estadoActual === 'en_terminal' ? 'bg-[#003366]' : 'bg-[#2f4f75]' }}"
                >
                    En Terminal
                </button>

                <button
                    type="button"
                    wire:click="cambiarEstado('en_curso')"
                    @disabled(!$viajeActual)
                    class="w-full rounded-xl px-4 py-4 text-left text-xl font-extrabold text-white shadow transition focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50 {{ $estadoActual === 'en_curso' ? 'bg-[#003366]' : 'bg-[#2f4f75]' }}"
                >
                    En Curso
                </button>

                <button
                    type="button"
                    wire:click="cambiarEstado('finalizada')"
                    @disabled(!$viajeActual)
                    class="w-full rounded-xl px-4 py-4 text-left text-xl font-extrabold text-white shadow transition focus:outline-none focus:ring-4 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-50 {{ $estadoActual === 'finalizada' ? 'bg-[#CC0000]' : 'bg-[#a03333]' }}"
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

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Asientos en tiempo real
            </h2>

            <div class="mt-3 rounded-lg bg-gray-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Disponibles</p>
                        <p class="mt-1 text-3xl font-black text-[#003366]">
                            {{ count($resumenAsientos['asientos_disponibles'] ?? []) }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Siguiente asiento</p>
                        <p class="mt-1 text-2xl font-black text-gray-900">
                            {{ $resumenAsientos['asientos_disponibles'][0] ?? 'Lleno' }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full bg-[#003366]" style="width: {{ min(100, (float) ($resumenAsientos['ocupacion'] ?? 0)) }}%"></div>
                </div>

                <p class="mt-2 text-sm text-gray-600">
                    Ocupación actual: {{ number_format((float) ($resumenAsientos['ocupacion'] ?? 0), 2) }}%
                </p>
            </div>
        </section>

        <section class="rounded-xl border-2 border-dashed border-[#003366] bg-white p-4 shadow-sm">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Escaneo QR (abordaje)
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Activa la cámara y apunta al QR del boleto. Al leer el UUID se registra el abordaje como
                <span class="font-semibold">A bordo</span>.
            </p>

            @if ($mensajeAbordaje)
                <div
                    class="mt-3 rounded-lg px-3 py-2 text-sm font-semibold {{ $tipoMensajeAbordaje === 'success' ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900' }}"
                    role="status"
                >
                    {{ $mensajeAbordaje }}
                </div>
            @endif

            <div
                class="mt-4"
                x-data="choferQrScanner(@js($this->getId()))"
            >
                <div
                    id="chofer-qr-reader"
                    wire:ignore
                    class="mx-auto min-h-[220px] max-w-sm overflow-hidden rounded-xl bg-black/5 ring-1 ring-gray-200"
                ></div>

                <p x-show="errorCamara" x-cloak class="mt-2 text-sm text-rose-600" x-text="errorCamara"></p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="flex-1 rounded-xl bg-[#003366] px-4 py-3 text-base font-extrabold text-white shadow disabled:cursor-not-allowed disabled:opacity-50"
                        @if (! $viajeActual) disabled @endif
                        @click="iniciar()"
                    >
                        Iniciar camara
                    </button>
                    <button
                        type="button"
                        class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-3 text-base font-extrabold text-gray-800 shadow"
                        @click="detener()"
                    >
                        Detener
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
            <h2 class="text-xl font-extrabold text-[#003366]">
                Venta express
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Registra un pasaje a bordo con datos simplificados, al precio base de la ruta y el primer asiento libre.
            </p>

            @if ($mensajeExpress)
                <div
                    class="mt-3 rounded-lg px-3 py-2 text-sm font-semibold {{ $tipoMensajeExpress === 'success' ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900' }}"
                    role="status"
                >
                    {{ $mensajeExpress }}
                </div>
            @endif

            <button
                type="button"
                wire:click="ventaExpress"
                wire:loading.attr="disabled"
                class="mt-4 w-full rounded-xl bg-emerald-700 px-4 py-4 text-xl font-extrabold text-white shadow transition hover:bg-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-300 disabled:cursor-not-allowed disabled:opacity-60"
                @if (! $viajeActual) disabled @endif
            >
                <span wire:loading.remove wire:target="ventaExpress">Venta Express</span>
                <span wire:loading wire:target="ventaExpress">Procesando...</span>
            </button>
        </section>
    </main>
</div>
