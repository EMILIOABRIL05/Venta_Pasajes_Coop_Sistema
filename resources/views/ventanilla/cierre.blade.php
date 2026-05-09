<x-layouts.app title="Cierre de Turno – Ventanilla">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                    📊 Cierre de Turno
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ auth()->user()->name }} —
                    <span class="font-semibold text-gray-700">
                        {{ \Carbon\Carbon::parse($fecha)->format('d \d\e F, Y') }}
                    </span>
                </p>
            </div>
            @if($cierreExistente)
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                    ✅ Turno cerrado — {{ $cierreExistente->created_at->format('H:i') }}
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-1.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-200">
                    🕐 Turno en curso
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-7">

            {{-- ── Alertas de sesión ───────────────────────────────────────── --}}
            @foreach(['success' => 'emerald', 'warning' => 'amber', 'error' => 'red'] as $key => $color)
                @if(session($key))
                    <div x-data="{ show: true }" x-show="show" x-transition
                         class="flex items-center gap-3 rounded-2xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-5 py-4">
                        <p class="flex-1 text-sm font-medium text-{{ $color }}-800">{{ session($key) }}</p>
                        <button @click="show = false" class="text-{{ $color }}-400 hover:text-{{ $color }}-600">✕</button>
                    </div>
                @endif
            @endforeach

            @if($cierreExistente)
                <div class="flex items-start gap-3 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4">
                    <p class="text-sm text-blue-800">
                        Turno cerrado el <strong>{{ $cierreExistente->created_at->format('d/m/Y') }}</strong>
                        a las <strong>{{ $cierreExistente->created_at->format('H:i') }}</strong>.
                        Datos de solo lectura.
                    </p>
                </div>
            @endif

            {{-- ── KPI Cards ───────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">

                {{-- Total Cobrado --}}
                <div class="relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-indigo-400"></div>
                    <div class="px-6 py-5 flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Total Cobrado</p>
                            <p class="text-3xl font-extrabold text-gray-900">
                                <span class="text-lg text-gray-400">$</span>{{ number_format($totalBruto, 2) }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">Ingreso bruto del turno</p>
                        </div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-2xl">💵</span>
                    </div>
                </div>

                {{-- Boletos Vendidos --}}
                <div class="relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 to-teal-400"></div>
                    <div class="px-6 py-5 flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Boletos Vendidos</p>
                            <p class="text-3xl font-extrabold text-gray-900">
                                {{ number_format($totalBoletos) }}
                                <span class="text-base font-medium text-gray-400 ml-1">uds.</span>
                            </p>
                            <p class="mt-1 text-xs text-gray-400">{{ $recaudacionPorRuta->count() }} rutas activas</p>
                        </div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-2xl">🎫</span>
                    </div>
                </div>

                {{-- Promedio de Venta --}}
                <div class="relative overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100 hover:shadow-lg transition-shadow">
                    <div class="h-1.5 w-full bg-gradient-to-r from-violet-500 to-purple-400"></div>
                    <div class="px-6 py-5 flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Promedio de Venta</p>
                            <p class="text-3xl font-extrabold text-gray-900">
                                <span class="text-lg text-gray-400">$</span>{{ number_format($promedioPorBoleto, 2) }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">Por boleto vendido</p>
                        </div>
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-2xl">📈</span>
                    </div>
                </div>
            </div>

            {{-- Neto + Reembolsos --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex items-center gap-5 rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 px-6 py-5 shadow-lg">
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 text-3xl">🏦</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200">Ingreso Neto</p>
                        <p class="text-3xl font-extrabold text-white">${{ number_format($totalNeto, 2) }}</p>
                        <p class="text-xs text-indigo-300 mt-1">Bruto − Reembolsos aprobados</p>
                    </div>
                </div>
                <div class="flex items-center gap-5 rounded-2xl bg-white shadow-md ring-1 ring-red-100 px-6 py-5">
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-3xl">↩️</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Reembolsos Aprobados</p>
                        <p class="text-3xl font-extrabold text-red-600">−${{ number_format($totalReembolsos, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">Descuentos procesados hoy</p>
                    </div>
                </div>
            </div>

            {{-- ── Gráfico de Progresión Horaria ────────────────────────────── --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-lg">📉</span>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-indigo-500">Análisis</p>
                            <p class="text-sm font-semibold text-gray-800">Flujo de Ventas por Hora</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                            <span class="text-gray-500 font-medium">Recaudación ($)</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            <span class="text-gray-500 font-medium">Boletos</span>
                        </span>
                    </div>
                </div>
                <div class="px-6 py-6">
                    <div class="relative" style="height: 280px;">
                        <canvas id="chartHorario"></canvas>
                    </div>
                </div>
            </div>

            {{-- ── Últimas Transacciones ────────────────────────────────────── --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-lg">🧾</span>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Historial</p>
                            <p class="text-sm font-semibold text-gray-800">Últimas Transacciones</p>
                        </div>
                    </div>
                    @if($ultimasTransacciones->isNotEmpty())
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                            Últimas {{ $ultimasTransacciones->count() }}
                        </span>
                    @endif
                </div>

                @if($ultimasTransacciones->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-gray-400 gap-3">
                        <span class="text-5xl opacity-30">📭</span>
                        <p class="text-sm font-medium">No hay transacciones registradas hoy.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold">Hora</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Venta #</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Pasajero</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Ruta</th>
                                    <th class="px-6 py-3.5 text-center font-semibold">Asientos</th>
                                    <th class="px-6 py-3.5 text-center font-semibold">Boletos</th>
                                    <th class="px-6 py-3.5 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach($ultimasTransacciones as $i => $tx)
                                    <tr class="group hover:bg-indigo-50/40 transition-colors duration-150">
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2 py-0.5 text-xs font-mono font-semibold text-gray-600">
                                                🕐 {{ $tx['hora'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 font-mono text-xs text-gray-400">#{{ $tx['id'] }}</td>
                                        <td class="px-6 py-3.5 font-medium text-gray-800">{{ $tx['pasajero'] }}</td>
                                        <td class="px-6 py-3.5">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                                {{ $tx['ruta'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-center text-xs text-gray-500 font-mono">
                                            {{ $tx['asientos'] ?: '—' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                                {{ $tx['boletos'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-right font-bold text-gray-900">
                                            ${{ number_format($tx['total'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ── Desglose por Ruta ────────────────────────────────────────── --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-lg">🗺️</span>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-widest text-indigo-500">Detalle</p>
                            <p class="text-sm font-semibold text-gray-800">Recaudación por Ruta</p>
                        </div>
                    </div>
                    @if($recaudacionPorRuta->isNotEmpty())
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                            {{ $recaudacionPorRuta->count() }} rutas
                        </span>
                    @endif
                </div>
                @if($recaudacionPorRuta->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-gray-400 gap-3">
                        <span class="text-5xl opacity-30">📭</span>
                        <p class="text-sm font-medium">Sin ventas registradas hoy.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-6 py-3.5 text-left font-semibold">#</th>
                                    <th class="px-6 py-3.5 text-left font-semibold">Ruta</th>
                                    <th class="px-6 py-3.5 text-center font-semibold">Boletos</th>
                                    <th class="px-6 py-3.5 text-right font-semibold">Recaudado</th>
                                    <th class="px-6 py-3.5 text-right pr-6 font-semibold">% Contribución</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                @foreach($recaudacionPorRuta as $i => $item)
                                    @php $pct = $totalBruto > 0 ? round(($item['total_recaudado'] / $totalBruto) * 100, 1) : 0; @endphp
                                    <tr class="hover:bg-indigo-50/40 transition-colors duration-150">
                                        <td class="px-6 py-4 font-mono text-xs text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-6 py-4 font-semibold text-gray-800">{{ $item['ruta'] }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
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
                                                         style="width: {{ $pct }}%"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-500 w-10 text-right">{{ $pct }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-700 uppercase">Total general</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-700">{{ $totalBoletos }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-base font-extrabold text-indigo-700">
                                        ${{ number_format($totalBruto, 2) }}
                                    </td>
                                    <td class="px-6 py-4 pr-6 text-right text-xs font-bold text-gray-500">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ── Panel de acciones ─────────────────────────────────────────── --}}
            <div x-data="{ loadingPdf: false, loadingExcel: false }"
                 class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 px-6 py-4">

                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Exportar reporte</p>
                    <p class="text-xs text-gray-400 mt-0.5">Actualizado: {{ now()->format('d/m/Y H:i:s') }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">

                    {{-- Imprimir --}}
                    <button onclick="window.print()"
                            class="group inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5
                                   text-sm font-medium text-gray-600 shadow-sm
                                   hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 hover:shadow-md
                                   active:scale-95 transition-all duration-150">
                        <svg class="h-4 w-4 text-gray-400 group-hover:text-gray-600 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        Imprimir
                    </button>

                    {{-- Descargar PDF --}}
                    <a href="{{ route('ventanilla.reporte-pdf') }}"
                       @click="loadingPdf = true; setTimeout(() => loadingPdf = false, 4000)"
                       class="group relative inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5
                              text-sm font-semibold text-red-700 shadow-sm
                              hover:bg-red-600 hover:text-white hover:border-red-600 hover:shadow-lg hover:shadow-red-100
                              active:scale-95 transition-all duration-200">

                        {{-- Spinner de carga --}}
                        <span x-show="loadingPdf" x-cloak>
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>

                        {{-- Ícono PDF --}}
                        <span x-show="!loadingPdf">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>

                        <span x-text="loadingPdf ? 'Generando PDF…' : 'Descargar PDF'"></span>
                    </a>

                    {{-- Descargar Excel --}}
                    <a href="{{ route('ventanilla.exportar-excel') }}"
                       @click="loadingExcel = true; setTimeout(() => loadingExcel = false, 4000)"
                       class="group relative inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5
                              text-sm font-semibold text-emerald-700 shadow-sm
                              hover:bg-emerald-600 hover:text-white hover:border-emerald-600 hover:shadow-lg hover:shadow-emerald-100
                              active:scale-95 transition-all duration-200">

                        <span x-show="loadingExcel" x-cloak>
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>

                        <span x-show="!loadingExcel">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c-.621 0-1.125.504-1.125 1.125v1.5m2.25-2.625h7.5M3.375 12h7.5" />
                            </svg>
                        </span>

                        <span x-text="loadingExcel ? 'Generando Excel…' : 'Exportar Excel'"></span>
                    </a>

                    {{-- Divider --}}
                    <div class="hidden sm:block h-8 w-px bg-gray-200"></div>

                    {{-- Volver --}}
                    <a href="{{ route('ventanilla.ventas.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5
                              text-sm font-medium text-gray-600 shadow-sm
                              hover:bg-gray-50 hover:border-gray-300 active:scale-95 transition-all duration-150">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Volver
                    </a>

                    @if(!$cierreExistente)
                        <form method="POST" action="{{ route('ventas.cierre-turno.store') }}"
                              onsubmit="return confirm('¿Registrar el cierre del turno? Esta acción no se puede deshacer.')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5
                                           text-sm font-semibold text-white shadow-md
                                           hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200
                                           active:scale-95 transition-all duration-200">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                Registrar Cierre
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-400 cursor-not-allowed select-none">
                            <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                            </svg>
                            Turno cerrado
                        </span>
                    @endif

                </div>
            </div>

        </div>{{-- /container --}}
    </div>

    {{-- ── Chart.js: Progresión Horaria ────────────────────────────────────── --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        var data = @json($chartHorario);

        // Resaltar solo las horas que tienen actividad
        var horaActual = new Date().getHours();

        var ctx = document.getElementById('chartHorario');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Recaudación ($)',
                        data: data.totales,
                        backgroundColor: data.totales.map(function(v, i) {
                            return v > 0 ? 'rgba(99,102,241,0.75)' : 'rgba(229,231,235,0.5)';
                        }),
                        borderColor: data.totales.map(function(v, i) {
                            return v > 0 ? 'rgb(79,70,229)' : 'rgba(209,213,219,0.5)';
                        }),
                        borderWidth: 1.5,
                        borderRadius: 6,
                        yAxisID: 'yMoney',
                        order: 2,
                    },
                    {
                        label: 'Boletos',
                        data: data.boletos,
                        type: 'line',
                        borderColor: 'rgb(52,211,153)',
                        backgroundColor: 'rgba(52,211,153,0.15)',
                        borderWidth: 2.5,
                        pointBackgroundColor: data.boletos.map(function(v) {
                            return v > 0 ? 'rgb(16,185,129)' : 'rgba(209,213,219,0.5)';
                        }),
                        pointRadius: data.boletos.map(function(v) { return v > 0 ? 5 : 2; }),
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'yBoletos',
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        bodyColor: '#f8fafc',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.datasetIndex === 0) return ' Recaudación: $' + ctx.parsed.y.toFixed(2);
                                return ' Boletos: ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(241,245,249,1)' },
                        ticks: {
                            font: { size: 10 },
                            color: '#94a3b8',
                            maxRotation: 0,
                            // Mostrar solo cada 2 horas para no saturar
                            callback: function(val, idx) {
                                return idx % 2 === 0 ? this.getLabelForValue(val) : '';
                            }
                        }
                    },
                    yMoney: {
                        type: 'linear',
                        position: 'left',
                        grid: { color: 'rgba(241,245,249,1)' },
                        ticks: {
                            font: { size: 11 },
                            color: '#818cf8',
                            callback: function(v) { return '$' + v.toFixed(0); }
                        },
                        title: { display: true, text: 'Recaudación ($)', color: '#818cf8', font: { size: 11 } }
                    },
                    yBoletos: {
                        type: 'linear',
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            font: { size: 11 },
                            color: '#34d399',
                            stepSize: 1,
                            callback: function(v) { return Number.isInteger(v) ? v : ''; }
                        },
                        title: { display: true, text: 'Boletos', color: '#34d399', font: { size: 11 } }
                    }
                }
            }
        });
    });
    </script>
</x-layouts.app>
