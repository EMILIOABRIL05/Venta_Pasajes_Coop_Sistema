<x-layouts.app title="Cierre de Turno">
    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        {{-- Encabezado --}}
        <div class="mb-8 flex items-start justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Cierre de Turno</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Cajero: <span class="font-semibold">{{ auth()->user()->name }}</span>
                    &mdash; Fecha: <span class="font-semibold">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                </p>
            </div>
            {{-- Badge de estado --}}
            @if($cierreExistente)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-4 py-1.5 text-sm font-semibold text-green-800 ring-1 ring-green-200">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                    </svg>
                    Turno cerrado
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-4 py-1.5 text-sm font-semibold text-yellow-800 ring-1 ring-yellow-200">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                    Pendiente de cierre
                </span>
            @endif
        </div>

        {{-- Alertas de sesión --}}
        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 p-4 flex items-center gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-yellow-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-medium text-yellow-700">{{ session('warning') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 flex items-center gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Aviso si el cierre ya fue registrado --}}
        @if($cierreExistente)
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 p-5">
                <p class="text-sm text-blue-800">
                    El cierre de este turno fue registrado el
                    <strong>{{ $cierreExistente->created_at->format('d/m/Y') }}</strong>
                    a las <strong>{{ $cierreExistente->created_at->format('H:i') }}</strong>.
                    Los totales mostrados a continuación reflejan el estado actual de las ventas del día
                    (pueden diferir del cierre original si se aprobaron reembolsos con posterioridad).
                </p>
            </div>
        @endif

        {{-- Tarjetas de resumen consolidado --}}
        <div class="grid gap-5 sm:grid-cols-3 mb-8">
            {{-- Ingreso bruto --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="rounded-full bg-blue-100 p-2">
                        <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Ingreso Bruto</p>
                </div>
                <p class="text-2xl font-bold text-gray-900">${{ number_format($totalBruto, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $totalBoletos }} boleto{{ $totalBoletos !== 1 ? 's' : '' }} vendido{{ $totalBoletos !== 1 ? 's' : '' }}</p>
            </div>

            {{-- Reembolsos --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="rounded-full bg-red-100 p-2">
                        <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Reembolsos</p>
                </div>
                <p class="text-2xl font-bold text-red-600">-${{ number_format($totalReembolsos, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500">Solo reembolsos aprobados</p>
            </div>

            {{-- Ingreso neto --}}
            <div class="bg-white border-2 border-green-300 rounded-xl p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="rounded-full bg-green-100 p-2">
                        <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-green-700 uppercase tracking-wide">Ingreso Neto</p>
                </div>
                <p class="text-2xl font-bold text-green-700">${{ number_format($totalNeto, 2) }}</p>
                <p class="mt-1 text-xs text-gray-500">Bruto − Reembolsos aprobados</p>
            </div>
        </div>

        {{-- Tabla de detalle por ruta --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8">
            <div class="px-6 py-4" style="background-color: #003366;">
                <h2 class="text-base font-semibold text-white">Desglose por Ruta</h2>
            </div>

            @if($recaudacionPorRuta->isEmpty())
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-500">No se registran ventas para el día de hoy.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ruta</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Boletos</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Recaudado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recaudacionPorRuta as $i => $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['ruta'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                        {{ $item['boletos_count'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-800">
                                    ${{ number_format($item['total_recaudado'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-900 uppercase">Total general</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">
                                    {{ $totalBoletos }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-base font-bold text-green-700">
                                ${{ number_format($totalBruto, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="flex items-center justify-between">
            <p class="text-xs text-gray-400">Generado el {{ now()->format('d/m/Y H:i') }}</p>
            <div class="flex gap-3">
                <button
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Imprimir
                </button>

                @if(!$cierreExistente)
                    <form method="POST" action="{{ route('ventas.cierre-turno.store') }}"
                          onsubmit="return confirm('¿Confirmar el cierre de turno del {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}? Esta acción no se puede deshacer.')">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors"
                            style="background-color: #003366;"
                            onmouseover="this.style.backgroundColor='#002244'"
                            onmouseout="this.style.backgroundColor='#003366'"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            Registrar Cierre de Turno
                        </button>
                    </form>
                @else
                    <span class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-gray-400 bg-gray-100 cursor-not-allowed select-none">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                        </svg>
                        Turno ya cerrado
                    </span>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
