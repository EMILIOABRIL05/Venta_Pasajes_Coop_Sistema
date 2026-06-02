<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-gray-100 min-h-screen">
    <!-- Hero Header Cooperativa Ambato -->
    <div class="bg-[#003366] text-white py-8 px-6 sm:px-10 rounded-2xl shadow-xl mb-8 flex flex-col md:flex-row items-center justify-between relative overflow-hidden border-b-4 border-[#CC0000]">
        <div class="z-10 w-full">
            <div class="flex items-center text-blue-200 text-sm font-semibold tracking-widest uppercase mb-1">
                <svg class="w-4 h-4 mr-2 animate-pulse text-[#CC0000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Portal de Venta Web
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3 text-white">
                {{ $viaje->frecuencia->ruta->origen->ciudad ?? 'Origen' }} 
                <span class="text-[#CC0000] mx-2 font-light">→</span> 
                {{ $viaje->frecuencia->ruta->destino->ciudad ?? 'Destino' }}
            </h1>
            <div class="flex flex-wrap items-center gap-4 text-blue-100">
                <span class="flex items-center bg-blue-900/60 px-3.5 py-1.5 rounded-full text-xs font-semibold shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($viaje->fecha)->format('d M Y') }}
                </span>
                <span class="flex items-center bg-blue-900/60 px-3.5 py-1.5 rounded-full text-xs font-semibold shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ \Carbon\Carbon::parse($viaje->frecuencia->hora_salida)->format('H:i') }}
                </span>
                <span class="flex items-center bg-blue-900/60 px-3.5 py-1.5 rounded-full text-xs font-semibold shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M16 16h3a1 1 0 001-1v-4a1 1 0 00-1-1h-3m-9 0h3"></path>
                    </svg>
                    Bus: {{ $viaje->bus->placa ?? 'N/D' }} ({{ $viaje->bus->categoria->nombre ?? 'Normal' }})
                </span>
            </div>
        </div>
        <!-- Decoración SVG de fondo -->
        <svg class="absolute right-0 top-0 h-full w-64 text-blue-800 opacity-20 transform translate-x-16" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1v12zm0 0v7"></path>
        </svg>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- COLUMNA IZQUIERDA: Selector de Asiento e Info -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Selector de Categoría de Asiento Global -->
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-[#003366] flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#CC0000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2V5zM5 15a2 2 0 012-2h10a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            Categoría del Asiento
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Elige la categoría para aplicar el recargo respectivo.</p>
                    </div>
                    <span class="text-xs font-bold text-gray-700 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                        Tarifa Base: ${{ number_format($viaje->frecuencia->ruta->precio_base ?? 0, 2) }}
                    </span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $tipoAsiento === 'estandar' ? 'border-[#003366] bg-blue-50/40 shadow-md' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50' }}">
                        <input type="radio" wire:model.live="tipoAsiento" value="estandar" class="sr-only">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center">
                                <span class="w-5 h-5 rounded-full border-2 border-[#003366] flex items-center justify-center mr-3">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#003366] transition-transform duration-300 {{ $tipoAsiento === 'estandar' ? 'scale-100' : 'scale-0' }}"></span>
                                </span>
                                <div>
                                    <span class="block font-bold text-gray-800 text-sm">Estándar</span>
                                    <span class="block text-xs text-gray-500">Sin cargos adicionales</span>
                                </div>
                            </div>
                            <span class="font-extrabold text-sm text-[#003366]">+$0.00</span>
                        </div>
                    </label>

                    <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $tipoAsiento === 'vip' ? 'border-[#003366] bg-blue-50/40 shadow-md' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50' }}">
                        <input type="radio" wire:model.live="tipoAsiento" value="vip" class="sr-only">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center">
                                <span class="w-5 h-5 rounded-full border-2 border-[#003366] flex items-center justify-center mr-3">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#003366] transition-transform duration-300 {{ $tipoAsiento === 'vip' ? 'scale-100' : 'scale-0' }}"></span>
                                </span>
                                <div>
                                    <span class="block font-bold text-gray-800 text-sm flex items-center">
                                        VIP
                                        <span class="ml-1.5 text-[9px] bg-yellow-100 text-yellow-800 px-1.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Premium</span>
                                    </span>
                                    <span class="block text-xs text-gray-500">Mayor confort y servicios</span>
                                </div>
                            </div>
                            <span class="font-extrabold text-sm text-[#CC0000]">+$5.00</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Mapa de Asientos -->
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-xl border border-gray-100">
                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h2 class="text-2xl font-bold text-[#003366]">Selecciona tus Asientos</h2>
                    <p class="text-gray-500 mt-1 text-sm">Haz clic en los asientos disponibles para agregarlos a tu compra.</p>
                </div>
                
                <div class="flex justify-center bg-gray-50 rounded-xl p-6 border border-gray-150"
                     wire:key="seat-map-container"
                     wire:loading.class="opacity-50 pointer-events-none transition-opacity">
                    
                    <x-seat-map 
                        :seatNumbers="range(1, $viaje->bus->numero_asientos ?? 40)" 
                        :occupiedSeats="$viaje->boletos ? $viaje->boletos->pluck('numero_asiento')->toArray() : []"
                        :selectedSeats="$asientosSeleccionados"
                    />
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Resumen y Formulario de Pasajeros -->
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
                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session()->has('error'))
                    <div class="mb-6 p-4 text-sm font-medium text-red-800 bg-red-50 border border-red-200 rounded-xl flex items-start">
                        <svg class="w-5 h-5 mr-2 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif
                
                @if(count($asientosSeleccionados) > 0)
                    <form wire:submit.prevent="confirmarVenta" class="flex flex-col">
                        <!-- Formularios de Pasajeros -->
                        <div class="space-y-4 overflow-y-auto pr-2 mb-6 custom-scrollbar" style="max-height: 400px;">
                            @foreach($asientosSeleccionados as $asiento)
                                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 shadow-sm transition-all duration-300 hover:shadow-md hover:border-blue-200 group">
                                    <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-150">
                                        <div class="flex items-center">
                                            <div class="bg-[#003366] text-white w-7 h-7 rounded-full flex items-center justify-center font-bold mr-2 text-xs shadow-inner">
                                                {{ $asiento }}
                                            </div>
                                            <span class="font-bold text-gray-700 text-sm">Pasajero</span>
                                        </div>
                                        <span class="text-[9px] font-extrabold px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full uppercase tracking-wider">
                                            {{ $tipoAsiento === 'vip' ? 'VIP' : 'Estándar' }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                                            <input type="text" wire:model="datosPasajeros.{{ $asiento }}.nombre" 
                                                   placeholder="Nombre Completo" 
                                                   class="w-full border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-[#003366] focus:border-[#003366] placeholder-gray-400 transition-all shadow-sm">
                                            @error('datosPasajeros.'.$asiento.'.nombre') <span class="text-[10px] text-[#CC0000] font-medium mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Cédula</label>
                                                <input type="text" wire:model="datosPasajeros.{{ $asiento }}.cedula" 
                                                       placeholder="10 dígitos" 
                                                       class="w-full border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-[#003366] focus:border-[#003366] transition-all shadow-sm">
                                                @error('datosPasajeros.'.$asiento.'.cedula') <span class="text-[10px] text-[#CC0000] font-medium mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Edad</label>
                                                <input type="number" wire:model.live="datosPasajeros.{{ $asiento }}.edad" 
                                                       placeholder="Edad" 
                                                       class="w-full border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-[#003366] focus:border-[#003366] transition-all shadow-sm">
                                                @error('datosPasajeros.'.$asiento.'.edad') <span class="text-[10px] text-[#CC0000] font-medium mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Desglose por asiento -->
                                    <div class="mt-3 pt-2.5 border-t border-gray-150 text-xs text-gray-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Precio base:</span>
                                            <span>${{ number_format($viaje->frecuencia->ruta->precio_base ?? 0, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Recargo Asiento ({{ ucfirst($tipoAsiento) }}):</span>
                                            <span>+${{ number_format($recargo, 2) }}</span>
                                        </div>
                                        
                                        @php
                                            $edadPasajeroRaw = $datosPasajeros[$asiento]['edad'] ?? '';
                                            $edadPasajero = ($edadPasajeroRaw !== '' && is_numeric($edadPasajeroRaw)) ? (int) $edadPasajeroRaw : null;
                                            $aplicaDescuento = \App\Support\DescuentoPorEdad::aplica($edadPasajero);
                                        @endphp
                                        
                                        @if($aplicaDescuento)
                                            <div class="flex justify-between text-green-600 font-medium">
                                                <span>Descuento de Ley (50%):</span>
                                                <span>-${{ number_format((\App\Support\DescuentoPorEdad::monto(($viaje->frecuencia->ruta->precio_base ?? 0) + $recargo, $edadPasajero)), 2) }}</span>
                                            </div>
                                        @endif
                                        
                                        <div class="flex justify-between items-center pt-1 border-t border-dashed border-gray-200 mt-1">
                                            <span class="font-bold text-gray-700">Subtotal Asiento:</span>
                                            <span class="font-extrabold text-[#003366] text-sm">
                                                ${{ number_format($datosPasajeros[$asiento]['precio'] ?? (($viaje->frecuencia->ruta->precio_base ?? 0) + $recargo), 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Sección de Totales Finales -->
                        <div class="bg-[#003366] rounded-xl p-5 text-white shadow-lg relative overflow-hidden">
                            <!-- Patrón decorativo -->
                            <div class="absolute inset-0 opacity-10 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9IiNmZmYiLz48L3N2Zz4=')]"></div>
                            
                            <div class="relative z-10">
                                <div class="flex justify-between text-blue-200 text-xs font-semibold mb-1 uppercase tracking-wider">
                                    <span>Asientos seleccionados:</span>
                                    <span class="bg-blue-800 px-2 py-0.5 rounded-full text-white">{{ count($asientosSeleccionados) }}</span>
                                </div>
                                <div class="flex justify-between text-blue-200 text-xs mb-1">
                                    <span>Categoría elegida:</span>
                                    <span class="font-bold uppercase">{{ $tipoAsiento }}</span>
                                </div>
                                <div class="flex justify-between items-end mt-2 pt-2 border-t border-blue-800">
                                    <span class="text-xs font-semibold text-blue-100 uppercase tracking-widest">Total a Pagar</span>
                                    <span class="text-3xl font-black tracking-tight text-white">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-[#CC0000] hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl mt-4 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl hover:shadow-red-900/30 uppercase tracking-widest text-sm flex justify-center items-center group">
                            Confirmar y Comprar
                            <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </form>
                @else
                    <!-- Carrito Vacío -->
                    <div class="flex flex-col items-center justify-center py-16 px-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl transition-all duration-300 hover:border-blue-300 hover:bg-blue-50/30">
                        <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center shadow-sm mb-4 border border-gray-150 text-gray-300">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-700 mb-1">Tu carrito está vacío</h3>
                        <p class="text-xs text-gray-400 text-center max-w-[200px]">Selecciona tus asientos libres en el mapa para iniciar la compra.</p>
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
            
            <div class="mt-6 flex flex-col items-center justify-center space-y-2 opacity-65 hover:opacity-100 transition-opacity duration-300">
                <div class="flex items-center space-x-2 text-gray-500">
                    <svg class="w-4 h-4 text-[#003366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span class="text-[10px] font-bold uppercase tracking-widest">Compra Segura y Protegida</span>
                </div>
                <div class="text-[9px] font-semibold text-gray-400">
                    &copy; Cooperativa Ambato 2026. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </div>
</div>
