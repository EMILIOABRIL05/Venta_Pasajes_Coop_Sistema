<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50 py-10" x-data="{ open: false, viajeId: null }">
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:flash-message.window="
            message = $event.detail.message;
            type = $event.detail.type;
            show = true;
            setTimeout(() => show = false, 3000);
         "
         x-show="show"
         x-transition
         class="fixed top-4 right-4 z-50 rounded-2xl px-6 py-4 shadow-xl text-sm font-medium"
         :class="type === 'error' ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'">
        <span x-text="message"></span>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-xl shadow-slate-200/60 backdrop-blur">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Operativa</span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Armar Hoja de Ruta</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Genera viajes asignando buses a frecuencias existentes.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <section class="md:col-span-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Generar Nuevo Viaje</h2>
                        <p class="text-sm text-slate-500">Selecciona fecha, frecuencia y bus.</p>
                    </div>
                </div>

                <form wire:submit.prevent="saveViaje" class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="fecha">Fecha</label>
                        <input type="date" id="fecha" wire:model="fecha" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('fecha') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="frecuencia_id">Frecuencia</label>
                        <select id="frecuencia_id" wire:model="frecuencia_id" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione una frecuencia</option>
                            @foreach($frecuencias as $frecuencia)
                                <option value="{{ $frecuencia->id }}">{{ $frecuencia->hora_salida }} - ({{ $frecuencia->ruta->origen->nombre }} a {{ $frecuencia->ruta->destino->nombre }})</option>
                            @endforeach
                        </select>
                        @error('frecuencia_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="bus_id">Bus</label>
                        <select id="bus_id" wire:model="bus_id" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione un bus</option>
                            @foreach($buses as $bus)
                                <option value="{{ $bus->id }}">{{ $bus->placa }} (Asientos: {{ $bus->numero_asientos }})</option>
                            @endforeach
                        </select>
                        @error('bus_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="chofer_user_id">Chofer (opcional)</label>
                        <select id="chofer_user_id" wire:model="chofer_user_id" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Sin asignar</option>
                            @foreach($choferes as $chofer)
                                <option value="{{ $chofer->id }}">{{ $chofer->name }}</option>
                            @endforeach
                        </select>
                        @error('chofer_user_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Generar Viaje
                        </button>
                    </div>
                </form>
            </section>

            <section class="md:col-span-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60">
                <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Hojas de Ruta / Viajes</h2>
                        <p class="text-sm text-slate-500">Programación de viajes existentes y su estado.</p>
                    </div>
                </div>

                <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">
                    @forelse ($viajes as $viaje)
                        <article class="rounded-2xl border border-slate-200 p-5 transition hover:border-emerald-200 hover:shadow-md {{ in_array($viaje->estado, ['Finalizada', 'cancelado']) ? 'opacity-75' : '' }}">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $viaje->fecha->format('d/m/Y') }}</h3>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $viaje->frecuencia?->hora_salida ?? 'Sin hora' }}</span>
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                                            @if($viaje->estado === 'En Terminal') bg-blue-100 text-blue-700
                                            @elseif($viaje->estado === 'En Curso') bg-amber-100 text-amber-700
                                            @elseif($viaje->estado === 'Finalizada') bg-emerald-100 text-emerald-700
                                            @elseif($viaje->estado === 'cancelado') bg-rose-100 text-rose-700
                                            @else bg-slate-100 text-slate-700
                                            @endif">
                                            {{ $viaje->estado }}
                                        </span>
                                    </div>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                                        {{ $viaje->frecuencia?->ruta?->origen?->nombre ?? 'Sin origen' }} a {{ $viaje->frecuencia?->ruta?->destino?->nombre ?? 'Sin destino' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Bus: {{ $viaje->bus?->placa ?? 'Sin bus asignado' }} 
                                        @if ($viaje->bus?->numero_asientos)
                                            (Asientos: {{ $viaje->bus->numero_asientos }})
                                        @endif
                                        @if ($viaje->chofer)
                                            · Chofer: {{ $viaje->chofer->name ?? 'Sin nombre' }}
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" 
                                        wire:click="cambiarEstado({{ $viaje->id }}, 'En Terminal')" 
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold transition {{ $viaje->estado === 'En Terminal' ? 'bg-blue-600 text-white' : 'border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                                        En Terminal
                                    </button>
                                    
                                    <button type="button" 
                                        wire:click="cambiarEstado({{ $viaje->id }}, 'En Curso')" 
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold transition {{ $viaje->estado === 'En Curso' ? 'bg-amber-600 text-white' : 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                                        En Curso
                                    </button>
                                    
                                    <button type="button" 
                                        wire:click="cambiarEstado({{ $viaje->id }}, 'Finalizada')" 
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold transition {{ $viaje->estado === 'Finalizada' ? 'bg-emerald-600 text-white' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        Finalizada
                                    </button>
                                    
                                    @if ($viaje->estado === 'En Terminal')
                                        <button type="button" 
                                            @click="open = true; viajeId = {{ $viaje->id }}" 
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            class="rounded-lg px-2 py-1 text-xs font-semibold border border-rose-200 bg-rose-50 text-rose-700 transition hover:bg-rose-100">
                                            Cancelar
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                            Aún no se han programado viajes.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         @click.self="open = false">
        
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="scale-100 opacity-100"
             x-transition:leave-end="scale-95 opacity-0"
             class="mx-4 w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl">
            
            <div class="flex flex-col items-center text-center">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-rose-100">
                    <svg class="h-8 w-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                
                <h3 class="text-xl font-bold text-slate-900">¿Cancelar este viaje?</h3>
                <p class="mt-2 text-sm text-slate-600">Esta acción liberará el bus y no se puede deshacer.</p>
                
                <div class="mt-6 flex w-full gap-3">
                    <button @click="open = false" 
                            class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        No, mantener
                    </button>
                    
                    <button @click="$wire.cambiarEstado(viajeId, 'cancelado'); open = false" 
                            class="flex-1 rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Sí, cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
