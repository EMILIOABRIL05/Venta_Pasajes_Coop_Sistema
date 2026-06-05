<div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden flex flex-col">
    <div class="bg-[#003366] text-white px-4 py-3 flex justify-between items-center">
        <h4 class="font-bold text-lg">Salida: {{ \Carbon\Carbon::parse($viaje->frecuencia->hora_salida)->format('H:i') }}</h4>
        @if($viaje->asientos_disponibles <= 5)
            <span class="bg-[#CC0000] text-white text-xs font-bold px-2 py-1 rounded animate-pulse">
                ¡Últimos {{ $viaje->asientos_disponibles }}!
            </span>
        @elseif($viaje->asientos_disponibles > 0)
            <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded">
                {{ $viaje->asientos_disponibles }} disponibles
            </span>
        @else
            <span class="bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded">
                Agotado
            </span>
        @endif
    </div>
    <div class="p-4 text-gray-800 flex-grow">
        <div class="mb-2">
            <span class="text-sm text-gray-500">Fecha:</span>
            <p class="font-semibold">{{ $viaje->fecha->format('d/m/Y') }}</p>
        </div>
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
    
    <div class="px-4 py-4 bg-gray-50 border-t border-gray-200">
        @if($viaje->asientos_disponibles > 0)
            <a href="{{ route('web.compra-web', ['viajeId' => $viaje->id]) }}" 
               class="w-full block text-center bg-[#CC0000] hover:bg-red-800 text-white font-bold py-2 px-4 rounded transition duration-200 shadow-sm">
                Comprar Boletos
            </a>
        @else
            <span class="w-full block text-center bg-gray-400 text-white font-bold py-2 px-4 rounded cursor-not-allowed">
                Sin Disponibilidad
            </span>
        @endif
    </div>
</div>
