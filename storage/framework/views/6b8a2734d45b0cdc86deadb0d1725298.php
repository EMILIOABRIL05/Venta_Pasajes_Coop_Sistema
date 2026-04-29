<div class="bg-gray-100 p-6 rounded-lg shadow-md max-w-5xl mx-auto border border-gray-200 mt-8">
    <h2 class="text-2xl font-bold text-[#003366] mb-6">Encuentra tu próximo viaje</h2>

    <form wire:submit.prevent="buscar" class="flex flex-col md:flex-row gap-4 items-end">
        
        <div class="w-full md:w-1/4">
            <label for="origen" class="block text-sm font-bold text-gray-800 mb-2">Origen</label>
            <select wire:model="origen" id="origen" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
                <option value="">Seleccione ciudad...</option>
                <option value="Ambato">Ambato</option>
                <option value="Quito">Quito</option>
                <option value="Guayaquil">Guayaquil</option>
            </select>
        </div>

        <div class="w-full md:w-1/4">
            <label for="destino" class="block text-sm font-bold text-gray-800 mb-2">Destino</label>
            <select wire:model="destino" id="destino" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
                <option value="">Seleccione ciudad...</option>
                <option value="Ambato">Ambato</option>
                <option value="Quito">Quito</option>
                <option value="Guayaquil">Guayaquil</option>
            </select>
        </div>

        <div class="w-full md:w-1/4">
            <label for="fecha" class="block text-sm font-bold text-gray-800 mb-2">Fecha de Viaje</label>
            <input type="date" wire:model="fecha" id="fecha" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
        </div>

        <div class="w-full md:w-1/6">
            <label for="pasajeros" class="block text-sm font-bold text-gray-800 mb-2">Pasajeros</label>
            <input type="number" wire:model="pasajeros" id="pasajeros" min="1" max="10" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-[#003366] focus:border-[#003366] text-gray-800 outline-none">
        </div>

        <div class="w-full md:w-auto">
            <button type="submit" class="w-full md:w-auto bg-[#CC0000] hover:bg-red-800 text-white font-bold py-3 px-8 rounded-md transition duration-200 shadow-sm">
                Buscar Pasajes
            </button>
        </div>
    </form>
</div><?php /**PATH C:\coopSistema\resources\views/livewire/buscador-pasajes.blade.php ENDPATH**/ ?>