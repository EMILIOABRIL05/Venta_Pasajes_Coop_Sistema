<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Gestión de Reembolsos</h1>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Solicitud</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reembolsos as $reembolso)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $reembolso->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $reembolso->venta_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($reembolso->monto, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $reembolso->motivo }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $reembolso->fecha_solicitud->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="aprobar({{ $reembolso->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded mr-2">
                                Aprobar
                            </button>
                            <button wire:click="rechazar({{ $reembolso->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                Rechazar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No hay solicitudes de reembolso pendientes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>