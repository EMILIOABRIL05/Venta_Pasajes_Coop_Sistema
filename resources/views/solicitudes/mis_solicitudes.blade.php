@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Mis Solicitudes de Cambio
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        {{-- ─── Header con botón de nueva solicitud ──────────────────────────── --}}
        <div class="mb-6 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Revisa el estado de tus solicitudes y los comentarios del equipo técnico.
            </p>
            <a href="{{ route('solicitudes-cambio.create') }}"
               class="inline-flex items-center gap-2 rounded-md bg-[#003366] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#002244] focus:outline-none focus:ring-2 focus:ring-[#003366] focus:ring-offset-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Solicitud
            </a>
        </div>

        {{-- ─── Tabla de solicitudes ──────────────────────────────────────────── --}}
        <div class="rounded-lg bg-white shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Módulo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($solicitudes as $sol)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $sol->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 max-w-xs truncate">
                                {{ $sol->tipo_solicitud }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $sol->reporteTecnico?->modulo_afectado ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusMap = [
                                        'Propuesto'           => ['bg-red-100 text-red-800', 'Propuesto'],
                                        'En Desarrollo'       => ['bg-yellow-100 text-yellow-800', 'En Desarrollo'],
                                        'Validado en Sandbox' => ['bg-purple-100 text-purple-800', 'Validado'],
                                        'Mergado'             => ['bg-indigo-100 text-indigo-800', 'Mergado'],
                                        'Desplegado'          => ['bg-green-100 text-green-800', 'Desplegado'],
                                        'Rechazado'           => ['bg-red-100 text-red-800', 'Rechazado'],
                                    ];
                                    [$badgeClass, $label] = $statusMap[$sol->estado_pipeline] ?? ['bg-gray-100 text-gray-800', $sol->estado_pipeline];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('solicitudes-cambio.show', $sol->id) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Ver Detalle
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-3 text-sm font-medium text-gray-900">No tienes solicitudes registradas</p>
                                <p class="mt-1 text-sm text-gray-500">Crea tu primera solicitud de cambio para comenzar.</p>
                                <a href="{{ route('solicitudes-cambio.create') }}"
                                   class="mt-4 inline-flex items-center gap-2 rounded-md bg-[#003366] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#002244]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Crear Solicitud
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
