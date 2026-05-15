<div class="py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-sm mb-4" role="alert">
                <p class="font-bold">¡Éxito!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm mb-4" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- Panel Superior de Estadísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Ruta más vendida --}}
            <div class="bg-gradient-to-br from-[#003366] to-indigo-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-indigo-200 text-sm font-semibold uppercase tracking-wider mb-1">Ruta más vendida</p>
                    <h3 class="text-2xl font-bold leading-tight">{{ $rutaMasVendida }}</h3>
                </div>
                <svg class="absolute -bottom-4 -right-4 w-24 h-24 text-white opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>

            {{-- Total histórico recaudado --}}
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-emerald-100 text-sm font-semibold uppercase tracking-wider mb-1">Total Histórico</p>
                    <h3 class="text-3xl font-extrabold tracking-tight">${{ number_format($totalHistorico, 2) }}</h3>
                </div>
                <svg class="absolute -bottom-4 -right-4 w-24 h-24 text-white opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            {{-- Porcentaje de ocupación promedio --}}
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-amber-100 text-sm font-semibold uppercase tracking-wider mb-1">Ocupación Promedio (Tus ventas)</p>
                    <h3 class="text-3xl font-extrabold tracking-tight">{{ number_format($porcentajeOcupacion, 1) }}%</h3>
                </div>
                <svg class="absolute -bottom-4 -right-4 w-24 h-24 text-white opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        {{-- Header & Filtros --}}
        <div class="bg-white shadow-lg rounded-2xl overflow-hidden ring-1 ring-gray-100 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Búsqueda --}}
                <div class="relative w-full md:w-1/3">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="busqueda" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out" placeholder="Buscar pasajero, cédula, código o placa...">
                </div>

                {{-- Filtros Rápidos --}}
                <div class="flex items-center space-x-2 w-full md:w-auto">
                    <button wire:click="setFiltroFecha('todas')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150 focus:outline-none {{ $filtroFecha === 'todas' ? 'bg-[#003366] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Todas
                    </button>
                    <button wire:click="setFiltroFecha('mes')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150 focus:outline-none {{ $filtroFecha === 'mes' ? 'bg-[#003366] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Este Mes
                    </button>
                    <button wire:click="setFiltroFecha('hoy')" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150 focus:outline-none {{ $filtroFecha === 'hoy' ? 'bg-[#CC0000] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Hoy
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla de Datos --}}
        <div class="bg-white shadow-lg rounded-2xl overflow-hidden ring-1 ring-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Boletos (Cod / Pasajero)</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ruta & Bus</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($ventas as $venta)
                            <tr class="hover:bg-indigo-50/30 transition-colors duration-150">
                                
                                {{-- Fecha y Hora --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ $venta->created_at->format('d M, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $venta->created_at->format('h:i A') }}</div>
                                    <div class="text-xs text-indigo-500 mt-1" title="Cajero">👤 {{ optional($venta->user)->name ?? 'N/A' }}</div>
                                </td>

                                {{-- Boletos --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        @foreach($venta->boletos as $boleto)
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold leading-none text-white bg-slate-700 rounded-full">
                                                    {{ $boleto->codigo_reserva }}
                                                </span>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900">{{ optional($boleto->pasajero)->nombre_completo ?? 'N/A' }}</span>
                                                    @if($boleto->estado === 'Anulado')
                                                        <span class="ml-1 inline-flex text-[10px] font-bold text-red-600 bg-red-100 px-1.5 py-0.5 rounded">ANULADO</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Ruta y Bus --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        // Extraemos la ruta del primer boleto
                                        $primerBoleto = $venta->boletos->first();
                                        $ruta = optional(optional($primerBoleto->frecuencia)->ruta);
                                        $bus = optional(optional(optional($primerBoleto->frecuencia)->viajes)->first())->bus;
                                    @endphp
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $ruta->origen->nombre ?? 'N/A' }} → {{ $ruta->destino->nombre ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500 flex items-center mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                        Placa: <span class="font-bold ml-1 text-gray-700">{{ $bus->placa ?? 'N/A' }}</span>
                                    </div>
                                </td>

                                {{-- Total --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-lg font-extrabold text-[#003366]">${{ number_format($venta->total, 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ $venta->boletos->count() }} asiento(s)</div>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex flex-col items-center gap-2">
                                        <a href="{{ route('ventanilla.ventas.show', $venta->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors text-xs font-semibold w-full text-center">
                                            Ver Detalle
                                        </a>
                                        
                                        @if($venta->created_at->diffInMinutes(now()) <= 30 && $venta->boletos->where('estado', '!=', 'Anulado')->count() > 0)
                                            <form action="{{ route('ventanilla.ventas.destroy', $venta->id) }}" method="POST" class="w-full inline-block" onsubmit="return confirm('¿Estás seguro de anular toda esta venta?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors text-xs font-semibold w-full text-center">
                                                    Anular Venta
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-base font-medium">No se encontraron ventas para los criterios seleccionados.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if ($ventas->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $ventas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
