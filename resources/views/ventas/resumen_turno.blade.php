<x-layouts.app title="Resumen de Turno">
    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Resumen de Turno</h1>
            <p class="mt-2 text-sm text-gray-600">
                Cajero: <span class="font-semibold">{{ auth()->user()->name }}</span>
                &mdash; Fecha: <span class="font-semibold">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
            </p>
        </div>

        {{-- Tarjetas de totales generales --}}
        <div class="grid gap-6 sm:grid-cols-2 mb-8">
            {{-- Total recaudado --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                    <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-green-700 uppercase tracking-wide">Total Recaudado</p>
                    <p class="text-3xl font-bold text-green-900">${{ number_format($totalVentas, 2) }}</p>
                </div>
            </div>

            {{-- Total boletos --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 flex items-center gap-4">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                    <svg class="h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-blue-700 uppercase tracking-wide">Boletos Vendidos</p>
                    <p class="text-3xl font-bold text-blue-900">{{ $totalBoletos }}</p>
                </div>
            </div>
        </div>

        {{-- Tabla de recaudación por ruta --}}
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-gray-800 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Recaudación por Ruta</h2>
            </div>

            @if($recaudacionPorRuta->isEmpty())
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-500">No se registran ventas para el día de hoy.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ruta</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Boletos</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Recaudado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recaudacionPorRuta as $index => $item)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['ruta'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-0.5 text-sm font-medium text-blue-800">
                                        {{ $item['boletos_count'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                    ${{ number_format($item['total_recaudado'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100">
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-900 uppercase">Total General</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-0.5 text-sm font-bold text-green-800">
                                    {{ $totalBoletos }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-green-700">
                                ${{ number_format($totalVentas, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="mt-8 flex items-center justify-between">
            <p class="text-xs text-gray-500">Generado el {{ now()->format('d/m/Y H:i') }}</p>
            <div class="flex gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-150">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Imprimir
                </button>
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-150">
                    Volver
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
