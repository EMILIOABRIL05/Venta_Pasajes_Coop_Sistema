@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Auditoría y Git Flow Dashboard
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        {{-- ─── KPI Cards ──────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-8">
            <div class="rounded-lg bg-white p-6 shadow-md border-l-4 border-[#003366]">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Solicitudes</p>
                <p class="mt-2 text-3xl font-bold text-[#003366]">{{ $totalSolicitudes }}</p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-md border-l-4 border-green-500">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Tasa de Éxito</p>
                <p class="mt-2 text-3xl font-bold text-green-600">{{ $tasaExito }}%</p>
                <p class="mt-1 text-xs text-gray-400">Desplegados vs Total</p>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-md border-l-4 border-red-500">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Prioridad Alta</p>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ $emergencias }}</p>
            </div>
        </div>

        {{-- ─── Charts Grid ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
            <div class="rounded-lg bg-white p-6 shadow-md">
                <h3 class="mb-4 text-lg font-semibold text-[#003366]">Distribución por Estado</h3>
                <div class="relative w-full" style="aspect-ratio: 16 / 9;">
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-md">
                <h3 class="mb-4 text-lg font-semibold text-[#003366]">Rendimiento del Equipo</h3>
                <div class="relative w-full" style="aspect-ratio: 16 / 9;">
                    <canvas id="chartDesarrolladores"></canvas>
                </div>
            </div>
        </div>

        {{-- ─── Recent Changes Table ───────────────────────────────────────── --}}
        <div class="rounded-lg bg-white shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-white">
                <h3 class="text-lg font-semibold text-[#003366]">Cambios Recientes</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Solicitante</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Evaluador</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Commit Hash</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($cambiosRecientes as $cambio)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cambio->tipo_solicitud }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cambio->usuario->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-[#003366] font-medium">{{ $cambio->evaluador->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $cambio->reporteTecnico?->commit_hash ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'Propuesto'           => 'bg-red-100 text-red-800',
                                        'En Desarrollo'       => 'bg-blue-100 text-blue-800',
                                        'Validado en Sandbox' => 'bg-purple-100 text-purple-800',
                                        'Mergado'             => 'bg-indigo-100 text-indigo-800',
                                        'Desplegado'          => 'bg-green-100 text-green-800',
                                        'Rechazado'           => 'bg-gray-100 text-gray-800',
                                    ];
                                    $badgeClass = $statusColors[$cambio->estado_pipeline] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                    {{ $cambio->estado_pipeline }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $priorityColors = [
                                        'Alta'  => 'bg-red-500',
                                        'Media' => 'bg-yellow-500',
                                        'Baja'  => 'bg-green-500',
                                    ];
                                    $priorityDot = $priorityColors[$cambio->prioridad] ?? 'bg-gray-500';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 text-sm text-gray-700">
                                    <span class="h-2 w-2 rounded-full {{ $priorityDot }}"></span>
                                    {{ $cambio->prioridad }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cambio->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                No hay solicitudes de cambio registradas aún.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $cambiosRecientes->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const estadoData = @json($porEstado);
    const devLabels = @json($devLabels);
    const devData = @json($devData);

    const statusColors = {
        'Propuesto': '#EF4444',
        'En Desarrollo': '#3B82F6',
        'Validado en Sandbox': '#A855F7',
        'Mergado': '#6366F1',
        'Desplegado': '#22C55E',
        'Rechazado': '#6B7280',
    };

    // Pie Chart - Status Distribution
    const ctxEstado = document.getElementById('chartEstado');
    if (ctxEstado && Object.keys(estadoData).length > 0) {
        new Chart(ctxEstado, {
            type: 'pie',
            data: {
                labels: Object.keys(estadoData),
                datasets: [{
                    data: Object.values(estadoData),
                    backgroundColor: Object.keys(estadoData).map(label => statusColors[label] || '#9CA3AF'),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 16, usePointStyle: true }
                    }
                }
            }
        });
    }

    // Bar Chart - Developer Performance
    const ctxDev = document.getElementById('chartDesarrolladores');
    if (ctxDev && devLabels.length > 0 && devData.length > 0) {
        new Chart(ctxDev, {
            type: 'bar',
            data: {
                labels: devLabels,
                datasets: [{
                    label: 'Solicitudes',
                    data: devData,
                    backgroundColor: '#003366',
                    borderRadius: 6,
                    barThickness: 40,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#F3F4F6' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
@endpush
