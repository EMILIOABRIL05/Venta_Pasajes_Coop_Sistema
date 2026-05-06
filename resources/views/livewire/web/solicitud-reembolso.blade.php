<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4 text-center">Solicitud de Reembolso</h2>

    <form wire:submit.prevent="submit" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="boleto_uuid" class="block text-sm font-medium text-gray-700">UUID del Boleto</label>
            <input type="text" wire:model="boleto_uuid" id="boleto_uuid" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Ingrese el UUID del boleto">
            @error('boleto_uuid') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="motivo" class="block text-sm font-medium text-gray-700">Motivo del Reembolso</label>
            <textarea wire:model="motivo" id="motivo" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Describa el motivo del reembolso"></textarea>
            @error('motivo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="evidencia" class="block text-sm font-medium text-gray-700">Evidencia (Opcional)</label>
            <input type="file" wire:model="evidencia" id="evidencia" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            @error('evidencia') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Enviar Solicitud
        </button>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>