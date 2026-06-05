<div>
    {{-- ─── Contenedor blanco: SOLO el formulario ──────────────────────────────── --}}
    <div class="bg-gray-100 p-6 rounded-lg shadow-md max-w-5xl mx-auto border border-gray-200 mt-8">
        <h2 class="text-2xl font-bold text-[#003366] mb-6">Encuentra tu próximo viaje</h2>

        <form wire:submit.prevent="buscar" class="flex flex-col md:flex-row gap-4 items-end">
            
            <!-- Origen Dinámico -->
            <div class="w-full md:w-1/4">
                <label for="origen" class="block text-sm font-bold text-gray-800 mb-2">Origen</label>
                <select wire:model="origen" id="origen" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
                    <option value="">Seleccione ciudad...</option>
                    @foreach ($paradas as $parada)
                        <option value="{{ $parada->id }}">{{ $parada->ciudad }}</option>
                    @endforeach
                </select>
                @error('origen') <span class="text-[#CC0000] text-xs font-semibold block mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Destino Dinámico -->
            <div class="w-full md:w-1/4">
                <label for="destino" class="block text-sm font-bold text-gray-800 mb-2">Destino</label>
                <select wire:model="destino" id="destino" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
                    <option value="">Seleccione ciudad...</option>
                    @foreach ($paradas as $parada)
                        <option value="{{ $parada->id }}">{{ $parada->ciudad }}</option>
                    @endforeach
                </select>
                @error('destino') <span class="text-[#CC0000] text-xs font-semibold block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="w-full md:w-1/4">
                <label for="fecha" class="block text-sm font-bold text-gray-800 mb-2">Fecha de Viaje</label>
                <input type="date" wire:model="fecha" id="fecha" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
                @error('fecha') <span class="text-[#CC0000] text-xs font-semibold block mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto bg-[#CC0000] hover:bg-red-800 text-white font-bold py-3 px-8 rounded-md transition duration-200 shadow-sm">
                    Buscar Pasajes
                </button>
            </div>
        </form>
    </div>

    {{-- ─── Toggle + Próximos Viajes (FUERA del contenedor blanco) ─────────────── --}}
    @if(!$busquedaRealizada && $proximosViajes->count() > 0)
        <div x-data="{ showUpcoming: false }" class="max-w-5xl mx-auto mt-4 px-4">
            <div class="text-center">
                <button type="button"
                        @click="showUpcoming = !showUpcoming"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span x-text="showUpcoming ? 'Ocultar próximos viajes' : 'Ver próximos viajes programados (48h)'"></span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showUpcoming }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>

            <div x-show="showUpcoming"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-3"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-3"
                 class="mt-6">
                <h3 class="text-xl font-bold text-[#003366] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Próximos Viajes Disponibles
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($proximosViajes as $viaje)
                        @include('livewire.partials.viaje-card', ['viaje' => $viaje])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ─── Resultados de la Búsqueda ─────────────────────────────────────────── --}}
    @if($busquedaRealizada)
        <div class="max-w-5xl mx-auto mt-10 px-4">
            @if(count($viajesEncontrados) > 0)
                <h3 class="text-xl font-bold text-[#003366] mb-4">Resultados Encontrados</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($viajesEncontrados as $viaje)
                        @include('livewire.partials.viaje-card', ['viaje' => $viaje])
                    @endforeach
                </div>
            @else
                <div class="bg-red-50 border-l-4 border-[#CC0000] p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-[#CC0000]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-[#CC0000] font-bold">No hay viajes programados para esta fecha.</p>
                            <p class="text-sm text-red-700 mt-1">Por favor, intenta buscar en otra fecha o con un origen/destino diferente.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
