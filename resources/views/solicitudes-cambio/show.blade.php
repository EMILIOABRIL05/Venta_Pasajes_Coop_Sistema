@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Solicitud #{{ $solicitud->id }}
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-5xl">

        {{-- ─── Encabezado con estado y prioridad ──────────────────────── --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-2xl font-bold text-[#003366]">{{ $solicitud->tipo_solicitud }}</h3>
                <p class="text-sm text-gray-500">
                    Solicitado por <span class="font-medium text-gray-700">{{ $solicitud->usuario->name }}</span>
                    &middot; {{ $solicitud->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColors = [
                        'Propuesto'           => 'bg-red-100 text-red-800',
                        'En Desarrollo'       => 'bg-blue-100 text-blue-800',
                        'Validado en Sandbox' => 'bg-purple-100 text-purple-800',
                        'Mergado'             => 'bg-indigo-100 text-indigo-800',
                        'Desplegado'          => 'bg-green-100 text-green-800',
                        'Rechazado'           => 'bg-gray-100 text-gray-800',
                    ];
                    $badgeClass = $statusColors[$solicitud->estado_pipeline] ?? 'bg-gray-100 text-gray-800';

                    $priorityColors = [
                        'Alta'  => 'bg-red-500',
                        'Media' => 'bg-yellow-500',
                        'Baja'  => 'bg-green-500',
                    ];
                    $priorityDot = $priorityColors[$solicitud->prioridad] ?? 'bg-gray-500';

                    $pipelineSteps = [
                        'Propuesto'           => ['En Desarrollo', 'Rechazado'],
                        'En Desarrollo'       => ['Validado en Sandbox', 'Rechazado'],
                        'Validado en Sandbox' => ['Mergado', 'Rechazado'],
                        'Mergado'             => ['Desplegado'],
                        'Desplegado'          => [],
                        'Rechazado'           => [],
                    ];
                    $nextSteps = $pipelineSteps[$solicitud->estado_pipeline] ?? [];
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $badgeClass }}">
                    {{ $solicitud->estado_pipeline }}
                </span>
                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700">
                    <span class="h-2.5 w-2.5 rounded-full {{ $priorityDot }}"></span>
                    {{ $solicitud->prioridad }}
                </span>
            </div>
        </div>

        {{-- ─── Action Buttons (Developer/Admin Only) ──────────────────── --}}
        @if($isDeveloper && count($nextSteps) > 0)
        <div class="mb-6 rounded-lg bg-white p-4 shadow-md border border-gray-200">
            <h4 class="mb-3 text-sm font-semibold text-gray-700">Avanzar en Pipeline</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($nextSteps as $nextStep)
                    @php
                        $btnColors = [
                            'En Desarrollo'       => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
                            'Validado en Sandbox' => 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500',
                            'Mergado'             => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
                            'Desplegado'          => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
                            'Rechazado'           => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                        ];
                        $btnClass = $btnColors[$nextStep] ?? 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500';
                    @endphp
                    <form method="POST" action="{{ route('solicitudes-cambio.update-status', $solicitud->id) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado_pipeline" value="{{ $nextStep }}">
                        <button type="submit"
                                class="rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $btnClass }}">
                            {{ $nextStep === 'Rechazado' ? 'Rechazar' : 'Mover a: ' . $nextStep }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        @elseif($isDeveloper && $solicitud->estado_pipeline === 'Rechazado')
        <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-800 border border-red-200">
            <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Esta solicitud ha sido rechazada.
        </div>
        @endif

        {{-- ─── Descripción ────────────────────────────────────────────── --}}
        <div class="mb-6 rounded-lg bg-white p-6 shadow-md">
            <h4 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-400">Descripción</h4>
            <p class="whitespace-pre-wrap text-gray-800">{{ $solicitud->descripcion }}</p>
        </div>

        {{-- ─── Campos Técnicos (solo developer / admin) ───────────────── --}}
        @if($isDeveloper && $solicitud->reporteTecnico)
        <div class="rounded-lg bg-white p-6 shadow-md border-l-4 border-[#CC0000]">
            <h4 class="mb-4 text-lg font-semibold text-[#CC0000]">
                Reporte Técnico
            </h4>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                {{-- Metadatos Git --}}
                <div>
                    <h5 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide">Metadatos Git</h5>
                    <dl class="space-y-3">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-sm text-gray-500">Módulo Afectado</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $solicitud->reporteTecnico->modulo_afectado ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-sm text-gray-500">GitHub Issue</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $solicitud->reporteTecnico->github_issue_id ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-sm text-gray-500">Git Branch</dt>
                            <dd class="text-sm font-medium text-gray-900 font-mono">{{ $solicitud->reporteTecnico->git_branch ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-sm text-gray-500">Commit Hash</dt>
                            <dd class="text-sm font-medium text-gray-900 font-mono">{{ $solicitud->reporteTecnico->commit_hash ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Sandbox + Riesgo --}}
                <div>
                    <h5 class="mb-3 text-sm font-semibold text-gray-500 uppercase tracking-wide">Validación</h5>
                    <dl class="space-y-3">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-sm text-gray-500">Sandbox Status</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $solicitud->reporteTecnico->sandbox_status ?? 'No probado' }}</dd>
                        </div>
                    </dl>

                    @if($solicitud->reporteTecnico->risk_analysis)
                    <div class="mt-4">
                        <h5 class="mb-1 text-sm font-semibold text-gray-500 uppercase tracking-wide">Análisis de Riesgo</h5>
                        <p class="whitespace-pre-wrap rounded-md bg-gray-50 p-3 text-sm text-gray-700">{{ $solicitud->reporteTecnico->risk_analysis }}</p>
                    </div>
                    @endif

                    @if($solicitud->reporteTecnico->rollback_plan)
                    <div class="mt-4">
                        <h5 class="mb-1 text-sm font-semibold text-gray-500 uppercase tracking-wide">Plan de Rollback</h5>
                        <p class="whitespace-pre-wrap rounded-md bg-gray-50 p-3 text-sm text-gray-700">{{ $solicitud->reporteTecnico->rollback_plan }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @elseif($isDeveloper)
        <div class="rounded-lg bg-white p-6 shadow-md border-l-4 border-gray-300">
            <p class="text-sm text-gray-500">Esta solicitud no tiene reporte técnico asociado aún.</p>
        </div>
        @endif

        {{-- ─── Botón Volver ───────────────────────────────────────────── --}}
        <div class="mt-6">
            <a href="{{ route('solicitudes-cambio.index') }}"
               class="inline-flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al Listado
            </a>
        </div>
    </div>
</div>
@endsection
