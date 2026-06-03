@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Editar Solicitud #{{ $solicitud->id }}
    </h2>
@endsection

@section('content')
<div class="py-6" x-data="solicitudForm({{ $solicitud->toJson() }}, {{ $solicitud->reporteTecnico ? $solicitud->reporteTecnico->toJson() : 'null' }})">
    <div class="mx-auto max-w-4xl">
        <form id="form-solicitud-cambio" method="POST" action="{{ route('solicitudes-cambio.update', $solicitud->id) }}">
            @csrf
            @method('PUT')

            {{-- ─── CAMPOS COMUNES (todos los roles) ──────────────────── --}}
            <div class="rounded-lg bg-white p-6 shadow-md">
                <h3 class="mb-4 text-lg font-semibold text-[#003366]">Información de la Solicitud</h3>

                {{-- TIPO DE CAMBIO (Radio Cards para Dev/Admin) --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Cambio
                    </label>
                    <div class="flex flex-wrap justify-center gap-3">
                        @php
                            $tipos = [
                                'Nueva Regla de Negocio',
                                'Ajuste de Interfaz / UX',
                                'Corrección de Error',
                                'Modificación de BD',
                                'Optimización',
                            ];
                        @endphp
                        @foreach($tipos as $tipo)
                            <label class="relative flex cursor-pointer flex-col items-center sm:w-[calc(50%-0.75rem)] md:w-[calc(33.333%-1rem)] lg:w-auto">
                                <input type="radio" name="tipo_solicitud" value="{{ $tipo }}" x-model="tipoSelected" @change="checkComplete()" class="peer sr-only" required {{ old('tipo_solicitud', $solicitud->tipo_solicitud) === $tipo ? 'checked' : '' }}>
                                <div class="w-full rounded-lg border-2 border-gray-200 p-4 text-center text-sm font-medium text-gray-700 transition-all hover:border-[#003366] hover:bg-[#003366]/5 peer-checked:border-2 peer-checked:border-[#003366] peer-checked:bg-[#003366]/10 peer-checked:text-[#003366] peer-checked:font-bold peer-checked:ring-2 peer-checked:ring-[#003366]/20">
                                    {{ $tipo }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- ORIGEN DE LA SOLICITUD (Radio Cards para Dev/Admin) --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Origen de la Solicitud
                    </label>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        @php
                            $origenes = [
                                'Retroalimentación del Usuario',
                                'Requerimiento del Ingeniero/Docente',
                                'Falla Crítica',
                                'Iniciativa Técnica',
                            ];
                        @endphp
                        @foreach($origenes as $origen)
                            <label class="relative flex cursor-pointer flex-col items-center">
                                <input type="radio" name="origen_solicitud" value="{{ $origen }}" x-model="origenSelected" @change="checkComplete()" class="peer sr-only" required {{ old('origen_solicitud', $solicitud->origen_solicitud) === $origen ? 'checked' : '' }}>
                                <div class="w-full rounded-lg border-2 border-gray-200 p-4 text-center text-sm font-medium text-gray-700 transition-all hover:border-[#003366] hover:bg-[#003366]/5 peer-checked:border-2 peer-checked:border-[#003366] peer-checked:bg-[#003366]/10 peer-checked:text-[#003366] peer-checked:font-bold peer-checked:ring-2 peer-checked:ring-[#003366]/20">
                                    {{ $origen }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-4">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">
                        Descripción
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="4" required
                              class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                              placeholder="Describa el cambio que necesita...">{{ old('descripcion', $solicitud->descripcion) }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="prioridad" class="block text-sm font-medium text-gray-700">
                        Prioridad
                    </label>
                    <select name="prioridad" id="prioridad" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                        <option value="Baja" {{ old('prioridad', $solicitud->prioridad) === 'Baja' ? 'selected' : '' }}>Baja</option>
                        <option value="Media" {{ old('prioridad', $solicitud->prioridad) === 'Media' ? 'selected' : '' }}>Media</option>
                        <option value="Alta" {{ old('prioridad', $solicitud->prioridad) === 'Alta' ? 'selected' : '' }}>Alta</option>
                    </select>
                </div>
            </div>

            {{-- ─── CAMPOS TÉCNICOS (solo developer / administrador) ──── --}}
            <div x-show="showTechnical" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4" class="mt-6 rounded-lg bg-white p-6 shadow-md border-l-4 border-[#CC0000]">
                <h3 class="mb-4 text-lg font-semibold text-[#CC0000]">
                    Campos Técnicos (Solo Personal Autorizado)
                </h3>

                {{-- Pipeline Status + Módulo Afectado --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="estado_pipeline" class="block text-sm font-medium text-gray-700">
                            Estado en Pipeline
                        </label>
                        <select name="estado_pipeline" id="estado_pipeline"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                            @php
                                $estados = ['Propuesto', 'En Desarrollo', 'Validado en Sandbox', 'Mergado', 'Desplegado', 'Rechazado'];
                            @endphp
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}" {{ old('estado_pipeline', $solicitud->estado_pipeline) === $estado ? 'selected' : '' }}>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="modulo_afectado" class="block text-sm font-medium text-gray-700">
                            Módulo Afectado
                        </label>
                        <select name="modulo_afectado" id="modulo_afectado" x-model="moduloAfectado"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                            <option value="">Seleccione...</option>
                            <option value="Operativa" {{ old('modulo_afectado', $solicitud->reporteTecnico?->modulo_afectado) === 'Operativa' ? 'selected' : '' }}>Operativa</option>
                            <option value="Ventanilla" {{ old('modulo_afectado', $solicitud->reporteTecnico?->modulo_afectado) === 'Ventanilla' ? 'selected' : '' }}>Ventanilla</option>
                            <option value="Web Client (Pasajero)" {{ old('modulo_afectado', $solicitud->reporteTecnico?->modulo_afectado) === 'Web Client (Pasajero)' ? 'selected' : '' }}>Web Client (Pasajero)</option>
                            <option value="Base de Datos" {{ old('modulo_afectado', $solicitud->reporteTecnico?->modulo_afectado) === 'Base de Datos' ? 'selected' : '' }}>Base de Datos</option>
                            <option value="Infraestructura" {{ old('modulo_afectado', $solicitud->reporteTecnico?->modulo_afectado) === 'Infraestructura' ? 'selected' : '' }}>Infraestructura</option>
                        </select>
                    </div>
                </div>

                {{-- Git Metadata --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h4 class="mb-3 text-sm font-semibold text-gray-700">Metadatos Git</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="github_issue_id" class="block text-sm font-medium text-gray-700">
                                GitHub Issue ID
                            </label>
                            <input type="text" name="github_issue_id" id="github_issue_id"
                                   value="{{ old('github_issue_id', $solicitud->reporteTecnico?->github_issue_id) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                   placeholder="#1234">
                        </div>

                        <div>
                            <label for="git_branch" class="block text-sm font-medium text-gray-700">
                                Git Branch
                            </label>
                            <input type="text" name="git_branch" id="git_branch"
                                   value="{{ old('git_branch', $solicitud->reporteTecnico?->git_branch) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                   placeholder="feature/nombre-rama">
                        </div>

                        <div>
                            <label for="commit_hash" class="block text-sm font-medium text-gray-700">
                                Commit Hash
                            </label>
                            <input type="text" name="commit_hash" id="commit_hash"
                                   value="{{ old('commit_hash', $solicitud->reporteTecnico?->commit_hash) }}"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                   placeholder="abc1234">
                        </div>
                    </div>
                </div>

                {{-- Sandbox Validation Grid (6 Modules) --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h4 class="mb-3 text-sm font-semibold text-gray-700">Validación Sandbox por Módulo</h4>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3" id="sandbox-grid">
                        @php
                            $sandboxModules = ['Operativa (CRUDs)', 'Ventanilla (Venta)', 'Web Client (Carrito)', 'Base de Datos (PostgreSQL)', 'Componentes Livewire', 'Estilos Tailwind CSS'];
                            $existingSandbox = $solicitud->reporteTecnico?->sandbox_status ?? '';
                        @endphp
                        @foreach($sandboxModules as $module)
                            <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 peer-checked:border-[#003366] peer-checked:bg-[#003366]/10">
                                <input type="checkbox" name="sandbox_modules[]" value="{{ $module }}" class="peer sr-only" {{ str_contains($existingSandbox, $module) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">{{ str_contains($module, '(') ? explode(' (', $module)[0] : $module }}</span>
                                <span class="mt-1 text-xs {{ str_contains($existingSandbox, $module) ? 'text-green-600' : 'text-gray-400' }}">{{ str_contains($existingSandbox, $module) ? 'Validado' : 'Pendiente' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="sandbox_status" id="sandbox_status" value="">
                </div>

                {{-- Risk + Rollback --}}
                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 border-t border-gray-200 pt-4">
                    <div>
                        <label for="risk_analysis" class="block text-sm font-medium text-gray-700">
                            Análisis de Riesgo
                        </label>
                        <textarea name="risk_analysis" id="risk_analysis" rows="3"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                  placeholder="Describa los riesgos potenciales...">{{ old('risk_analysis', $solicitud->reporteTecnico?->risk_analysis) }}</textarea>
                    </div>

                    <div>
                        <label for="rollback_plan" class="block text-sm font-medium text-gray-700">
                            Plan de Rollback
                        </label>
                        <textarea name="rollback_plan" id="rollback_plan" rows="3"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                  placeholder="Describa el plan de reversión...">{{ old('rollback_plan', $solicitud->reporteTecnico?->rollback_plan) }}</textarea>
                    </div>
                </div>

                {{-- Matriz de Impacto --}}
                <div class="mt-6 rounded-md bg-gray-50 p-4 border-t border-gray-200 pt-4">
                    <h4 class="mb-2 text-sm font-semibold text-gray-700">Matriz de Impacto</h4>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded p-2 transition"
                             :class="getImpactLevel() === 'bajo' ? 'bg-green-100 ring-2 ring-green-500' : 'bg-gray-100 opacity-50'">
                            <span class="font-medium" :class="getImpactLevel() === 'bajo' ? 'text-green-800' : 'text-gray-500'">Bajo</span>
                            <p :class="getImpactLevel() === 'bajo' ? 'text-green-600' : 'text-gray-400'">Sin downtime</p>
                        </div>
                        <div class="rounded p-2 transition"
                             :class="getImpactLevel() === 'medio' ? 'bg-yellow-100 ring-2 ring-yellow-500' : 'bg-gray-100 opacity-50'">
                            <span class="font-medium" :class="getImpactLevel() === 'medio' ? 'text-yellow-800' : 'text-gray-500'">Medio</span>
                            <p :class="getImpactLevel() === 'medio' ? 'text-yellow-600' : 'text-gray-400'">Requiere mantenimiento</p>
                        </div>
                        <div class="rounded p-2 transition"
                             :class="getImpactLevel() === 'alto' ? 'bg-red-100 ring-2 ring-red-500' : 'bg-gray-100 opacity-50'">
                            <span class="font-medium" :class="getImpactLevel() === 'alto' ? 'text-red-800' : 'text-gray-500'">Alto</span>
                            <p :class="getImpactLevel() === 'alto' ? 'text-red-600' : 'text-gray-400'">Downtime esperado</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('solicitudes-cambio.show', $solicitud->id) }}"
                   class="rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-300">
                    Cancelar
                </a>
                <button type="submit"
                        class="rounded-md bg-[#003366] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#002244] focus:outline-none focus:ring-2 focus:ring-[#003366] focus:ring-offset-2">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function solicitudForm(solicitud, reporteTecnico) {
    return {
        tipoSelected: solicitud.tipo_solicitud || '',
        origenSelected: solicitud.origen_solicitud || '',
        showTechnical: (solicitud.tipo_solicitud && solicitud.origen_solicitud) || false,
        moduloAfectado: reporteTecnico ? reporteTecnico.modulo_afectado : '',
        checkComplete() {
            this.showTechnical = this.tipoSelected !== '' && this.origenSelected !== '';
        },
        getImpactLevel() {
            if (['Web Client (Pasajero)', 'Ventanilla'].includes(this.moduloAfectado)) return 'medio';
            if (['Base de Datos', 'Infraestructura'].includes(this.moduloAfectado)) return 'alto';
            return 'bajo';
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-solicitud-cambio');

    form.addEventListener('submit', function (e) {
        const tipo = document.querySelector('input[name="tipo_solicitud"]:checked')?.value || document.getElementById('tipo_solicitud')?.value;
        const desc = document.getElementById('descripcion').value.trim();

        if (!tipo || !desc) {
            e.preventDefault();
            alert('Complete los campos obligatorios.');
            return;
        }

        const modulo = document.getElementById('modulo_afectado');
        if (modulo && !modulo.value) {
            e.preventDefault();
            alert('Seleccione el módulo afectado.');
            return;
        }

        const checkedBoxes = document.querySelectorAll('input[name="sandbox_modules[]"]:checked');
        const hiddenField = document.getElementById('sandbox_status');
        const moduleNames = Array.from(checkedBoxes).map(cb => cb.value);
        hiddenField.value = moduleNames.length > 0
            ? 'Probados: ' + moduleNames.join(', ')
            : 'No probado';
    });

    document.querySelectorAll('.sandbox-item input[type="checkbox"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const statusSpan = this.closest('.sandbox-item').querySelector('span:last-child');
            if (this.checked) {
                statusSpan.textContent = 'Validado';
                statusSpan.classList.remove('text-gray-400');
                statusSpan.classList.add('text-green-600');
            } else {
                statusSpan.textContent = 'Pendiente';
                statusSpan.classList.remove('text-green-600');
                statusSpan.classList.add('text-gray-400');
            }
        });
    });
});
</script>
@endpush
