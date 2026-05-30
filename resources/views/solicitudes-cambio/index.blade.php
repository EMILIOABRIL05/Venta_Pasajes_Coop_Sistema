@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Listado de Solicitudes de Cambio
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-7xl">
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Mostrando {{ $solicitudes->firstItem() ?? 0 }}-{{ $solicitudes->lastItem() ?? 0 }} de {{ $solicitudes->total() }} registros
            </p>
            <a href="{{ route('solicitudes-cambio.create') }}"
               class="rounded-md bg-[#003366] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#002244]">
                + Nueva Solicitud
            </a>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Prioridad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Estado Pipeline</th>
                        @if($isDeveloper)
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Módulo Afectado</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($solicitudes as $solicitud)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            #{{ $solicitud->id }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $solicitud->usuario->name }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $solicitud->tipo_solicitud }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if($solicitud->prioridad === 'Alta')
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Alta</span>
                            @elseif($solicitud->prioridad === 'Media')
                                <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">Media</span>
                            @else
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Baja</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @php
                                $statusColors = [
                                    'Propuesto'            => 'bg-red-100 text-red-800',
                                    'En Desarrollo'        => 'bg-blue-100 text-blue-800',
                                    'Validado en Sandbox'  => 'bg-purple-100 text-purple-800',
                                    'Mergado'              => 'bg-indigo-100 text-indigo-800',
                                    'Desplegado'           => 'bg-green-100 text-green-800',
                                    'Rechazado'            => 'bg-gray-100 text-gray-800',
                                ];
                                $badgeClass = $statusColors[$solicitud->estado_pipeline] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                {{ $solicitud->estado_pipeline }}
                            </span>
                        </td>
                        @if($isDeveloper)
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                            {{ $solicitud->reporteTecnico?->modulo_afectado ?? '—' }}
                        </td>
                        @endif
                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                            <a href="{{ route('solicitudes-cambio.show', $solicitud->id) }}"
                               class="text-[#003366] hover:text-[#002244]">
                                Ver / Gestionar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isDeveloper ? 7 : 6 }}" class="px-6 py-10 text-center text-sm text-gray-500">
                            No hay solicitudes de cambio registradas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $solicitudes->links() }}
        </div>
    </div>
</div>
@endsection
