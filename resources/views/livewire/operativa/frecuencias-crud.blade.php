<div class="p-6 bg-gray-100 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Gestión de Frecuencias</h1>

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
        <h2 class="text-xl font-semibold text-gray-700 mb-4">{{ $frecuencia_id ? 'Editar Frecuencia' : 'Crear Nueva Frecuencia' }}</h2>
        <form wire:submit.prevent="guardar">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div>
                    <label for="ruta_id" class="block text-gray-700 text-sm font-bold mb-2">Ruta:</label>
                    <select id="ruta_id" wire:model="ruta_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">Seleccione una ruta</option>
                        @foreach($rutas as $ruta)
                            <option value="{{ $ruta->id }}">{{ $ruta->origen->nombre }} - {{ $ruta->destino->nombre }}</option>
                        @endforeach
                    </select>
                    @error('ruta_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="hora_salida" class="block text-gray-700 text-sm font-bold mb-2">Hora de Salida:</label>
                    <input type="time" id="hora_salida" wire:model="hora_salida" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('hora_salida') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
            </div>

            <button type="submit" class="bg-[#003366] hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                {{ $frecuencia_id ? 'Actualizar' : 'Guardar' }}
            </button>
            <button type="button" wire:click="limpiarCampos" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2">
                Cancelar
            </button>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Frecuencias Existentes</h2>
        @if($frecuencias->isEmpty())
            <p>No hay frecuencias registradas aún.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                ID
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Ruta
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Hora de Salida
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-[#003366] text-left text-xs font-semibold text-white uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($frecuencias as $frecuencia)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $frecuencia->id }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $frecuencia->ruta->origen->nombre ?? 'Origen no encontrado' }} - {{ $frecuencia->ruta->destino->nombre ?? 'Destino no encontrado' }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $frecuencia->hora_salida }}
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <button wire:click="editar({{ $frecuencia->id }})" class="text-white bg-yellow-500 hover:bg-yellow-700 font-bold py-1 px-3 rounded text-xs">Editar</button>
                                    <button wire:click="eliminar({{ $frecuencia->id }})" class="text-white bg-red-600 hover:bg-red-700 font-bold py-1 px-3 rounded text-xs ml-2">Eliminar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
