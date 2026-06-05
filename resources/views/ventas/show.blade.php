<x-layouts.app title="Recibo de Venta">
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg border-2 border-dashed border-gray-300 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">RECIBO DE VENTA</h1>
                <p class="text-sm text-gray-600 mt-2">Cooperativa de Transportes - Sistema de Pasajes</p>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 mb-6">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Información de la Venta -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Información de Venta</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">ID Venta:</span>
                            <span>{{ $venta->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Fecha:</span>
                            <span>{{ $venta->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Cajero:</span>
                            <span>{{ $venta->user->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Total:</span>
                            <span class="font-bold text-lg">${{ number_format($venta->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Información del Boleto -->
                @php $boleto = $venta->boletos->first(); @endphp
                @if($boleto)
                <div class="bg-blue-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Boleto</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">Código Boleto:</span>
                            <span class="font-mono text-xs">{{ $boleto->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Asiento:</span>
                            <span class="font-bold">{{ $boleto->numero_asiento }}</span>
                        </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Categoría:</span>
                                <span class="font-bold {{ ($boleto->categoria_asiento ?? 'estandar') === 'vip' ? 'text-amber-700' : 'text-gray-700' }}">
                                    {{ ($boleto->categoria_asiento ?? 'estandar') === 'vip' ? 'VIP' : 'Estándar' }}
                                </span>
                            </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Precio:</span>
                            <span>${{ number_format($boleto->precio_final, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Información del Viaje -->
            @if($boleto)
            <div class="mt-6 bg-green-50 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Detalles del Viaje</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="text-center">
                        <p class="text-xs uppercase tracking-wide text-green-600">Origen</p>
                        <p class="text-lg font-bold text-gray-900">{{ $boleto->frecuencia->ruta->origen->nombre }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs uppercase tracking-wide text-green-600">Destino</p>
                        <p class="text-lg font-bold text-gray-900">{{ $boleto->frecuencia->ruta->destino->nombre }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs uppercase tracking-wide text-green-600">Salida</p>
                        <p class="text-lg font-bold text-gray-900">{{ $boleto->frecuencia->hora_salida }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Información del Pasajero -->
            @if($boleto && $boleto->pasajero)
            <div class="mt-6 bg-purple-50 rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Información del Pasajero</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Nombre Completo</p>
                        <p class="text-base">{{ $boleto->pasajero->nombre_completo }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Cédula</p>
                        <p class="text-base">{{ $boleto->pasajero->cedula }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Información de Pago -->
            @if($venta->pagos->isNotEmpty())
                @php $pago = $venta->pagos->first(); @endphp
                <div class="mt-6 bg-yellow-50 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Información de Pago</h2>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Método</p>
                            <p class="text-base">{{ $pago->metodo_pago }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Referencia</p>
                            <p class="text-base">{{ $pago->referencia ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Fecha</p>
                            <p class="text-base">{{ $pago->fecha->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($pago->observaciones)
                    <div class="mt-3">
                        <p class="text-sm font-medium text-gray-700">Observaciones</p>
                        <p class="text-sm text-gray-600">{{ $pago->observaciones }}</p>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Acciones -->
            <div class="mt-8 flex items-center justify-between pt-6 border-t border-gray-300">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Este recibo es válido como comprobante de pago</p>
                    <p class="text-xs text-gray-500">Conserve este documento para su viaje</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.print()" class="inline-flex justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Imprimir
                    </button>
                    <a href="{{ route('ventanilla.ventas.create') }}" class="inline-flex justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Nueva Venta
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>