<x-layouts.app title="Cierre de Turno – Ventanilla">

    {{-- Lucide Icons CDN --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight flex items-center gap-2">
                    <i data-lucide="bar-chart-2" class="h-6 w-6 text-indigo-600 inline-block"></i>
                    Cierre de Turno
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Resumen financiero del turno —
                    <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($fecha)->format('d \d\e F, Y') }}</span>
                </p>
            </div>

            {{-- Badge estado cierre --}}
            @if($cierreExistente)
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    <i data-lucide="lock" class="h-4 w-4"></i>
                    Turno cerrado — {{ $cierreExistente->created_at->format('H:i') }}
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-1.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-200">
                    <i data-lucide="clock" class="h-4 w-4"></i>
                    Turno en curso
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-7">

            {{-- ── Alertas de sesión ───────────────────────────────────────── --}}
            @foreach(['success' => ['emerald', 'check-circle'], 'warning' => ['amber', 'alert-triangle'], 'error' => ['red', 'x-circle']] as $type => [$color, $icon])
                @if(session($type))
                    <div x-data="{ show: true }" x-show="show" x-transition
                         class="flex items-center gap-3 rounded-2xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-5 py-4">
                        <i data-lucide="{{ $icon }}" class="h-5 w-5 flex-shrink-0 text-{{ $color }}-600"></i>
                        <p class="flex-1 text-sm font-medium text-{{ $color }}-800">{{ session($type) }}</p>
                        <button @click="show = false" class="text-{{ $color }}-400 hover:text-{{ $color }}-600 transition-colors">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>
                @endif
            @endforeach

            {{-- Aviso: cierre ya registrado --}}
            @if($cierreExistente)
                <div class="flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4">
                    <i data-lucide="info" class="h-5 w-5 flex-shrink-0 text-blue-500 mt-0.5"></i>
                    <p class="text-sm text-blue-800">
                        Este turno fue cerrado el <strong>{{ $cierreExistente->created_at->format('d/m/Y') }}</strong>
                        a las <strong>{{ $cierreExistente->created_at->format('H:i') }}</strong>.
                        Los indicadores son de solo lectura y reflejan el estado actual de las ventas del día.
                    </p>
                </div>
            @endif

            {{-- ── KPIs — fila superior ────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                {{-- KPI 1: Total Cobrado --}}
                <div class="group relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100 hover:shadow-lg transition-shadow duration-200">
                    {{-- Banda de color superior --}}
                    <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-indigo-400 rounded-t-2xl"></div>
                    <div class="px-6 py-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Total Cobrado</p>
                                <p class="text-3xl font-extrabold text-gray-900 tracking-tight">
                                    <span class="text-lg font-semibold text-gray-400">$</span>{{ number_format($totalBruto, 2) }}
                                </p>
                                <p class="mt-1.5 text-xs text-gray-400 flex items-center gap-1">
                                    <i data-lucide="trending-up" class="h-3.5 w-3.5 text-indigo-400"></i>
                                    Ingreso bruto del turno
                                </p>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 group-hover:bg-indigo-200 transition-colors duration-200">
                                <i data-lucide="dollar-sign" class="h-6 w-6"></i>
                            </span>
                        </div>
                        {{-- Barra visual proporcional --}}
                        <div class="mt-4 h-1 rounded-full bg-gray-100">
                            <div class="h-1 rounded-full bg-indigo-400 transition-all duration-700"
                                 style="width: {{ $totalBruto > 0 ? '100' : '0' }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- KPI 2: Boletos Vendidos --}}
                <div class="group relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100 hover:shadow-lg transition-shadow duration-200">
                    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-t-2xl"></div>
                    <div class="px-6 py-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Boletos Vendidos</p>
                                <p class="text-3xl font-extrabold text-gray-900 tracking-tight">
                                    {{ number_format($totalBoletos) }}
                                    <span class="text-base font-medium text-gray-400 ml-1">uds.</span>
                                </p>
                                <p class="mt-1.5 text-xs text-gray-400 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="h-3.5 w-3.5 text-emerald-400"></i>
                                    {{ $recaudacionPorRuta->count() }} {{ $recaudacionPorRuta->count() === 1 ? 'ruta' : 'rutas' }} activas
                                </p>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 group-hover:bg-emerald-200 transition-colors duration-200">
                                <i data-lucide="ticket" class="h-6 w-6"></i>
                            </span>
                        </div>
                        <div class="mt-4 h-1 rounded-full bg-gray-100">
                            <div class="h-1 rounded-full bg-emerald-400"></div>
                        </div>
                    </div>
                </div>

                {{-- KPI 3: Promedio de Venta --}}
                <div class="group relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100 hover:shadow-lg transition-shadow duration-200">
                    <div class="h-1.5 w-full bg-gradient-to-r from-violet-500 to-purple-400 rounded-t-2xl"></div>
                    <div class="px-6 py-5">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Promedio de Venta</p>
                                <p class="text-3xl font-extrabold text-gray-900 tracking-tight">
                                    <span class="text-lg font-semibold text-gray-400">$</span>{{ number_format($promedioPorBoleto, 2) }}
                                </p>
                                <p class="mt-1.5 text-xs text-gray-400 flex items-center gap-1">
                                    <i data-lucide="bar-chart" class="h-3.5 w-3.5 text-violet-400"></i>
                                    Por boleto vendido
                                </p>
                            </div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600 group-hover:bg-violet-200 transition-colors duration-200">
                                <i data-lucide="calculator" class="h-6 w-6"></i>
                            </span>
                        </div>
                        <div class="mt-4 h-1 rounded-full bg-gray-100">
                            <div class="h-1 rounded-full bg-violet-400"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Fila inferior: Neto vs Reembolsos ───────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Ingreso Neto --}}
                <div class="flex items-center gap-5 rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 px-6 py-5 shadow-lg shadow-indigo-200">
                    <span class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-white/20 text-white">
                        <i data-lucide="banknote" class="h-7 w-7"></i>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200">Ingreso Neto</p>
                        <p class="text-3xl font-extrabold text-white tracking-tight mt-0.5">
                            ${{ number_format($totalNeto, 2) }}
                        </p>
                        <p class="text-xs text-indigo-300 mt-1">Bruto − Reembolsos aprobados</p>
                    </div>
                </div>

                {{-- Reembolsos --}}
                <div class="flex items-center gap-5 rounded-2xl bg-white shadow-md ring-1 ring-red-100 px-6 py-5">
                    <span class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-500">
                        <i data-lucide="undo-2" class="h-7 w-7"></i>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Reembolsos Aprobados</p>
                        <p class="text-3xl font-extrabold text-red-600 tracking-tight mt-0.5">
                            −${{ number_format($totalReembolsos, 2) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="info" class="h-3 w-3"></i>
                            Descuentos procesados hoy
                        </p>
                    </div>
                </div>
            </div>

            {{-- ── Tabla de desglose por ruta ───────────────────────────────── --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100">

                {{-- Cabecera --}}
                <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                            <i data-lucide="route" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-indigo-500">Detalle</p>
                            <p class="text-sm font-semibold text-gray-800">Recaudación por Ruta</p>
                        </div>
                    </div>
                    @if($recaudacionPorRuta->isNotEmpty())
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            <i data-lucide="list" class="h-3 w-3"></i>
                            {{ $recaudacionPorRuta->count() }} {{ $recaudacionPorRuta->count() === 1 ? 'ruta' : 'rutas' }}
                        </span>
                    @endif
                </div>

                @if($recaudacionPorRuta->isEmpty())
                    {{-- Estado vacío --}}
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400 gap-3">
                        <i data-lucide="inbox" class="h-12 w-12 opacity-30"></i>
                        <p class="text-sm font-medium">No se registran ventas para el día de hoy.</p>
                        <p class="text-xs text-gray-400">Las ventas procesadas durante este turno aparecerán aquí.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold">#</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i data-lucide="map" class="h-3.5 w-3.5 text-indigo-400"></i>
                                            Ruta
                                        </span>
                                    </th>
                                    <th class="px-6 py-3.5 text-center font-semibold">
                                        <span class="inline-flex items-center gap-1.5">
                                            <i data-lucide="ticket" class="h-3.5 w-3.5 text-emerald-400"></i>
                                            Boletos
                                        </span>
                                    </th>
                                    <th class="px-6 py-3.5 text-right font-semibold">
                                        <span class="inline-flex items-center gap-1.5 justify-end">
                                            <i data-lucide="dollar-sign" class="h-3.5 w-3.5 text-indigo-400"></i>
                                            Recaudado
                                        </span>
                                    </th>
                                    <th class="px-6 py-3.5 text-right font-semibold pr-6">% Contribución</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach($recaudacionPorRuta as $i => $item)
                                    @php
                                        $porcentaje = $totalBruto > 0
                                            ? round(($item['total_recaudado'] / $totalBruto) * 100, 1)
                                            : 0;
                                    @endphp
                                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-150">
                                        <td class="px-6 py-4 font-mono text-xs text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                                                    <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                                                </span>
                                                <span class="font-semibold text-gray-800">{{ $item['ruta'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                                <i data-lucide="ticket" class="h-3 w-3"></i>
                                                {{ $item['boletos_count'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-bold text-gray-800">
                                            ${{ number_format($item['total_recaudado'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 pr-6">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="w-24 h-1.5 rounded-full bg-gray-100">
                                                    <div class="h-1.5 rounded-full bg-indigo-400 transition-all duration-700"
                                                         style="width: {{ $porcentaje }}%"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-500 w-10 text-right">{{ $porcentaje }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- Fila totales --}}
                            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-700 uppercase tracking-wide">
                                        Total general
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-700">
                                            {{ $totalBoletos }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-extrabold text-indigo-700">
                                        ${{ number_format($totalBruto, 2) }}
                                    </td>
                                    <td class="px-6 py-4 pr-6 text-right">
                                        <span class="text-xs font-bold text-gray-500">100%</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ── Panel de acciones ─────────────────────────────────────────── --}}
            <div class="flex items-center justify-between rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 px-6 py-4">
                <p class="text-xs text-gray-400 flex items-center gap-1.5">
                    <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                    Actualizado: {{ now()->format('d/m/Y H:i:s') }}
                </p>
                <div class="flex items-center gap-3">
                    <button
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-50 hover:border-gray-300 active:scale-95 transition-all duration-150"
                    >
                        <i data-lucide="printer" class="h-4 w-4"></i>
                        Imprimir
                    </button>

                    <a href="{{ route('ventanilla.ventas.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-50 active:scale-95 transition-all duration-150">
                        <i data-lucide="arrow-left" class="h-4 w-4"></i>
                        Volver
                    </a>

                    @if(!$cierreExistente)
                        <form method="POST" action="{{ route('ventas.cierre-turno.store') }}"
                              onsubmit="return confirm('¿Registrar el cierre de turno del {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}?\n\nEsta acción no se puede deshacer.')">
                            @csrf
                            <button
                                type="submit"
                                id="btn-cerrar-turno"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 active:scale-95 transition-all duration-150"
                            >
                                <i data-lucide="lock" class="h-4 w-4"></i>
                                Registrar Cierre
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-5 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed select-none">
                            <i data-lucide="check-circle-2" class="h-4 w-4 text-emerald-500"></i>
                            Turno cerrado
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Inicializar iconos Lucide después del DOM --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>

</x-layouts.app>
