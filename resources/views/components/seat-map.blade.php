@props([
    'seatNumbers' => [],
    'occupiedSeats' => [],
    'selectedSeats' => [],
    'seatCategories' => [],
    'name' => 'numero_asiento',
    'title' => 'Selector de asientos',
    'subtitle' => 'Los asientos ocupados aparecen bloqueados para evitar ventas duplicadas.',
])

@php
    $occupiedSeats = collect($occupiedSeats)->map(fn ($seat) => (string) $seat)->values()->all();
    $selectedSeats = collect($selectedSeats)->map(fn ($seat) => (string) $seat)->values()->all();
    $seatCategories = collect($seatCategories)->mapWithKeys(fn ($category, $seat) => [(string) $seat => $category])->all();
@endphp

<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-xl shadow-slate-200/60">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#003366]">Mapa de asientos</p>
            <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        </div>

        <div class="flex flex-wrap gap-2 text-xs font-semibold">
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-700">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                Libre
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-amber-800">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                VIP
            </span>
            <span class="inline-flex items-center gap-2 rounded-full bg-[#CC0000]/10 px-3 py-1.5 text-[#CC0000]">
                <span class="h-2.5 w-2.5 rounded-full bg-[#CC0000]"></span>
                Ocupado
            </span>
        </div>
    </div>

    @if (count($seatNumbers))
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4">
            @foreach($seatNumbers as $seat)
                @php
                    $seatKey = (string) $seat;
                    $isOccupied = in_array($seatKey, $occupiedSeats, true);
                    $isSelected = in_array($seatKey, $selectedSeats, true);
                    $seatCategory = $seatCategories[$seatKey] ?? 'estandar';
                @endphp

                <label
                    wire:key="seat-{{ $seatKey }}"
                    wire:click="seleccionarAsiento('{{ $seatKey }}')"
                    class="group relative flex cursor-pointer items-center justify-center rounded-2xl border px-3 py-6 text-center transition {{ $isOccupied ? 'border-[#CC0000]/30 bg-[#FBECEC] text-[#CC0000]' : 'bg-white' }} {{ $isSelected && ! $isOccupied ? 'ring-2 ring-[#003366] ring-offset-2' : '' }} {{ $isOccupied ? 'cursor-not-allowed opacity-80' : '' }} {{ $seatCategory === 'vip' && ! $isOccupied ? 'border-yellow-400 ring-2 ring-yellow-200' : '' }}">
                    <input
                        type="radio"
                        name="{{ $name }}"
                        value="{{ $seatKey }}"
                        class="sr-only"
                        {{ $isOccupied ? 'disabled' : '' }}
                        {{ in_array($seatKey, $selectedSeats, true) ? 'checked' : '' }}
                    >

                    {{-- Star for VIP (absolute above number) --}}
                    @if($seatCategory === 'vip')
                        <span class="absolute -top-3 inline-flex items-center justify-center rounded-full bg-yellow-400 text-white w-6 h-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.84-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z" />
                            </svg>
                        </span>
                    @endif

                    <span class="text-2xl font-extrabold tracking-tight {{ $isOccupied ? 'text-[#CC0000]' : 'text-slate-800' }}">{{ $seatKey }}</span>
                </label>
            @endforeach
        </div>
    @else
        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center text-sm text-slate-500">
            No hay asientos disponibles para mostrar.
        </div>
    @endif
</section>