<x-layouts.app title="Detalle de Venta #{{ $venta->id }}">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('ventanilla.ventas.index') }}" class="text-gray-400 hover:text-indigo-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                        Detalle de Venta <span class="text-indigo-600">#{{ $venta->id }}</span>
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Fecha: {{ $venta->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
            <a href="#" onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-slate-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Recibo
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Tarjeta de Resumen --}}
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden ring-1 ring-gray-100 p-6 flex flex-col sm:flex-row justify-between items-center gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-widest mb-1">Total Cobrado</p>
                    <p class="text-4xl font-extrabold text-emerald-600 tracking-tight">
                        ${{ number_format($venta->total, 2) }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-50 rounded-xl px-4 py-3 border border-indigo-100 text-center">
                        <p class="text-xs text-indigo-400 uppercase font-semibold">Boletos</p>
                        <p class="text-xl font-bold text-indigo-700">{{ $venta->boletos->count() }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100 text-center">
                        <p class="text-xs text-gray-400 uppercase font-semibold">Estado</p>
                        <p class="text-xl font-bold text-gray-700">Completada</p>
                    </div>
                </div>
            </div>

            {{-- Detalle de Boletos --}}
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden ring-1 ring-gray-100">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-800">Boletos Emitidos</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Código</th>
                                <th class="px-6 py-4 text-left font-semibold">Pasajero</th>
                                <th class="px-6 py-4 text-center font-semibold">Asiento</th>
                                <th class="px-6 py-4 text-center font-semibold">Estado</th>
                                <th class="px-6 py-4 text-right font-semibold">Precio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @foreach($venta->boletos as $boleto)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-indigo-600">
                                        {{ $boleto->codigo_reserva }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-800">{{ $boleto->pasajero->nombre_completo ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">C.I: {{ $boleto->pasajero->cedula ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs">
                                            {{ $boleto->numero_asiento }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($boleto->estado === 'Anulado')
                                            <span class="inline-flex px-2 py-1 rounded-full bg-red-100 text-red-700 text-[10px] font-bold uppercase tracking-wider">
                                                Anulado
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">
                                                Vendido
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-700">
                                        ${{ number_format($boleto->precio_final, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
