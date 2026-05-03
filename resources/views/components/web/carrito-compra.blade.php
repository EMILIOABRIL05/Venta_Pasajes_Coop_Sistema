<div class="container mx-auto py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Columna Izquierda: Mapa de Asientos (Trabajo de Integración) -->
        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow-lg border-t-4 border-[#003366]">
            <h2 class="text-2xl font-bold mb-4 text-[#003366]">Selecciona tus Asientos</h2>
            <p class="mb-6 text-gray-600">Haz clic en los asientos disponibles para agregarlos a tu reserva.</p>
            
            <!-- Llamada al componente que ya existe en el proyecto -->
            <x-seat-map :viaje="$viaje" />
        </div>

        <!-- Columna Derecha: Detalle y Formulario (Tu parte del Sprint 3) -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4 text-[#CC0000]">Resumen de Compra</h2>
            
            @if(count($asientosSeleccionados) > 0)
                <form wire:submit.prevent="confirmarVenta">
                    @foreach($asientosSeleccionados as $asiento)
                        <div class="mb-6 p-4 bg-white rounded border-l-4 border-[#003366] shadow-sm">
                            <span class="font-bold text-[#003366]">Asiento: {{ $asiento }}</span>
                            
                            <div class="mt-2 space-y-3">
                                <input type="text" wire:model="datosPasajeros.{{ $asiento }}.nombre" 
                                       placeholder="Nombre Completo" class="w-full border-gray-300 rounded-md shadow-sm focus:border-[#003366]">
                                
                                <input type="text" wire:model="datosPasajeros.{{ $asiento }}.cedula" 
                                       placeholder="Cédula" class="w-full border-gray-300 rounded-md shadow-sm">
                                
                                <input type="number" wire:model.live="datosPasajeros.{{ $asiento }}.edad" 
                                       wire:change="calcularTotal"
                                       placeholder="Edad" class="w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            
                            <div class="mt-2 text-right">
                                <span class="text-sm text-gray-500">Subtotal:</span>
                                <span class="font-bold text-green-600">${{ number_format($datosPasajeros[$asiento]['precio'], 2) }}</span>
                            </div>
                        </div>
                    @endforeach

                    <div class="border-t pt-4 mt-4">
                        <div class="flex justify-between text-2xl font-bold text-[#003366] mb-6">
                            <span>Total:</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>

                        <button type="submit" 
                                class="w-full bg-[#CC0000] hover:bg-[#990000] text-white font-bold py-3 rounded-lg transition duration-300 shadow-lg">
                            CONFIRMAR Y PAGAR
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center py-10 text-gray-400">
                    <p>No has seleccionado ningún asiento aún.</p>
                </div>
            @endif
        </div>
    </div>
</div>