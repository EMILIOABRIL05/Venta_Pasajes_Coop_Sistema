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
            <div class="flex items-center justify-between rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 px-6 py-4">
                <p class="text-xs text-gray-400">Actualizado: {{ now()->format('d/m/Y H:i:s') }}</p>
                <div class="flex items-center gap-3">
                    <button onclick="window.print()"
                            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-50 active:scale-95 transition-all">
                        🖨️ Imprimir
                    </button>
                    <a href="{{ route('ventanilla.reporte-pdf') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100 active:scale-95 transition-all">
                        📄 Descargar PDF
                    </a>
                    <a href="{{ route('ventanilla.ventas.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-50 active:scale-95 transition-all">
                        ← Volver
                    </a>
                    @if(!$cierreExistente)
                        <form method="POST" action="{{ route('ventas.cierre-turno.store') }}"
                              onsubmit="return confirm('¿Registrar el cierre del turno? Esta acción no se puede deshacer.')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 active:scale-95 transition-all">
                                🔒 Registrar Cierre
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-5 py-2 text-sm font-semibold text-gray-400 cursor-not-allowed">
                            ✅ Turno cerrado
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
