<x-layouts.app title="Crear venta y seleccionar asiento">
    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Nueva venta</h1>
                <p class="mt-2 text-sm text-gray-600">Seleccione pasajero, bus y asiento para la frecuencia seleccionada.</p>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 mb-6">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->has('general'))
                <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-6">
                    <p class="text-sm text-red-700">{{ $errors->first('general') }}</p>
                </div>
            @endif

            <div class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Frecuencia</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">Ruta: {{ $frecuencia->ruta->origen->nombre }} → {{ $frecuencia->ruta->destino->nombre }}</p>
                        <p class="mt-1 text-sm text-slate-600">Salida: {{ $frecuencia->hora_salida }}</p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-200">
                        <p class="text-xs uppercase tracking-wide text-slate-500">Bus</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $bus->placa }}</p>
                        <p class="mt-1 text-sm text-slate-600">Asientos disponibles: {{ $bus->numero_asientos }}</p>
                    </div>
                </div>

                <form action="{{ route('ventas.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="frecuencia_id" value="{{ $frecuencia->id }}">

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="bus_id" class="block text-sm font-medium text-gray-700">Bus</label>
                            <select name="bus_id" id="bus_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($buses as $item)
                                    <option value="{{ $item->id }}" {{ $item->id === $bus->id ? 'selected' : '' }}>
                                        {{ $item->placa }} — {{ $item->numero_asientos }} asientos
                                    </option>
                                @endforeach
                            </select>
                            @error('bus_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pasajero_id" class="block text-sm font-medium text-gray-700">Pasajero</label>
                            <select name="pasajero_id" id="pasajero_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Seleccione un pasajero</option>
                                @foreach($pasajeros as $pasajero)
                                    <option value="{{ $pasajero->id }}" {{ old('pasajero_id') == $pasajero->id ? 'selected' : '' }}>
                                        {{ $pasajero->nombre_completo }} — {{ $pasajero->cedula }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pasajero_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="precio_final" class="block text-sm font-medium text-gray-700">Precio final</label>
                            <input
                                type="number"
                                name="precio_final"
                                id="precio_final"
                                value="{{ old('precio_final') }}"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="0.00"
                                required
                            >
                            @error('precio_final')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <x-seat-map
                        :seat-numbers="$seatNumbers"
                        :occupied-seats="$occupiedSeats"
                        :selected-seat="old('numero_asiento')"
                        name="numero_asiento"
                    />

                    @error('numero_asiento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label for="metodo_pago" class="block text-sm font-medium text-gray-700">Método de pago</label>
                            <select
                                name="metodo_pago"
                                id="metodo_pago"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                @php
                                    $metodosPago = [
                                        'efectivo' => 'Efectivo',
                                        'transferencia' => 'Transferencia',
                                        'deposito' => 'Deposito',
                                        'pago_movil' => 'Pago movil',
                                        'tarjeta_simulada' => 'Tarjeta (simulada)',
                                    ];
                                @endphp
                                @foreach ($metodosPago as $valor => $label)
                                    <option value="{{ $valor }}" {{ old('metodo_pago', 'efectivo') === $valor ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="referencia" class="block text-sm font-medium text-gray-700">Referencia</label>
                            <input
                                type="text"
                                name="referencia"
                                id="referencia"
                                value="{{ old('referencia') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="123ABC"
                            >
                        </div>

                        <div>
                            <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <input
                                type="text"
                                name="observaciones"
                                id="observaciones"
                                value="{{ old('observaciones') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Pago al contado"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                        <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Confirmar venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
