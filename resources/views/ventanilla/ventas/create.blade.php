<x-layouts.app title="Ventanilla – Nueva Venta">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                    🎫 Nueva Venta de Pasajes
                </h2>
                <p class="mt-1 text-sm text-gray-500">Complete los datos del pasajero, seleccione la ruta y los asientos.</p>
            </div>
            <a href="{{ route('ventanilla.ventas.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm hover:bg-gray-50 transition-all">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('ventanilla.ventas.store') }}" id="form-venta">
                @csrf

                {{-- ── Alerta de errores globales ─────────────────────────────── --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-red-50 ring-1 ring-red-200 px-5 py-4 flex gap-3">
                        <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-700">Corrige los siguientes errores:</p>
                            <ul class="mt-1 list-disc list-inside text-xs text-red-600 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- ═══════════════════════════════════════════════════════════
                         COLUMNA IZQUIERDA (span 2): Mapa de asientos
                    ═══════════════════════════════════════════════════════════ --}}
                    <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg ring-1 ring-gray-100">

                        {{-- Cabecera del mapa --}}
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-slate-50 to-white">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="14" rx="3"/>
                                        <path d="M3 10h18M8 21l1-4M16 21l-1-4M7 17h10" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Selección de asientos</p>
                                    <p class="text-sm font-semibold text-gray-800">Haz clic en un asiento disponible</p>
                                </div>
                            </div>
                            {{-- Leyenda --}}
                            <div class="flex items-center gap-3 text-xs font-semibold text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <span class="inline-block w-3 h-3 rounded bg-emerald-500"></span> Disponible
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="inline-block w-3 h-3 rounded bg-amber-400 ring-2 ring-amber-200"></span> VIP (+50%)
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="inline-block w-3 h-3 rounded bg-cyan-500 ring-2 ring-cyan-300"></span> Seleccionado
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="inline-block w-3 h-3 rounded bg-red-400 opacity-60"></span> Ocupado
                                </span>
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="max-w-xs mx-auto">

                                {{-- Frente del bus --}}
                                <div class="flex flex-col items-center mb-6 pb-5 border-b-2 border-dashed border-gray-200">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-2">⬆ Frente del Bus</p>
                                    <div class="w-16 h-16 text-slate-700">
                                        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                            <circle cx="50" cy="50" r="43" stroke="currentColor" stroke-width="7"/>
                                            <circle cx="50" cy="50" r="9" fill="currentColor"/>
                                            <line x1="50" y1="41" x2="50" y2="7" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                            <line x1="43" y1="55" x2="12" y2="74" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                            <line x1="57" y1="55" x2="88" y2="74" stroke="currentColor" stroke-width="6" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </div>

                                {{-- ── Mapa de asientos ── --}}
                                @php
                                    $asientosOcupados  = [];            // TODO: reemplazar con query real
                                    $asientosOldArray  = old('asientos', []);  // Restaurar selección previa
                                    $precioRefMap      = $rutas->min('precio_base') ?? 0;
                                @endphp

                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
                                    @for ($i = 1; $i <= 40; $i++)
                                        @php
                                            $ocupado      = true; // Deshabilitado por defecto hasta que se seleccione ruta
                                            $preselected  = in_array($i, array_map('intval', $asientosOldArray));
                                        @endphp

                                        <div class="relative group flex justify-center" data-group>

                                            <!-- VIP Star Badge (absolute above button) -->
                                            <span id="vip-star-{{ $i }}" class="vip-star hidden absolute -top-2.5 z-10 inline-flex items-center justify-center rounded-full bg-yellow-400 text-white w-5 h-5 shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.84-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z" />
                                                </svg>
                                            </span>

                                            {{-- Tooltip --}}
                                            <div class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 z-50
                                                        opacity-0 invisible group-hover:opacity-100 group-hover:visible
                                                        transition-all duration-200 w-28">
                                                <div class="bg-gray-900 text-white rounded-xl px-3 py-2 shadow-2xl text-center">
                                                    <p class="text-[11px] font-bold">Asiento {{ $i }}</p>
                                                    <p class="text-emerald-400 text-[11px] font-semibold">${{ number_format($precioRefMap, 2) }}</p>
                                                    <p data-tooltip-status class="text-[9px] mt-0.5 text-gray-400">
                                                        🟢 Disponible
                                                    </p>
                                                </div>
                                                <div class="flex justify-center">
                                                    <div class="w-2 h-2 bg-gray-900 rotate-45 -mt-1"></div>
                                                </div>
                                            </div>

                                            {{-- Botón de asiento --}}
                                            <button type="button"
                                                    id="asiento-{{ $i }}"
                                                    data-asiento="{{ $i }}"
                                                    data-seat-state="available"
                                                    @disabled(true)
                                                    class="w-full flex flex-col items-center justify-center gap-0.5 rounded-lg py-2 px-1 text-xs font-bold border transition-all duration-300 bg-emerald-500 border-emerald-600 text-white shadow-sm opacity-50 cursor-not-allowed"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 shrink-0">
                                                    <path d="M6 2a2 2 0 00-2 2v7h2V4h12v7h2V4a2 2 0 00-2-2H6z"/>
                                                    <path d="M4 13a2 2 0 012-2h12a2 2 0 012 2v3H4v-3z"/>
                                                    <path d="M6 16v4h2v-2h8v2h2v-4H6z"/>
                                                </svg>
                                                {{ $i }}
                                            </button>
                                        </div>
                                    @endfor
                                </div>

                            </div>
                        </div>

                        {{-- Inputs ocultos de asientos seleccionados (pobla JS + old()) --}}
                        {{-- old() restaura los valores si el servidor rechaza el form --}}
                        <div id="asientos-hidden-container" class="hidden">
                            @foreach (old('asientos', []) as $oldAsiento)
                                <input type="hidden" name="asientos[]" value="{{ $oldAsiento }}">
                            @endforeach
                        </div>

                        {{-- Error de asientos --}}
                        @error('asientos')
                            <p class="px-6 pb-4 text-xs text-red-600 font-medium">⚠ {{ $message }}</p>
                        @enderror
                        @error('asientos.*')
                            <p class="px-6 pb-4 text-xs text-red-600 font-medium">⚠ {{ $message }}</p>
                        @enderror

                        {{-- Safelist Tailwind --}}
                        <div class="hidden bg-cyan-500 border-cyan-600 ring-2 ring-cyan-300 ring-offset-1 text-cyan-400"></div>

                    </div>

                    {{-- ═══════════════════════════════════════════════════════════
                         COLUMNA DERECHA: Datos de la venta
                    ═══════════════════════════════════════════════════════════ --}}
                    <div class="flex flex-col gap-5">

                        {{-- ── Ruta ── --}}
                        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-100 p-6">
                            <label for="ruta_id" class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"></polygon><line x1="9" y1="3" x2="9" y2="18"></line><line x1="15" y1="6" x2="15" y2="21"></line></svg>
                                Ruta
                            </label>
                            <select name="ruta_id" id="ruta_id"
                                    class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition
                                           {{ $errors->has('ruta_id') ? 'border-red-400 bg-red-50' : '' }}">
                                <option value="">— Seleccione una ruta —</option>
                                @foreach ($rutas as $ruta)
                                    @php
                                        $bloqueada = $rutasBloqueadas->contains($ruta->id);
                                    @endphp
                                    <option value="{{ $ruta->id }}"
                                        data-precio="{{ $ruta->precio_base }}"
                                        {{ $bloqueada ? 'disabled' : '' }}
                                        {{ old('ruta_id') == $ruta->id ? 'selected' : '' }}>
                                        {{ $ruta->origen->nombre ?? '?' }} → {{ $ruta->destino->nombre ?? '?' }}
                                        (${{ number_format($ruta->precio_base, 2) }})
                                        {{ $bloqueada ? '— 🚫 Bus en Ruta ' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ruta_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ── Pasajero ── --}}
                        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-100 p-6 flex flex-col gap-4">
                            <h3 class="text-sm font-bold text-gray-800 border-b border-gray-100 pb-2 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                Datos del Pasajero
                            </h3>
                            
                            <div>
                                <label for="cedula" class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                    Cédula (10 dígitos)
                                </label>
                                <div class="relative flex items-center">
                                    <input type="text"
                                           name="cedula"
                                           id="cedula"
                                           value="{{ old('cedula') }}"
                                           placeholder="Ej: 1712345670"
                                           maxlength="10"
                                           class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition pr-10
                                                  {{ $errors->has('cedula') ? 'border-red-400 bg-red-50' : '' }}">
                                    <div id="cedula-spinner" class="hidden absolute right-3 items-center">
                                        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div id="cedula-check" class="hidden absolute right-3 items-center text-emerald-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <p id="cedula-helper" class="mt-1 text-[11px] text-gray-400"></p>
                                @error('cedula')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="nombre_completo" class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                    Nombre Completo
                                </label>
                                <input type="text"
                                       name="nombre_completo"
                                       id="nombre_completo"
                                       value="{{ old('nombre_completo') }}"
                                       placeholder="Nombre y Apellido"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition
                                              {{ $errors->has('nombre_completo') ? 'border-red-400 bg-red-50' : '' }}">
                                @error('nombre_completo')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="edad" class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                    Edad
                                </label>
                                <input type="number"
                                       name="edad"
                                       id="edad"
                                       value="{{ old('edad') }}"
                                       placeholder="Ej: 25"
                                       min="0"
                                       max="120"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition
                                              {{ $errors->has('edad') ? 'border-red-400 bg-red-50' : '' }}">
                                @error('edad')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="tiene_discapacidad" id="tiene_discapacidad" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-colors" {{ old('tiene_discapacidad') ? 'checked' : '' }}>
                                    <span class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-indigo-600 transition-colors">Posee carné de discapacidad</span>
                                </label>
                            </div>
                        </div>

                        {{-- ── Desglose de Compra ── --}}
                        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex items-center gap-3">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                </span>
                                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest">Resumen de Compra</h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="space-y-3 mb-5">
                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <span class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div> Tarifa Base de la Ruta</span>
                                        <span id="desglose-base" class="font-medium text-gray-800">$0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm text-gray-600 hidden" id="row-recargo">
                                        <span class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div> Recargo Asiento VIP (<span id="count-vip">0</span>)</span>
                                        <span id="desglose-recargo" class="font-medium text-amber-600">+$0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm text-gray-600 hidden" id="row-descuento">
                                        <span class="flex items-center gap-2"><div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div> Descuento Prioritario (50%)</span>
                                        <span id="desglose-descuento" class="font-medium text-emerald-600">-$0.00</span>
                                    </div>
                                </div>
                                
                                <div class="border-t border-gray-100 pt-4 pb-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[11px] font-bold uppercase tracking-widest text-gray-400">Total Neto a Pagar</span>
                                        <span id="desglose-neto" class="text-2xl font-extrabold text-indigo-700 tracking-tight">$0.00</span>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>Precio Base x Boleto: <strong id="desglose-unitario" class="text-gray-700">$0.00</strong></span>
                                        <span>Asientos Seleccionados: <strong id="resumen-count" class="text-gray-700">0</strong></span>
                                    </div>
                                </div>
                                <div id="resumen-asientos" class="mt-4 flex flex-wrap gap-1 min-h-[1.5rem]"></div>
                            </div>
                            
                            {{-- Se envía el unitario como input hidden, el backend procesa recargos y descuentos --}}
                            <input type="hidden" name="precio_unitario" id="precio_unitario" value="{{ old('precio_unitario') }}">
                        </div>

                        {{-- ── Botón submit ── --}}
                        <button type="submit"
                                id="btn-submit"
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-md hover:bg-indigo-700 hover:scale-[1.02] active:scale-95 transition-all duration-300
                                       disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100"
                                disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path><path d="M13 5v2"></path><path d="M13 17v2"></path><path d="M13 11v2"></path></svg>
                            Confirmar Venta
                        </button>

                    </div>{{-- /col derecha --}}
                </div>{{-- /grid --}}
            </form>

        </div>
    </div>

    {{-- ── JavaScript: selección de asientos + resumen dinámico ──────────────── --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var CLS_AVAIL    = ['bg-emerald-500', 'border-emerald-600', 'text-white', 'shadow-sm', 'hover:bg-emerald-400', 'hover:shadow-md', 'hover:-translate-y-0.5', 'hover:scale-110', 'active:scale-95', 'cursor-pointer'];
        var CLS_VIP      = ['bg-amber-400', 'border-amber-500', 'text-white', 'shadow-sm', 'hover:bg-amber-300', 'hover:shadow-md', 'hover:-translate-y-0.5', 'hover:scale-110', 'active:scale-95', 'cursor-pointer', 'ring-2', 'ring-amber-200', 'ring-offset-1'];
        var CLS_SELECTED = ['bg-cyan-500', 'border-cyan-600', 'text-white', 'shadow-md', 'ring-2', 'ring-cyan-300', 'ring-offset-1', 'hover:scale-110'];
        var CLS_OCCUPIED = ['bg-red-400', 'border-red-500', 'text-white', 'opacity-50', 'cursor-not-allowed'];

        var container   = document.getElementById('asientos-hidden-container');
        var btnSubmit   = document.getElementById('btn-submit');
        var countEl     = document.getElementById('resumen-count');
        
        var baseEl      = document.getElementById('desglose-base');
        var recargoRow  = document.getElementById('row-recargo');
        var recargoEl   = document.getElementById('desglose-recargo');
        var countVipEl  = document.getElementById('count-vip');
        var descRow     = document.getElementById('row-descuento');
        var descEl      = document.getElementById('desglose-descuento');
        var netoEl      = document.getElementById('desglose-neto');
        var unitarioEl  = document.getElementById('desglose-unitario');
        
        var asientosEl  = document.getElementById('resumen-asientos');
        var precioInput = document.getElementById('precio_unitario');

        var rutaSelect  = document.getElementById('ruta_id');
        var cedulaInput = document.getElementById('cedula');
        var nombreInput = document.getElementById('nombre_completo');
        var edadInput   = document.getElementById('edad');
        var discapacidadCb = document.getElementById('tiene_discapacidad');
        var spinner     = document.getElementById('cedula-spinner');
        var checkMark   = document.getElementById('cedula-check');
        var helper      = document.getElementById('cedula-helper');

        // ── Cambiar estilo de asiento dinámicamente ────────────────────────
        function setSeatStyle(btn, state, category) {
            var allClasses = [...CLS_AVAIL, ...CLS_VIP, ...CLS_SELECTED, ...CLS_OCCUPIED];
            allClasses.forEach(function(c) { btn.classList.remove(c); });

            var seatNum = btn.dataset.asiento;
            var star = document.getElementById('vip-star-' + seatNum);

            if (state === 'occupied') {
                btn.dataset.seatState = 'occupied';
                btn.disabled = true;
                CLS_OCCUPIED.forEach(function(c) { btn.classList.add(c); });
                if (star) star.classList.add('hidden');
            } else if (state === 'selected') {
                btn.dataset.seatState = 'selected';
                btn.disabled = false;
                CLS_SELECTED.forEach(function(c) { btn.classList.add(c); });
                if (star) star.classList.add('hidden');
            } else { // available
                btn.dataset.seatState = 'available';
                btn.disabled = false;
                if (category === 'vip') {
                    CLS_VIP.forEach(function(c) { btn.classList.add(c); });
                    if (star) star.classList.remove('hidden');
                } else {
                    CLS_AVAIL.forEach(function(c) { btn.classList.add(c); });
                    if (star) star.classList.add('hidden');
                }
            }
        }

        // ── Sincronizar Resumen ──────────────────────────────────────────────
        function syncResumen() {
            var hiddens  = container.querySelectorAll('input[name="asientos[]"]');
            var count    = hiddens.length;
            var precioBaseSelected = parseFloat(precioInput.value) || 0;
            var total = 0;

            asientosEl.innerHTML = '';
            hiddens.forEach(function (inp) {
                var seatNumber = inp.value;
                var btn = document.getElementById('asiento-' + seatNumber);
                var isVip = btn && btn.dataset.category === 'vip';
                var price = isVip ? (precioBaseSelected * 1.5) : precioBaseSelected;
                total += price;

                var badge = document.createElement('span');
                badge.className = 'inline-flex items-center justify-center w-7 h-7 rounded-md bg-indigo-200 text-indigo-800 text-xs font-bold';
                badge.textContent = seatNumber;
                asientosEl.appendChild(badge);
            });

            countEl.textContent = count;
            totalEl.textContent = '$' + total.toFixed(2);
            btnSubmit.disabled = count === 0;
        }

        // ── Recalcular Precio Unitario por Descuento o Ruta ─────────────────
        function recalcularPrecio() {
            var selectedOption = rutaSelect.options[rutaSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                baseEl.textContent = '$0.00';
                netoEl.textContent = '$0.00';
                unitarioEl.textContent = '$0.00';
                if (precioInput) precioInput.value = '0.00';
                return;
            }
            
            var precioBase = parseFloat(selectedOption.dataset.precio) || 0;
            var edadVal = parseInt(edadInput.value);
            var tieneDiscapacidad = discapacidadCb ? discapacidadCb.checked : false;
            
            var aplicaDescuento = false;
            if (!isNaN(edadVal) && (edadVal <= 12 || edadVal >= 65)) {
                aplicaDescuento = true;
            }
            if (tieneDiscapacidad) {
                aplicaDescuento = true;
            }

            var hiddens  = container.querySelectorAll('input[name="asientos[]"]');
            var count    = hiddens.length;
            
            // Simular lógica VIP (Ej: Asientos 1 al 4 son VIP en el frontend visual)
            var countVip = 0;
            hiddens.forEach(function (inp) {
                if (parseInt(inp.value) <= 4) countVip++;
            });

            var totalBase = precioBase * count;
            var totalRecargo = countVip * RECARGO_VIP;
            var subtotal = totalBase + totalRecargo;
            
            var totalDescuento = 0;
            if (aplicaDescuento) {
                totalDescuento = subtotal * 0.50;
            }
            
            var totalNeto = subtotal - totalDescuento;

            countEl.textContent = count;
            unitarioEl.textContent = '$' + precioBase.toFixed(2);
            if (precioInput) precioInput.value = precioBase.toFixed(2);

            baseEl.textContent = '$' + totalBase.toFixed(2);
            
            if (countVip > 0) {
                recargoRow.classList.remove('hidden');
                recargoRow.classList.add('flex');
                countVipEl.textContent = countVip;
                recargoEl.textContent = '+$' + totalRecargo.toFixed(2);
            } else {
                recargoRow.classList.add('hidden');
                recargoRow.classList.remove('flex');
            }

            if (aplicaDescuento && subtotal > 0) {
                descRow.classList.remove('hidden');
                descRow.classList.add('flex');
                descEl.textContent = '-$' + totalDescuento.toFixed(2);
            } else {
                descRow.classList.add('hidden');
                descRow.classList.remove('flex');
            }

            netoEl.textContent = '$' + totalNeto.toFixed(2);

            asientosEl.innerHTML = '';
            hiddens.forEach(function (inp) {
                var isVip = parseInt(inp.value) <= 4;
                var badge = document.createElement('span');
                badge.className = isVip 
                    ? 'inline-flex items-center justify-center w-7 h-7 rounded-md bg-amber-100 text-amber-800 text-xs font-bold border border-amber-300 shadow-sm'
                    : 'inline-flex items-center justify-center w-7 h-7 rounded-md bg-indigo-200 text-indigo-800 text-xs font-bold shadow-sm';
                badge.textContent = inp.value;
                asientosEl.appendChild(badge);
            });

            btnSubmit.disabled = count === 0;
        }

        function clearSelectedSeats() {
            container.innerHTML = '';
            syncResumen();
        }

        // ── AJAX Cargar Asientos y categorías ──────────────────────────────
        function cargarAsientosPorRuta(rutaId) {
            if (!rutaId) {
                clearSelectedSeats();
                document.querySelectorAll('[data-seat-state]').forEach(function(btn) {
                    btn.dataset.category = 'estandar';
                    setSeatStyle(btn, 'available', 'estandar');
                    btn.disabled = true;
                    var statusEl = btn.closest('[data-group]').querySelector('[data-tooltip-status]');
                    if (statusEl) {
                        statusEl.textContent = '🟢 Disponible';
                        statusEl.classList.remove('text-cyan-400', 'text-red-400');
                        statusEl.classList.add('text-gray-400');
                    }
                });
                return;
            }

            fetch('/ventanilla/ventas/asientos-por-ruta/' + rutaId)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Error al cargar asientos');
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (data.success) {
                        clearSelectedSeats();

                        var occupied = data.occupiedSeats || [];
                        var categories = data.seatCategories || {};
                        var maxSeats = data.numero_asientos || 40;

                        document.querySelectorAll('[data-seat-state]').forEach(function(btn) {
                            var seatNum = parseInt(btn.dataset.asiento);
                            var seatGroup = btn.closest('[data-group]');

                            if (seatNum > maxSeats) {
                                if (seatGroup) seatGroup.classList.add('hidden');
                                return;
                            } else {
                                if (seatGroup) seatGroup.classList.remove('hidden');
                            }

                            var isOccupied = occupied.includes(String(seatNum)) || occupied.includes(seatNum);
                            var cat = categories[String(seatNum)] || 'estandar';
                            btn.dataset.category = cat;

                            // Actualizar precio en tooltip
                            var priceRef = data.precio_base || 0;
                            var seatPrice = cat === 'vip' ? (priceRef * 1.5) : priceRef;
                            var priceEl = seatGroup.querySelector('.text-emerald-400');
                            if (priceEl) {
                                priceEl.textContent = '$' + seatPrice.toFixed(2);
                            }

                            var statusEl = seatGroup.querySelector('[data-tooltip-status]');
                            if (isOccupied) {
                                setSeatStyle(btn, 'occupied', cat);
                                if (statusEl) {
                                    statusEl.textContent = '🔴 Ocupado';
                                    statusEl.classList.remove('text-cyan-400', 'text-gray-400');
                                    statusEl.classList.add('text-red-400');
                                }
                            } else {
                                setSeatStyle(btn, 'available', cat);
                                if (statusEl) {
                                    statusEl.textContent = cat === 'vip' ? '🟡 VIP disponible' : '🟢 Disponible';
                                    statusEl.classList.remove('text-cyan-400', 'text-red-400');
                                    statusEl.classList.add('text-gray-400');
                                }
                            }
                        });

                        recalcularPrecio();
                    } else {
                        clearSelectedSeats();
                        alert('⚠️ ' + data.message);
                        document.querySelectorAll('[data-seat-state]').forEach(function(btn) {
                            btn.dataset.category = 'estandar';
                            setSeatStyle(btn, 'available', 'estandar');
                            btn.disabled = true;
                        });
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    alert('⚠️ Ocurrió un error al cargar el mapa de asientos.');
                });
        }

        // ── Click en asientos disponibles ─────────────────────────────────────
        document.querySelectorAll('[data-seat-state]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (this.disabled || this.dataset.seatState === 'occupied') return;

                var seat       = this.dataset.asiento;
                var isSelected = this.dataset.seatState === 'selected';
                var statusEl   = this.closest('[data-group]').querySelector('[data-tooltip-status]');
                var category   = this.dataset.category || 'estandar';

                if (isSelected) {
                    // Deseleccionar
                    setSeatStyle(this, 'available', category);
                    container.querySelector('input[value="' + seat + '"]')?.remove();
                    if (statusEl) {
                        statusEl.textContent = category === 'vip' ? '🟡 VIP disponible' : '🟢 Disponible';
                        statusEl.classList.remove('text-cyan-400');
                        statusEl.classList.add('text-gray-400');
                    }
                } else {
                    // Seleccionar
                    setSeatStyle(this, 'selected', category);
                    var inp = document.createElement('input');
                    inp.type  = 'hidden';
                    inp.name  = 'asientos[]';
                    inp.value = seat;
                    container.appendChild(inp);
                    if (statusEl) {
                        statusEl.textContent = '🔵 Seleccionado';
                        statusEl.classList.remove('text-gray-400');
                        statusEl.classList.add('text-cyan-400');
                    }
                }
                syncResumen();
            });
        });

        // ── AJAX Búsqueda de Cédula ──────────────────────────────────────────
        var abortController = null;
        cedulaInput.addEventListener('input', function () {
            // Permitir solo dígitos y limitar a 10
            var val = this.value.replace(/\D/g, '').substring(0, 10);
            this.value = val;

            if (val.length < 10) {
                spinner.classList.add('hidden');
                spinner.classList.remove('flex');
                checkMark.classList.add('hidden');
                helper.textContent = '';
                helper.className = 'mt-1 text-[11px] text-gray-400';
                return;
            }

            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();
            spinner.classList.remove('hidden');
            spinner.classList.add('flex');
            checkMark.classList.add('hidden');
            helper.textContent = 'Buscando pasajero...';
            helper.className = 'mt-1 text-[11px] text-indigo-500 font-medium animate-pulse';

            fetch('/ventanilla/pasajeros/buscar/' + val, { signal: abortController.signal })
                .then(function (response) {
                    if (response.status === 200) {
                        return response.json();
                    } else if (response.status === 404) {
                        return { exists: false };
                    } else {
                        throw new Error('Error en la búsqueda');
                    }
                })
                .then(function (data) {
                    spinner.classList.add('hidden');
                    spinner.classList.remove('flex');
                    if (data.exists && data.pasajero) {
                        checkMark.classList.remove('hidden');
                        nombreInput.value = data.pasajero.nombre_completo;
                        edadInput.value = data.pasajero.edad;
                        helper.textContent = '🟢 Pasajero encontrado en el sistema.';
                        helper.className = 'mt-1 text-[11px] text-emerald-600 font-semibold';
                        recalcularPrecio();
                    } else {
                        checkMark.classList.add('hidden');
                        helper.textContent = 'ℹ️ Nuevo pasajero (no registrado). Ingrese nombre y edad.';
                        helper.className = 'mt-1 text-[11px] text-amber-600 font-medium';
                    }
                })
                .catch(function (err) {
                    if (err.name !== 'AbortError') {
                        spinner.classList.add('hidden');
                        spinner.classList.remove('flex');
                        checkMark.classList.add('hidden');
                        helper.textContent = '⚠️ Error al buscar pasajero.';
                        helper.className = 'mt-1 text-[11px] text-red-500 font-medium';
                        console.error(err);
                    }
                });
        });

        // ── Event Listeners para recalculado automático ─────────────────────
        rutaSelect.addEventListener('change', function() {
            cargarAsientosPorRuta(this.value);
            recalcularPrecio();
        });
        edadInput.addEventListener('input', recalcularPrecio);
        if (discapacidadCb) discapacidadCb.addEventListener('change', recalcularPrecio);

        // ── Inicialización / Restauración de old() ──────────────────────────
        if (rutaSelect.value) {
            cargarAsientosPorRuta(rutaSelect.value);
        } else {
            document.querySelectorAll('[data-seat-state]').forEach(function(btn) {
                btn.disabled = true;
            });
        }
        syncResumen();
        recalcularPrecio();
        if (cedulaInput.value.length === 10) {
            cedulaInput.dispatchEvent(new Event('input'));
        }
    });
    </script>

</x-layouts.app>
