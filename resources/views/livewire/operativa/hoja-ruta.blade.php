<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50 py-10">
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
                                        {{ $viaje->frecuencia?->ruta->origen->nombre ?? 'Sin origen' }} a {{ $viaje->frecuencia?->ruta->destino->nombre ?? 'Sin destino' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Bus: {{ $viaje->bus->placa }} (Asientos: {{ $viaje->bus->numero_asientos }})
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
                                            wire:click="cambiarEstado({{ $viaje->id }}, 'cancelado')" 
                                            wire:confirm="¿Cancelar este viaje?"
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
</div>
