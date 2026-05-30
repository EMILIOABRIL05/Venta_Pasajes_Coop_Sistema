@extends('layouts.app')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
        {{ auth()->user()->hasRole('developer') || auth()->user()->hasRole('admin')
            ? 'Formulario Técnico de Cambio'
            : 'Solicitar Cambio' }}
    </h2>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-4xl">
        <form id="form-solicitud-cambio" method="POST" action="{{ route('solicitudes-cambio.store') }}">
            @csrf

            {{-- ─── CAMPOS COMUNES (todos los roles) ──────────────────── --}}
            <div class="rounded-lg bg-white p-6 shadow-md">
                <h3 class="mb-4 text-lg font-semibold text-[#003366]">Información de la Solicitud</h3>

                <div class="mb-4">
                    <label for="tipo_solicitud" class="block text-sm font-medium text-gray-700">
                        Tipo de Solicitud
                    </label>
                    <select name="tipo_solicitud" id="tipo_solicitud" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                        <option value="">Seleccione...</option>
                        <option value="Mejora funcional">Mejora funcional</option>
                        <option value="Reporte de error">Reporte de error</option>
                        <option value="Nueva característica">Nueva característica</option>
                        <option value="Cambio de diseño">Cambio de diseño</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="descripcion" class="block text-sm font-medium text-gray-700">
                        Descripción
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="4" required
                              class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                              placeholder="Describa el cambio que necesita..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="prioridad" class="block text-sm font-medium text-gray-700">
                        Prioridad
                    </label>
                    <select name="prioridad" id="prioridad" required
                            class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                        <option value="Baja">Baja</option>
                        <option value="Media" selected>Media</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>
            </div>

            {{-- ─── CAMPOS TÉCNICOS (solo developer / administrador) ──── --}}
            @if(auth()->user()->hasRole('developer') || auth()->user()->hasRole('admin'))
            <div class="mt-6 rounded-lg bg-white p-6 shadow-md border-l-4 border-[#CC0000]">
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
                            <option value="Propuesto" selected>Propuesto</option>
                            <option value="En Desarrollo">En Desarrollo</option>
                            <option value="Validado en Sandbox">Validado en Sandbox</option>
                            <option value="Mergado">Mergado</option>
                            <option value="Desplegado">Desplegado</option>
                            <option value="Rechazado">Rechazado</option>
                        </select>
                    </div>

                    <div>
                        <label for="modulo_afectado" class="block text-sm font-medium text-gray-700">
                            Módulo Afectado
                        </label>
                        <select name="modulo_afectado" id="modulo_afectado" required
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                            <option value="">Seleccione...</option>
                            <option value="Operativa">Operativa</option>
                            <option value="Ventanilla">Ventanilla</option>
                            <option value="Web Client (Pasajero)">Web Client (Pasajero)</option>
                            <option value="Base de Datos">Base de Datos</option>
                            <option value="Infraestructura">Infraestructura</option>
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
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                   placeholder="#1234">
                        </div>

                        <div>
                            <label for="git_branch" class="block text-sm font-medium text-gray-700">
                                Git Branch
                            </label>
                            <input type="text" name="git_branch" id="git_branch"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                   placeholder="feature/nombre-rama">
                        </div>

                        <div>
                            <label for="commit_hash" class="block text-sm font-medium text-gray-700">
                                Commit Hash
                            </label>
                            <input type="text" name="commit_hash" id="commit_hash"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                   placeholder="abc1234">
                        </div>
                    </div>
                </div>

                {{-- Sandbox Validation Grid (6 Modules) --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h4 class="mb-3 text-sm font-semibold text-gray-700">Validación Sandbox por Módulo</h4>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3" id="sandbox-grid">
                        <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 has-[:checked]:border-[#003366] has-[:checked]:bg-[#003366]/10">
                            <input type="checkbox" name="sandbox_modules[]" value="Operativa (CRUDs)" class="peer sr-only">
                            <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">Operativa (CRUDs)</span>
                            <span class="mt-1 text-xs text-gray-400 peer-checked:text-[#003366]">Pendiente</span>
                        </label>

                        <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 has-[:checked]:border-[#003366] has-[:checked]:bg-[#003366]/10">
                            <input type="checkbox" name="sandbox_modules[]" value="Ventanilla (Venta)" class="peer sr-only">
                            <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">Ventanilla (Venta)</span>
                            <span class="mt-1 text-xs text-gray-400 peer-checked:text-[#003366]">Pendiente</span>
                        </label>

                        <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 has-[:checked]:border-[#003366] has-[:checked]:bg-[#003366]/10">
                            <input type="checkbox" name="sandbox_modules[]" value="Web Client (Carrito)" class="peer sr-only">
                            <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">Web Client (Carrito)</span>
                            <span class="mt-1 text-xs text-gray-400 peer-checked:text-[#003366]">Pendiente</span>
                        </label>

                        <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 has-[:checked]:border-[#003366] has-[:checked]:bg-[#003366]/10">
                            <input type="checkbox" name="sandbox_modules[]" value="Base de Datos (PostgreSQL)" class="peer sr-only">
                            <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">Base de Datos</span>
                            <span class="mt-1 text-xs text-gray-400 peer-checked:text-[#003366]">Pendiente</span>
                        </label>

                        <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 has-[:checked]:border-[#003366] has-[:checked]:bg-[#003366]/10">
                            <input type="checkbox" name="sandbox_modules[]" value="Componentes Livewire" class="peer sr-only">
                            <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">Livewire</span>
                            <span class="mt-1 text-xs text-gray-400 peer-checked:text-[#003366]">Pendiente</span>
                        </label>

                        <label class="sandbox-item relative flex cursor-pointer flex-col items-center rounded-lg border-2 border-gray-200 p-4 text-center transition hover:border-[#003366] hover:bg-[#003366]/5 has-[:checked]:border-[#003366] has-[:checked]:bg-[#003366]/10">
                            <input type="checkbox" name="sandbox_modules[]" value="Estilos Tailwind CSS" class="peer sr-only">
                            <span class="text-sm font-medium text-gray-700 peer-checked:text-[#003366]">Tailwind CSS</span>
                            <span class="mt-1 text-xs text-gray-400 peer-checked:text-[#003366]">Pendiente</span>
                        </label>
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
                                  placeholder="Describa los riesgos potenciales..."></textarea>
                    </div>

                    <div>
                        <label for="rollback_plan" class="block text-sm font-medium text-gray-700">
                            Plan de Rollback
                        </label>
                        <textarea name="rollback_plan" id="rollback_plan" rows="3"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-[#003366] focus:ring-[#003366]"
                                  placeholder="Describa el plan de reversión..."></textarea>
                    </div>
                </div>

                {{-- Matriz de Impacto --}}
                <div class="mt-6 rounded-md bg-gray-50 p-4 border-t border-gray-200 pt-4">
                    <h4 class="mb-2 text-sm font-semibold text-gray-700">Matriz de Impacto</h4>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded bg-green-100 p-2">
                            <span class="font-medium text-green-800">Bajo</span>
                            <p class="text-green-600">Sin downtime</p>
                        </div>
                        <div class="rounded bg-yellow-100 p-2">
                            <span class="font-medium text-yellow-800">Medio</span>
                            <p class="text-yellow-600">Requiere mantenimiento</p>
                        </div>
                        <div class="rounded bg-red-100 p-2">
                            <span class="font-medium text-red-800">Alto</span>
                            <p class="text-red-600">Downtime esperado</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-md bg-[#003366] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#002244] focus:outline-none focus:ring-2 focus:ring-[#003366] focus:ring-offset-2">
                    Enviar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-solicitud-cambio');

    form.addEventListener('submit', function (e) {
        const tipo = document.getElementById('tipo_solicitud').value;
        const desc = document.getElementById('descripcion').value.trim();

        if (!tipo || !desc) {
            e.preventDefault();
            alert('Complete los campos obligatorios.');
            return;
        }

        @if(auth()->user()->hasRole('developer') || auth()->user()->hasRole('admin'))
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
        @endif
    });

    @if(auth()->user()->hasRole('developer') || auth()->user()->hasRole('admin'))
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
    @endif
});
</script>
@endpush
