<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-gray-100 min-h-screen">
    <style>
        .min-h-screen > div {
            max-width: 100% !important;
            width: 100% !important;
        }
    </style>
    
    <!-- Hero Minimalista -->
    <div class="bg-[#003366] text-white py-8 px-6 sm:px-10 rounded-2xl shadow-xl mb-8 flex flex-col md:flex-row items-center justify-between relative overflow-hidden border-b-4 border-[#CC0000]">
        <div class="z-10 w-full">
            <div class="flex items-center text-blue-200 text-sm font-semibold tracking-widest uppercase mb-1">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Detalles del Viaje
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3 text-white">
                {{ $viaje->frecuencia->ruta->origen->ciudad ?? 'Origen' }} <span class="text-[#CC0000] mx-2">→</span> {{ $viaje->frecuencia->ruta->destino->ciudad ?? 'Destino' }}
            </h1>
            <div class="flex flex-wrap items-center gap-4 text-blue-100">
                <span class="flex items-center bg-blue-900/50 px-3 py-1 rounded-full text-sm font-medium">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ \Carbon\Carbon::parse($viaje->fecha)->format('d M Y') }}
                </span>
                <span class="flex items-center bg-blue-900/50 px-3 py-1 rounded-full text-sm font-medium">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ \Carbon\Carbon::parse($viaje->frecuencia->hora_salida)->format('H:i') }}
                </span>
            </div>
        </div>
        <!-- Decoración SVG de fondo -->
        <svg class="absolute right-0 top-0 h-full w-64 text-blue-800 opacity-30 transform translate-x-16" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1v12zm0 0v7"></path>
        </svg>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- COLUMNA IZQUIERDA: Selección de Asientos -->
        <div class="lg:col-span-2 bg-white p-6 sm:p-8 rounded-2xl shadow-xl border border-gray-100">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-2xl font-bold text-[#003366]">Selecciona tus Asientos</h2>
                    <p class="text-gray-500 mt-1 text-sm">Haz clic en los asientos disponibles en el mapa para agregarlos a tu reserva.</p>
                </div>
            </div>
            
{{-- Integración Directa con el Mapa de Asientos (Sin Alpine para evitar conflictos) --}}
<div class="flex justify-center bg-gray-50 rounded-xl p-6 border border-gray-100"
     wire:key="seat-map-container"
     wire:loading.class="opacity-50 pointer-events-none transition-opacity">
    
    <x-seat-map 
        :seatNumbers="range(1, $viaje->bus->numero_asientos ?? 40)" 
        :occupiedSeats="$viaje->boletos ? $viaje->boletos->pluck('numero_asiento')->toArray() : []"
        :selectedSeats="$asientosSeleccionados"
    />
</div>
        </div>

        <!-- COLUMNA DERECHA: Resumen y Formulario -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl border border-gray-100 sticky top-6">
                <h2 class="text-xl font-extrabold mb-6 text-[#003366] border-b-2 border-gray-100 pb-4 flex items-center uppercase tracking-wide">
                    <svg class="w-6 h-6 mr-3 text-[#CC0000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Resumen de Compra
                </h2>

                @if(session()->has('success'))
                    <div class="mb-6 p-4 text-sm font-medium text-green-800 bg-green-50 border border-green-200 rounded-xl flex items-start">
                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session()->has('error'))
                    <div class="mb-6 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-xl flex items-start">
                        <svg class="w-5 h-5 mr-2 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if(count($asientosSeleccionados) > 0)
                    <form wire:submit.prevent="confirmarVenta" class="flex flex-col">
                        <!-- Area Desplazable de Pasajeros -->
                        <div class="space-y-4 overflow-y-auto pr-2 mb-6 custom-scrollbar" style="max-height: 400px;">
                            @foreach($asientosSeleccionados as $asiento)
                                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-200 group">
                                    <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                                        <div class="flex items-center">
                                            <div class="bg-[#003366] text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-2 text-xs shadow-inner">
                                                {{ $asiento }}
                                            </div>
                                            <span class="font-bold text-gray-700 text-sm">Pasajero</span>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 bg-green-100 text-green-700 rounded-full uppercase tracking-wider">Llenar</span>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <input type="text" wire:model="datosPasajeros.{{ $asiento }}.nombre" 
                                                   placeholder="Nombre Completo" 
                                                   class="w-full border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-[#003366] focus:border-[#003366] placeholder-gray-400 transition-all shadow-sm">
                                            @error('datosPasajeros.'.$asiento.'.nombre') <span class="text-[10px] text-[#CC0000] font-medium mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <input type="text" wire:model="datosPasajeros.{{ $asiento }}.cedula" 
                                                       placeholder="Cédula" 
                                                       class="w-full border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-[#003366] focus:border-[#003366] transition-all shadow-sm">
                                            </div>
                                            <div>
                                                <input type="number" wire:model.live="datosPasajeros.{{ $asiento }}.edad" 
                                                       wire:change="calcularTotal"
                                                       placeholder="Edad" 
                                                       class="w-full border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-[#003366] focus:border-[#003366] transition-all shadow-sm">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 pt-2 border-t border-gray-100 flex justify-between items-center">
                                        <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Precio:</span>
                                        <span class="font-bold text-[#003366] text-sm">
                                            ${{ number_format($datosPasajeros[$asiento]['precio'] ?? 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Sección de Totales Finales (Fija abajo) -->
                        <div class="bg-[#003366] rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
                            <!-- Patrón decorativo -->
                            <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between text-blue-200 text-xs font-medium mb-1">
                                    <span>Asientos seleccionados:</span>
                                    <span class="bg-blue-800 px-2 py-0.5 rounded-full text-white">{{ count($asientosSeleccionados) }}</span>
                                </div>
                                <div class="flex justify-between items-end mt-2 pt-2 border-t border-blue-800">
                                    <span class="text-xs font-medium text-blue-100 uppercase tracking-wider">Total</span>
                                    <span class="text-3xl font-extrabold tracking-tight text-white">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-[#CC0000] hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl mt-4 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl hover:shadow-red-900/30 uppercase tracking-widest text-sm flex justify-center items-center group">
                            Confirmar y Pagar
                            <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                @else
                    <div class="flex flex-col items-center justify-center py-16 px-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl transition-all duration-300 hover:border-blue-300 hover:bg-blue-50/30">
                        <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center shadow-sm mb-4 border border-gray-100 text-gray-300">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700 mb-1">Tu carrito está vacío</h3>
                        <p class="text-sm text-gray-500 text-center max-w-[200px]">Selecciona uno o más asientos en el mapa para comenzar tu reserva.</p>
                    </div>
                @endif
            </div>

            <style>
                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #00336644;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #00336688;
                }
            </style>
            
            <div class="mt-6 flex flex-col items-center justify-center space-y-2 opacity-60 hover:opacity-100 transition-opacity duration-300">
                <div class="flex items-center space-x-2 text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span class="text-xs font-bold uppercase tracking-widest">Pago Seguro y Encriptado</span>
                </div>
                <div class="text-[10px] font-medium text-gray-400">
                    &copy; Cooperativa Ambato 2026. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>
</div>