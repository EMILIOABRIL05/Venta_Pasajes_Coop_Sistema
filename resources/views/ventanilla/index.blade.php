<x-layouts.app title="Ventanilla – Rutas disponibles">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                    🚌 Rutas Disponibles
                </h2>
                <p class="mt-1 text-sm text-gray-500">Selecciona una ruta para iniciar la venta de pasajes.</p>
            </div>

            {{-- Botón acción futura: Nueva venta --}}
            <a href="#"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 active:scale-95 transition-all duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nueva Venta
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Tarjeta principal ───────────────────────────────────────── --}}
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden ring-1 ring-gray-100">

                {{-- Cabecera de la tarjeta --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-indigo-50 to-white">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 012.553-1.894l5.447 2.724m0 0L21 3m-10 3.276V20m0 0l10-3.276V5.618"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-indigo-500">Módulo Ventanilla</p>
                            <p class="text-base font-semibold text-gray-800">Listado de Rutas</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                        {{ $rutas->count() }} {{ Str::plural('ruta', $rutas->count()) }}
                    </span>
                </div>

                {{-- ── Tabla ──────────────────────────────────────────────── --}}
                @if ($rutas->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2a4 4 0 014-4h0a4 4 0 014 4v2M9 17H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4"/>
                        </svg>
                        <p class="text-sm font-medium">No hay rutas registradas aún.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">

                            {{-- Encabezados --}}
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left font-semibold">#</th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-semibold">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            Origen
                                        </span>
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left font-semibold">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                            Destino
                                        </span>
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right font-semibold">Precio Base</th>
                                    <th scope="col" class="px-6 py-3.5 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>

                            {{-- Filas --}}
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach ($rutas as $ruta)
                                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-150">

                                        {{-- Número de fila --}}
                                        <td class="px-6 py-4 text-gray-400 font-mono text-xs">
                                            {{ $loop->iteration }}
                                        </td>

                                        {{-- Origen --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                                    {{ strtoupper(substr($ruta->origen->nombre ?? '?', 0, 2)) }}
                                                </span>
                                                <div>
                                                    <p class="font-semibold text-gray-800">{{ $ruta->origen->nombre ?? '—' }}</p>
                                                    @if($ruta->origen?->ciudad)
                                                        <p class="text-xs text-gray-400">{{ $ruta->origen->ciudad }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Destino --}}
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700 text-xs font-bold">
                                                    {{ strtoupper(substr($ruta->destino->nombre ?? '?', 0, 2)) }}
                                                </span>
                                                <div>
                                                    <p class="font-semibold text-gray-800">{{ $ruta->destino->nombre ?? '—' }}</p>
                                                    @if($ruta->destino?->ciudad)
                                                        <p class="text-xs text-gray-400">{{ $ruta->destino->ciudad }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Precio --}}
                                        <td class="px-6 py-4 text-right">
                                            <span class="inline-flex items-baseline gap-0.5">
                                                <span class="text-xs font-medium text-gray-400">$</span>
                                                <span class="text-base font-bold text-indigo-700">
                                                    {{ number_format($ruta->precio_base, 2) }}
                                                </span>
                                            </span>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="px-6 py-4 text-center">
                                            <a href="#"
                                               class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all duration-150 opacity-0 group-hover:opacity-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                                </svg>
                                                Vender
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>{{-- /tarjeta --}}

            {{-- ── Cuadrícula de asientos ──────────────────────────────────── --}}
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden ring-1 ring-gray-100">

                {{-- Cabecera --}}
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="text-xs font-medium uppercase tracking-widest text-gray-500">Distribución del bus</p>
                    <p class="text-base font-semibold text-gray-800">Mapa de Asientos (40 asientos)</p>
                </div>

                {{-- Cuadrícula --}}
                <div class="p-6">
                    <div class="grid grid-cols-4 gap-2">
                        @for ($i = 1; $i <= 40; $i++)
                            <button
                                type="button"
                                id="asiento-{{ $i }}"
                                data-asiento="{{ $i }}"
                                class="flex items-center justify-center rounded-md border border-gray-300 p-3 text-sm font-semibold"
                            >
                                {{ $i }}
                            </button>
                        @endfor
                    </div>
                </div>

            </div>{{-- /cuadrícula asientos --}}

        </div>
    </div>

</x-layouts.app>
