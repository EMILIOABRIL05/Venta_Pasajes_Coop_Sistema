<x-layouts.app title="Ventanilla – Nueva Venta">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                    🎫 Nueva Venta de Pasajes
                </h2>
                <p class="mt-1 text-sm text-gray-500">Complete los datos del pasajero, seleccione la ruta y los asientos.</p>
            </div>
            <a href="{{ route('ventanilla.index') }}"
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

                                <div class="grid grid-cols-4 gap-2">
                                    @for ($i = 1; $i <= 40; $i++)
                                        @php
                                            $ocupado      = in_array($i, $asientosOcupados);
                                            $preselected  = in_array($i, array_map('intval', $asientosOldArray));
                                        @endphp

                                        <div class="relative group flex justify-center" data-group>

                                            {{-- Tooltip --}}
                                            <div class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 z-50
                                                        opacity-0 invisible group-hover:opacity-100 group-hover:visible
                                                        transition-all duration-200 w-28">
                                                <div class="bg-gray-900 text-white rounded-xl px-3 py-2 shadow-2xl text-center">
                                                    <p class="text-[11px] font-bold">Asiento {{ $i }}</p>
                                                    <p class="text-emerald-400 text-[11px] font-semibold">${{ number_format($precioRefMap, 2) }}</p>
                                                    <p data-tooltip-status class="text-[9px] mt-0.5
                                                        {{ $ocupado ? 'text-red-400' : ($preselected ? 'text-cyan-400' : 'text-gray-400') }}">
                                                        {{ $ocupado ? '🔴 Ocupado' : ($preselected ? '🔵 Seleccionado' : '🟢 Disponible') }}
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
                                                    data-seat-state="{{ $ocupado ? 'occupied' : ($preselected ? 'selected' : 'available') }}"
                                                    @disabled($ocupado)
                                                    class="w-full flex flex-col items-center justify-center gap-0.5 rounded-lg py-2 px-1 text-xs font-bold border transition-all duration-150
                                                        {{ $ocupado
                                                            ? 'bg-red-400 border-red-500 text-white opacity-50 cursor-not-allowed'
                                                            : ($preselected
                                                                ? 'bg-cyan-500 border-cyan-600 text-white shadow-md ring-2 ring-cyan-300 ring-offset-1'
                                                                : 'bg-emerald-500 border-emerald-600 text-white shadow-sm hover:bg-emerald-400 hover:shadow-md hover:-translate-y-0.5 active:scale-95 cursor-pointer')
                                                        }}"
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
                                    <option value="{{ $ruta->id }}"
                                        {{ old('ruta_id') == $ruta->id ? 'selected' : '' }}>
                                        {{ $ruta->origen->nombre ?? '?' }} → {{ $ruta->destino->nombre ?? '?' }}
                                        (${{ number_format($ruta->precio_base, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ruta_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ── Pasajero ── --}}
                        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-100 p-6">
                            <label for="pasajero_id" class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                ID del Pasajero
                            </label>
                            <input type="number"
                                   name="pasajero_id"
                                   id="pasajero_id"
                                   value="{{ old('pasajero_id') }}"
                                   placeholder="Ej: 42"
                                   min="1"
                                   class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition
                                          {{ $errors->has('pasajero_id') ? 'border-red-400 bg-red-50' : '' }}">
                            @error('pasajero_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ── Precio unitario ── --}}
                        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-100 p-6">
                            <label for="precio_unitario" class="block text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">
                                Precio por asiento (USD)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-sm font-semibold">$</span>
                                <input type="number"
                                       name="precio_unitario"
                                       id="precio_unitario"
                                       value="{{ old('precio_unitario') }}"
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0.01"
                                       class="w-full rounded-xl border-gray-200 bg-gray-50 pl-7 text-sm font-medium text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition
                                              {{ $errors->has('precio_unitario') ? 'border-red-400 bg-red-50' : '' }}">
                            </div>
                            @error('precio_unitario')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ── Resumen de selección ── --}}
                        <div class="bg-indigo-50 rounded-2xl ring-1 ring-indigo-100 p-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-indigo-400 mb-3">Resumen</p>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Asientos seleccionados:</span>
                                <span id="resumen-count" class="font-bold text-gray-800">0</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Total estimado:</span>
                                <span id="resumen-total" class="font-bold text-emerald-700">$0.00</span>
                            </div>
                            <div id="resumen-asientos" class="mt-3 flex flex-wrap gap-1 min-h-[1.5rem]"></div>
                        </div>

                        {{-- ── Botón submit ── --}}
                        <button type="submit"
                                id="btn-submit"
                                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-md hover:bg-indigo-700 active:scale-95 transition-all duration-150
                                       disabled:opacity-40 disabled:cursor-not-allowed"
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
        var CLS_AVAIL    = ['bg-emerald-500', 'border-emerald-600', 'shadow-sm'];
        var CLS_SELECTED = ['bg-cyan-500', 'border-cyan-600', 'shadow-md', 'ring-2', 'ring-cyan-300', 'ring-offset-1'];

        var container   = document.getElementById('asientos-hidden-container');
        var btnSubmit   = document.getElementById('btn-submit');
        var countEl     = document.getElementById('resumen-count');
        var totalEl     = document.getElementById('resumen-total');
        var asientosEl  = document.getElementById('resumen-asientos');
        var precioInput = document.getElementById('precio_unitario');

        // ── Restaurar inputs ocultos desde old() ──────────────────────────────
        // Los inputs de old('asientos') ya vienen pre-renderizados en el HTML.
        // Aquí sólo sincronizamos el estado visual del resumen al cargar la página.
        function syncResumen() {
            var hiddens  = container.querySelectorAll('input[name="asientos[]"]');
            var count    = hiddens.length;
            var precio   = parseFloat(precioInput.value) || 0;
            var total    = (precio * count).toFixed(2);

            countEl.textContent = count;
            totalEl.textContent = '$' + total;
            asientosEl.innerHTML = '';
            hiddens.forEach(function (inp) {
                var badge = document.createElement('span');
                badge.className = 'inline-flex items-center justify-center w-7 h-7 rounded-md bg-indigo-200 text-indigo-800 text-xs font-bold';
                badge.textContent = inp.value;
                asientosEl.appendChild(badge);
            });
            btnSubmit.disabled = count === 0;
        }

        // ── Click en asientos disponibles ─────────────────────────────────────
        document.querySelectorAll('[data-seat-state]').forEach(function (btn) {
            if (btn.dataset.seatState === 'occupied') return;

            btn.addEventListener('click', function () {
                var seat       = this.dataset.asiento;
                var isSelected = this.dataset.seatState === 'selected';
                var statusEl   = this.closest('[data-group]').querySelector('[data-tooltip-status]');

                if (isSelected) {
                    // Deseleccionar
                    this.dataset.seatState = 'available';
                    CLS_SELECTED.forEach(function (c) { btn.classList.remove(c); });
                    CLS_AVAIL.forEach(function (c)    { btn.classList.add(c); });
                    container.querySelector('input[value="' + seat + '"]')?.remove();
                    if (statusEl) { statusEl.textContent = '🟢 Disponible'; statusEl.classList.replace('text-cyan-400', 'text-gray-400'); }
                } else {
                    // Seleccionar
                    this.dataset.seatState = 'selected';
                    CLS_AVAIL.forEach(function (c)    { btn.classList.remove(c); });
                    CLS_SELECTED.forEach(function (c) { btn.classList.add(c); });
                    var inp = document.createElement('input');
                    inp.type  = 'hidden';
                    inp.name  = 'asientos[]';
                    inp.value = seat;
                    container.appendChild(inp);
                    if (statusEl) { statusEl.textContent = '🔵 Seleccionado'; statusEl.classList.replace('text-gray-400', 'text-cyan-400'); }
                }
                syncResumen();
            });
        });

        // Actualizar total al cambiar el precio
        precioInput.addEventListener('input', syncResumen);

        // Sincronía inicial (restaura old() al recargar tras error)
        syncResumen();
    });
    </script>

</x-layouts.app>
