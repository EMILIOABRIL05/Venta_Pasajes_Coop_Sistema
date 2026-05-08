<div class="min-h-screen bg-[#F3F4F6] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Encabezado -->
        <div class="bg-[#003366] rounded-t-2xl p-8 shadow-lg text-white text-center">
            <h1 class="text-3xl font-bold uppercase tracking-wider">Finalizar Pago</h1>
            <p class="mt-2 text-blue-100 italic">Cooperativa de Transportes Ambato</p>
        </div>

        <!-- Contenido Principal -->
        <div class="bg-white rounded-b-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <!-- Resumen de Venta -->
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-[#1F2937]">Resumen de Compra</h2>
                            <p class="text-sm text-gray-500">ID de Venta: #{{ $venta->id }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-gray-400 block uppercase tracking-tighter">Total a Pagar</span>
                            <span class="text-3xl font-black text-[#003366]">${{ number_format($venta->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones de Pago -->
                <div class="bg-blue-50 border-l-4 border-[#003366] p-6 mb-8 rounded-r-lg">
                    <h3 class="font-bold text-[#003366] mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Instrucciones de Depósito/Transferencia
                    </h3>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        Por favor, realice el pago a la siguiente cuenta y suba una foto o captura del comprobante:
                    </p>
                    <div class="mt-4 bg-white p-4 rounded border border-blue-100 font-mono text-sm text-[#003366]">
                        {{ $banco_destino }} <br>
                        Beneficiario: Cooperativa Ambato
                    </div>
                </div>

                <!-- Formulario de Carga -->
                <form wire:submit.prevent="guardarPago" class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#1F2937]">
                            Subir Comprobante (JPG, PNG - Máx. 2MB)
                        </label>
                        
                        <div class="relative">
                            <input type="file" wire:model="comprobante" id="comprobante" class="hidden">
                            <label for="comprobante" class="cursor-pointer flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 transition-all duration-300 hover:border-[#003366] group">
                                @if ($comprobante)
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span class="mt-2 text-sm text-gray-600 font-medium">{{ $comprobante->getClientOriginalName() }}</span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 group-hover:text-[#003366] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <p class="mt-2 text-sm text-gray-500">Haz clic para seleccionar o arrastra la imagen</p>
                                    </div>
                                @endif
                            </label>
                        </div>
                        
                        @error('comprobante') 
                            <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span> 
                        @enderror

                        <!-- Barra de Progreso Livewire -->
                        <div wire:loading wire:target="comprobante" class="w-full bg-gray-200 rounded-full h-2 mt-4">
                            <div class="bg-[#003366] h-2 rounded-full animate-pulse w-full"></div>
                            <p class="text-[10px] text-[#003366] mt-1 text-center font-bold">CARGANDO ARCHIVO...</p>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="flex-1 bg-[#003366] text-white py-4 px-6 rounded-xl font-bold uppercase tracking-widest hover:bg-[#002244] transform transition-all active:scale-95 shadow-lg shadow-blue-900/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="guardarPago">Confirmar y Enviar Pago</span>
                            <span wire:loading wire:target="guardarPago">Procesando...</span>
                        </button>
                        
                        <a href="{{ route('dashboard') }}" 
                           class="px-6 py-4 border-2 border-gray-200 text-gray-500 rounded-xl font-bold hover:bg-gray-50 transition-colors uppercase text-sm">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer Informativo -->
        <p class="mt-8 text-center text-gray-400 text-sm italic">
            Al subir su comprobante, acepta los términos y condiciones de la Cooperativa Ambato.
        </p>
    </div>
</div>
