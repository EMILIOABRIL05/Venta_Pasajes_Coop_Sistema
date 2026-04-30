<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                
                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Gestión de Frecuencias</h2>
                    <button wire:click="create()" class="bg-[#003366] hover:bg-blue-900 text-white font-bold py-2 px-4 rounded transition ease-in-out duration-150">
                        Nueva Frecuencia
                    </button>
                </div>

                <div class="mb-4">
                    <input wire:model.live="search" type="text" placeholder="Buscar por terminal origen o destino..." class="w-full px-4 py-2 border rounded-md text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#003366]">
                </div>

                <!-- Tabla Responsiva -->
                <div class="overflow-x-auto bg-gray-100 rounded-lg shadow">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr class="bg-[#003366] text-white uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Ruta (Origen - Destino)</th>
                                <th class="py-3 px-6 text-left">Hora de Salida</th>
                                <th class="py-3 px-6 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-800 text-sm font-light">
                            @forelse($frecuencias as $frecuencia)
                                <tr class="border-b border-gray-200 hover:bg-gray-200">
                                    <td class="py-3 px-6 text-left whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="font-medium">{{ $frecuencia->ruta->origen->nombre }} - {{ $frecuencia->ruta->destino->nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-6 text-left">
                                        {{ \Carbon\Carbon::parse($frecuencia->hora_salida)->format('H:i') }}
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="flex item-center justify-center">
                                            <button wire:click="edit('{{ $frecuencia->id }}')" class="w-4 mr-2 transform hover:text-blue-600 hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button wire:click="delete('{{ $frecuencia->id }}')" onclick="confirm('¿Estás seguro de eliminar esta frecuencia?') || event.stopImmediatePropagation()" class="w-4 mr-2 transform hover:text-[#CC0000] hover:scale-110">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-3 px-6 text-center text-gray-500 italic">No se encontraron frecuencias.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $frecuencias->links() }}
                </div>

            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar -->
    @if($isOpen)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form>
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="mb-4">
                                <label for="ruta_id" class="block text-gray-700 text-sm font-bold mb-2">Ruta:</label>
                                <select wire:model="ruta_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="ruta_id">
                                    <option value="">Seleccione una ruta</option>
                                    @foreach($rutas as $ruta)
                                        <option value="{{ $ruta->id }}">{{ $ruta->origen->nombre }} - {{ $ruta->destino->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('ruta_id') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
                            </div>
                            <div class="mb-4">
                                <label for="hora_salida" class="block text-gray-700 text-sm font-bold mb-2">Hora de Salida:</label>
                                <input wire:model="hora_salida" type="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="hora_salida">
                                @error('hora_salida') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button wire:click.prevent="store()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#003366] text-base font-medium text-white hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar
                            </button>
                            <button wire:click="closeModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
