@props([
    'seatNumbers' => [],
    'occupiedSeats' => [],
    'selectedSeat' => null,
    'name' => 'numero_asiento',
    'title' => 'Selector de asientos',
    'subtitle' => 'Los asientos ocupados aparecen bloqueados para evitar ventas duplicadas.',
])

@php
    $occupiedSeats = collect($occupiedSeats)->map(fn ($seat) => (string) $seat)->values()->all();
    $selectedSeat = filled($selectedSeat) ? (string) $selectedSeat : null;
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
            <span class="inline-flex items-center gap-2 rounded-full bg-[#CC0000]/10 px-3 py-1.5 text-[#CC0000]">
                <span class="h-2.5 w-2.5 rounded-full bg-[#CC0000]"></span>
                Ocupado
            </span>
        </div>
    </div>

    @if (count($seatNumbers))
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach($seatNumbers as $seat)
                @php
                    $seatKey = (string) $seat;
                    $isOccupied = in_array($seatKey, $occupiedSeats, true);
                    $isSelected = $selectedSeat === $seatKey;
                @endphp

                <label class="group relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border px-3 py-4 text-center transition {{ $isOccupied ? 'border-[#CC0000]/30 bg-[#CC0000]/5 text-[#CC0000]' : 'border-emerald-200 bg-emerald-50/80 text-emerald-800 hover:-translate-y-0.5 hover:border-[#003366]/30 hover:bg-[#003366]/5' }} {{ $isSelected && ! $isOccupied ? 'ring-2 ring-[#003366] ring-offset-2' : '' }} {{ $isOccupied ? 'cursor-not-allowed opacity-90' : '' }}">
                    <input
                        type="radio"
                        name="{{ $name }}"
                        value="{{ $seatKey }}"
                        class="sr-only"
                        {{ $isOccupied ? 'disabled' : '' }}
                        {{ $selectedSeat === $seatKey ? 'checked' : '' }}
                    >

                    <span class="text-lg font-black tracking-tight">{{ $seatKey }}</span>
                    <span class="mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] {{ $isOccupied ? 'bg-[#CC0000]/10 text-[#CC0000]' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $isOccupied ? 'Ocupado' : 'Libre' }}
                    </span>

                    @if ($isSelected && ! $isOccupied)
                        <span class="mt-2 text-xs font-semibold text-[#003366]">Seleccionado</span>
                    @endif
                </label>
            @endforeach
        </div>
    @else
        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 px-6 py-10 text-center text-sm text-slate-500">
            No hay asientos disponibles para mostrar.
        </div>
    @endif
</section>