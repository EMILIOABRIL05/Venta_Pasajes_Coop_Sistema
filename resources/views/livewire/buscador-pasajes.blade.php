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

        <div class="w-full md:w-1/6">
            <label for="pasajeros" class="block text-sm font-bold text-gray-800 mb-2">Pasajeros</label>
            <input type="number" wire:model="pasajeros" id="pasajeros" min="1" max="10" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
            @error('pasajeros') <span class="text-[#CC0000] text-xs font-semibold block mt-1">{{ $message }}</span> @enderror
        </div>

        <div class="w-full md:w-auto">
            <button type="submit" class="w-full md:w-auto bg-[#CC0000] hover:bg-red-800 text-white font-bold py-3 px-8 rounded-md transition duration-200 shadow-sm">
                Buscar Pasajes
            </button>
        </div>
    </form>

    <!-- Resultados de la Búsqueda -->
    @if($busquedaRealizada)
        <div class="mt-10">
            @if(count($viajesEncontrados) > 0)
                <h3 class="text-xl font-bold text-[#003366] mb-4">Resultados Encontrados</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($viajesEncontrados as $viaje)
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden flex flex-col">
                            <div class="bg-[#003366] text-white px-4 py-3 flex justify-between items-center">
                                <h4 class="font-bold text-lg">Salida: {{ \Carbon\Carbon::parse($viaje->frecuencia->hora_salida)->format('H:i') }}</h4>
                                <span class="bg-[#CC0000] text-white text-xs font-bold px-2 py-1 rounded">Asientos</span>
                            </div>
                            <div class="p-4 text-gray-800 flex-grow">
                                <div class="mb-2">
                                    <span class="text-sm text-gray-500">Ruta:</span>
                                    <p class="font-semibold">{{ $viaje->frecuencia->ruta->origen->ciudad ?? 'N/A' }} ➔ {{ $viaje->frecuencia->ruta->destino->ciudad ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-2">
                                    <span class="text-sm text-gray-500">Placa:</span>
                                    <p class="font-semibold">{{ $viaje->bus->placa ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Precio Base:</span>
                                    <p class="font-bold text-[#003366] text-xl">${{ number_format($viaje->frecuencia->ruta->precio_base, 2) }}</p>
                                </div>
                            </div>
                            
                            <!-- Botón Corregido con Enlace al Componente Compra Web -->
                            <div class="px-4 py-4 bg-gray-50 border-t border-gray-200">
                                <a href="{{ route('web.compra-web', ['viajeId' => $viaje->id]) }}" 
                                   class="w-full block text-center bg-[#CC0000] hover:bg-red-800 text-white font-bold py-2 px-4 rounded transition duration-200 shadow-sm">
                                    Comprar Boletos
                                </a>
                            </div>
                        </div>
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