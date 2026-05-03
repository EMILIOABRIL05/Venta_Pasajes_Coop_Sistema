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

            {{-- ── Flash: Venta registrada exitosamente ──────────────────────── --}}
            @if (session('venta_exitosa'))
                @php $v = session('venta_exitosa'); @endphp

                <div x-data="{ visible: true }" x-show="visible"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-3"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="rounded-2xl overflow-hidden shadow-lg ring-1 ring-emerald-200">

                    {{-- Banda de éxito --}}
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-white font-bold text-base tracking-tight">Venta #{{ $v['id'] }} registrada exitosamente</p>
                                <p class="text-emerald-100 text-xs mt-0.5">
                                    {{ $v['fecha'] }} · {{ $v['hora'] }} · Cajero: {{ $v['cajero'] }}
                                </p>
                            </div>
                        </div>
                        <button @click="visible = false"
                                class="text-white/70 hover:text-white transition-colors"
                                aria-label="Cerrar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Cuerpo del resumen --}}
                    <div class="bg-white px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-6">

                        {{-- Total cobrado --}}
                        <div class="flex flex-col items-center justify-center bg-emerald-50 rounded-xl p-4 ring-1 ring-emerald-100">
                            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-600 mb-1">Total Cobrado</p>
                            <p class="text-3xl font-extrabold text-emerald-700 tracking-tight">
                                <span class="text-lg font-semibold">$</span>{{ $v['total'] }}
                                <span class="text-sm font-medium text-emerald-500 ml-1">USD</span>
                            </p>
                            <p class="text-xs text-emerald-500 mt-1">{{ $v['boletos'] }} boleto(s) emitido(s)</p>
                        </div>

                        {{-- Asientos vendidos --}}
                        <div class="flex flex-col justify-center">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Asientos vendidos</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (explode(', ', $v['asientos']) as $num)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-bold">
                                        {{ trim($num) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Códigos de reserva --}}
                        <div class="flex flex-col justify-center">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Códigos de reserva</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (explode(' · ', $v['codigos']) as $cod)
                                    <span class="inline-flex items-center rounded-full bg-slate-800 text-slate-100 text-[10px] font-mono font-semibold px-2.5 py-1 tracking-wider">
                                        {{ trim($cod) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            @endif
            {{-- /Flash --}}

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
                        <table class="min-w-full divide-y divide-gray-100 text-sm whitespace-nowrap">

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
            <div class="bg-white shadow-lg rounded-2xl ring-1 ring-gray-100">

                {{-- Cabecera con leyenda --}}
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="14" rx="3"/>
                                <path d="M3 10h18M8 21l1-4M16 21l-1-4M7 17h10" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Distribución del bus</p>
                            <p class="text-base font-semibold text-gray-800">Mapa de Asientos — 40 plazas</p>
                        </div>
                    </div>
                    {{-- Leyenda --}}
                    <div class="flex items-center gap-4 text-xs font-semibold text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-3.5 h-3.5 rounded bg-emerald-500 shadow-sm"></span>
                            Disponible
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-3.5 h-3.5 rounded bg-cyan-500 shadow-sm ring-2 ring-cyan-300"></span>
                            Seleccionado
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block w-3.5 h-3.5 rounded bg-red-400 opacity-60"></span>
                            Ocupado
                        </span>
                    </div>
                </div>

                {{-- Cuerpo del bus --}}
                <div class="p-8">
                    <div class="max-w-sm mx-auto">

                        {{-- ── Frente del bus: volante ── --}}
                        <div class="flex flex-col items-center mb-8 pb-6 border-b-2 border-dashed border-gray-200">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">⬆ Frente del Bus</p>

                            {{-- Volante SVG de 3 rayos --}}
                            <div class="w-20 h-20 text-slate-700 drop-shadow-md">
                                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                    {{-- Aro exterior --}}
                                    <circle cx="50" cy="50" r="43" stroke="currentColor" stroke-width="7" stroke-linecap="round"/>
                                    {{-- Hub central --}}
                                    <circle cx="50" cy="50" r="9" fill="currentColor"/>
                                    {{-- Rayo superior --}}
                                    <line x1="50" y1="41" x2="50" y2="7" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    {{-- Rayo inferior-izquierdo --}}
                                    <line x1="43" y1="55" x2="12" y2="74" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                    {{-- Rayo inferior-derecho --}}
                                    <line x1="57" y1="55" x2="88" y2="74" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-2 font-medium tracking-wide">Conductor</p>
                        </div>

                        {{-- ── Cuadrícula de asientos ── --}}
                        @php
                            // Temporal: reemplazar con asientos reales de la BD
                            $asientosOcupados = [3, 7, 12, 15, 22, 28, 33];
                            // Precio de referencia: mínimo entre las rutas cargadas
                            $precioRef = $rutas->min('precio_base') ?? 0;
                        @endphp

                        <div class="grid grid-cols-4 gap-3">
                            @for ($i = 1; $i <= 40; $i++)
                                @php $ocupado = in_array($i, $asientosOcupados); @endphp

                                {{-- Wrapper group para el tooltip --}}
                                <div class="relative group flex justify-center" data-group>

                                    {{-- ── Tooltip ── --}}
                                    <div class="pointer-events-none absolute -top-[5.5rem] left-1/2 -translate-x-1/2 z-50
                                                opacity-0 invisible
                                                group-hover:opacity-100 group-hover:visible
                                                transition-all duration-200 ease-out
                                                w-32">
                                        <div class="bg-gray-900 text-white rounded-xl px-3 py-2.5 shadow-2xl text-center">
                                            <p class="text-[11px] font-bold tracking-wide">Asiento {{ $i }}</p>
                                            <p class="text-emerald-400 text-[11px] font-semibold mt-0.5">
                                                ${{ number_format($precioRef, 2) }}
                                            </p>
                                            <p data-tooltip-status
                                               class="text-[9px] mt-1 font-medium {{ $ocupado ? 'text-red-400' : 'text-gray-400' }}">
                                                {{ $ocupado ? '🔴 Ocupado' : '🟢 Disponible' }}
                                            </p>
                                        </div>
                                        {{-- Flecha del tooltip --}}
                                        <div class="flex justify-center">
                                            <div class="w-2.5 h-2.5 bg-gray-900 rotate-45 -mt-1.5"></div>
                                        </div>
                                    </div>

                                    {{-- ── Botón de asiento ── --}}
                                    <button
                                        type="button"
                                        id="asiento-{{ $i }}"
                                        data-asiento="{{ $i }}"
                                        data-seat-state="{{ $ocupado ? 'occupied' : 'available' }}"
                                        @disabled($ocupado)
                                        class="w-full flex flex-col items-center justify-center gap-0.5 rounded-lg py-2.5 px-1 text-xs font-bold border transition-all duration-150
                                            {{ $ocupado
                                                ? 'bg-red-400 border-red-500 text-white opacity-50 cursor-not-allowed'
                                                : 'bg-emerald-500 border-emerald-600 text-white shadow-sm hover:bg-emerald-400 hover:shadow-md hover:-translate-y-0.5 active:scale-95 cursor-pointer'
                                            }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 shrink-0">
                                            <path d="M6 2a2 2 0 00-2 2v7h2V4h12v7h2V4a2 2 0 00-2-2H6z"/>
                                            <path d="M4 13a2 2 0 012-2h12a2 2 0 012 2v3H4v-3z"/>
                                            <path d="M6 16v4h2v-2h8v2h2v-4H6z"/>
                                        </svg>
                                        {{ $i }}
                                    </button>

                                </div>{{-- /group --}}
                            @endfor
                        </div>

                    </div>
                </div>

                {{-- Safelist Tailwind: clases dinámicas de selección --}}
                <div class="hidden bg-cyan-500 border-cyan-600 ring-2 ring-cyan-300 ring-offset-1 text-cyan-400"></div>

                {{-- Lógica de selección de asientos --}}
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Clases CSS para cada estado
                    var CLS_AVAIL    = ['bg-emerald-500', 'border-emerald-600', 'shadow-sm'];
                    var CLS_SELECTED = ['bg-cyan-500',    'border-cyan-600',    'shadow-md', 'ring-2', 'ring-cyan-300', 'ring-offset-1'];

                    // Escuchar clics solo en asientos disponibles
                    document.querySelectorAll('[data-seat-state="available"]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var isSelected = this.dataset.seatState === 'selected';
                            var statusEl   = this.closest('[data-group]').querySelector('[data-tooltip-status]');

                            if (isSelected) {
                                // ── Deseleccionar → vuelve a verde ──
                                this.dataset.seatState = 'available';
                                CLS_SELECTED.forEach(function (c) { btn.classList.remove(c); });
                                CLS_AVAIL.forEach(function (c)    { btn.classList.add(c); });
                                if (statusEl) {
                                    statusEl.textContent = '🟢 Disponible';
                                    statusEl.classList.replace('text-cyan-400', 'text-gray-400');
                                }
                            } else {
                                // ── Seleccionar → cambia a cian ──
                                this.dataset.seatState = 'selected';
                                CLS_AVAIL.forEach(function (c)    { btn.classList.remove(c); });
                                CLS_SELECTED.forEach(function (c) { btn.classList.add(c); });
                                if (statusEl) {
                                    statusEl.textContent = '🔵 Seleccionado';
                                    statusEl.classList.replace('text-gray-400', 'text-cyan-400');
                                }
                            }
                        });
                    });
                });
                </script>

            </div>{{-- /cuadrícula asientos --}}

        </div>
    </div>

</x-layouts.app>
