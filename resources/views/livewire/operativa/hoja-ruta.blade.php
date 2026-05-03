<div class="sm:ml-64 p-6 bg-gray-100 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Armar Hoja de Ruta</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Generar Nuevo Viaje</h2>
        <form wire:submit.prevent="saveViaje">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <div>
                    <label for="fecha" class="block text-gray-700 text-sm font-bold mb-2">Fecha:</label>
                    <input type="date" id="fecha" wire:model="fecha" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('fecha') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="frecuencia_id" class="block text-gray-700 text-sm font-bold mb-2">Frecuencia:</label>
                    <select id="frecuencia_id" wire:model="frecuencia_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Seleccione una frecuencia</option>
                        @foreach($frecuencias as $frecuencia)
                            <option value="{{ $frecuencia->id }}">{{ $frecuencia->hora_salida }} - ({{ $frecuencia->ruta->origen->nombre }} a {{ $frecuencia->ruta->destino->nombre }})</option>
                        @endforeach
                    </select>
                    @error('frecuencia_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="bus_id" class="block text-gray-700 text-sm font-bold mb-2">Bus:</label>
                    <select id="bus_id" wire:model="bus_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Seleccione un bus</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">{{ $bus->placa }} (Asientos: {{ $bus->numero_asientos }})</option>
                        @endforeach
                    </select>
                    @error('bus_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
            </div>

            <button type="submit" class="bg-[#003366] hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Generar Viaje
            </button>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Hojas de Ruta / Viajes Existentes</h2>
        @if($viajes->isEmpty())
            <p>No hay viajes programados aún.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Frecuencia
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Bus
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($viajes as $viaje)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $viaje->fecha->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $viaje->frecuencia?->hora_salida ?? 'Sin hora' }} - 
                                    ({{ $viaje->frecuencia?->ruta->origen->nombre }} a {{ $viaje->frecuencia?->ruta->destino->nombre }})
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $viaje->bus->placa }} (Asientos: {{ $viaje->bus->numero_asientos }})
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $viaje->estado }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <button wire:click="cancelarViaje({{ $viaje->id }})" class="text-white bg-[#CC0000] hover:bg-red-700 font-bold py-1 px-3 rounded text-xs">Cancelar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
