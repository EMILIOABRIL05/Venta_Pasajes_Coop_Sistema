<div class="py-12 bg-[#F3F4F6] min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Cabecera de Sección -->
        <div class="mb-10 flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-200 pb-6">
            <div>
                <h1 class="text-4xl font-extrabold text-[#1F2937] tracking-tight">Mis Viajes</h1>
                <p class="mt-2 text-lg text-gray-500">Consulta el historial de tus boletos y el estado de tus pagos.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="/" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-xl text-white bg-[#003366] hover:bg-[#002244] transition-all shadow-md uppercase tracking-wider">
                    Nueva Compra
                </a>
            </div>
        </div>

        @if($compras->isEmpty())
            <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-gray-100">
                <div class="mx-auto h-24 w-24 text-gray-300 mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 012.5 2.5V17m-5 1v2.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-[#1F2937] mb-2">Aún no tienes viajes registrados</h3>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Cuando realices tu primera compra, aparecerá aquí con toda la información de tus boletos.</p>
                <a href="/" class="text-[#003366] font-black uppercase tracking-widest hover:underline">Comprar mi primer pasaje →</a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($compras as $venta)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                        <!-- Header de la Card -->
                        <div class="bg-gray-50 px-8 py-4 flex flex-wrap justify-between items-center border-b border-gray-100">
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Venta #{{ $venta->id }}</span>
                                <span class="text-xs text-gray-400">•</span>
                                <span class="text-xs font-medium text-gray-500">{{ $venta->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            
                            @php
                                $estadoColor = match($venta->estado) {
                                    'Pendiente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'Pendiente de Validación' => 'bg-blue-100 text-[#003366] border-blue-200',
                                    'Pagada' => 'bg-green-100 text-green-700 border-green-200',
                                    'Cancelada' => 'bg-red-100 text-[#CC0000] border-red-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                            @endphp

                            <span class="px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $estadoColor }}">
                                {{ $venta->estado }}
                            </span>
                        </div>

                        <!-- Detalle de Boletos -->
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <!-- Boletos -->
                                <div class="md:col-span-2 space-y-4">
                                    <h4 class="text-[#003366] font-bold uppercase text-xs tracking-widest mb-4">Pasajeros y Rutas</h4>
                                    @foreach($venta->boletos as $boleto)
                                        <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-100">
                                            <div class="bg-[#003366] text-white p-2 rounded-lg shadow-inner">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex justify-between">
                                                    <p class="font-bold text-[#1F2937] text-sm uppercase">{{ $boleto->pasajero->nombre_completo }}</p>
                                                    <p class="font-black text-[#003366] text-sm italic">Asiento: {{ $boleto->numero_asiento }}</p>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <span class="font-bold">RUTA:</span> {{ $boleto->frecuencia->ruta->origen->ciudad }} &rarr; {{ $boleto->frecuencia->ruta->destino->ciudad }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-1">COD: {{ $boleto->codigo_reserva }}</p>
                                                
                                                @if($venta->estado === 'pagada')
                                                    <button wire:click="descargarBoleto('{{ $boleto->id }}')" 
                                                            wire:loading.attr="disabled"
                                                            class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-[#003366] text-white text-[10px] font-bold uppercase tracking-widest rounded-lg hover:bg-[#002244] transition-colors shadow-sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                        <span>Descargar Boleto</span>
                                                        <div wire:loading wire:target="descargarBoleto('{{ $boleto->id }}')" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></div>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Resumen Pago -->
                                <div class="bg-[#003366] rounded-2xl p-6 text-white flex flex-col justify-between shadow-lg shadow-blue-900/20">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-300 mb-2">Total Pagado</p>
                                        <p class="text-4xl font-black">${{ number_format($venta->total, 2) }}</p>
                                    </div>
                                    
                                    <div class="mt-8">
                                        @if($venta->estado === 'Pendiente')
                                            <a href="{{ route('pago', $venta->id) }}" class="block w-full text-center bg-[#CC0000] py-3 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-red-700 transition-colors shadow-lg">
                                                Subir Comprobante
                                            </a>
                                        @elseif($venta->comprobante)
                                            <div class="flex items-center gap-2 text-blue-200 text-[10px] font-bold uppercase tracking-widest">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                                                Comprobante Enviado
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="mt-8">
                    {{ $compras->links() }}
                </div>
            </div>
        @endif
    </div>
</div>