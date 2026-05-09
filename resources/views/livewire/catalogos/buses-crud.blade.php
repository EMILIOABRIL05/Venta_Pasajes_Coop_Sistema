<div class="min-h-screen bg-[#F3F4F6] bg-[radial-gradient(circle_at_top,_rgba(0,51,102,0.12),_transparent_38%),linear-gradient(180deg,#F3F4F6_0%,#ffffff_65%)] py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-[#003366] p-6 text-white shadow-2xl shadow-slate-300/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Flota</span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight">Buses</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Registra la flota, sube la foto del bus y define su mapa lógico de asientos para la operación del sistema.</p>
                </div>

                <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-[#003366] transition hover:bg-slate-100">
                    Nuevo bus
                </button>
            </div>

            @if (session('message'))
                <div class="mt-6 rounded-2xl border border-[#003366]/20 bg-[#003366]/10 px-4 py-3 text-sm font-medium text-[#003366]">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-2xl border border-[#CC0000]/20 bg-[#CC0000]/10 px-4 py-3 text-sm font-medium text-[#CC0000]">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="grid gap-6 xl:grid-cols-[460px_minmax(0,1fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $busId ? 'Editar bus' : 'Crear bus' }}</h2>
                        <p class="text-sm text-slate-500">Carga foto, datos técnicos y configuración base del asiento.</p>
                    </div>
                </div>

                <form wire:key="bus-form-{{ $busId ?? 'new' }}" wire:submit.prevent="save" class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="categoria_bus_id">Categoría</label>
                        <select id="categoria_bus_id" wire:model="categoria_bus_id" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                            <option value="">Seleccione una categoría...</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                            @error('categoria_bus_id') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="placa">Placa</label>
                            <input id="placa" type="text" wire:model="placa" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="ABC-1234">
                            @error('placa') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="anio">Año</label>
                            <input id="anio" type="number" wire:model="anio" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="2026">
                            @error('anio') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="marca_chasis">Marca del chasis</label>
                            <input id="marca_chasis" type="text" wire:model="marca_chasis" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="Mercedes Benz">
                            @error('marca_chasis') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="carroceria">Carrocería</label>
                            <input id="carroceria" type="text" wire:model="carroceria" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="Marcopolo">
                            @error('carroceria') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="estado">Estado</label>
                            <select id="estado" wire:model="estado" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                                <option value="disponible">Disponible</option>
                                <option value="en_ruta">En ruta</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                            @error('estado') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-2xl border border-[#003366]/15 bg-[#003366]/5 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-[#003366]">Capacidad estimada</p>
                            <p class="mt-2 text-2xl font-black text-[#003366]">
                                {{ \App\Models\Bus::calcularCapacidad((int) $filas, true) }} asientos
                            </p>
                            <p class="mt-1 text-xs text-[#003366]">Calculado automáticamente desde filas (pasillo central por defecto).</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="filas">Filas</label>
                            <input id="filas" type="number" wire:model="filas" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="10">
                            @error('filas') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        {{-- Pasillo central fijo: no editable (estándar interprovincial 2+2) --}}
                        <div class="rounded-2xl border border-slate-200 px-4 py-4 text-sm font-semibold text-slate-700">
                            <p>Pasillo central: <span class="font-semibold">Sí (por defecto)</span></p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="foto">Foto del bus</label>
                        <input id="foto" type="file" wire:model="foto" accept="image/*" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[#003366] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#00284d]">
                        @error('foto') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror

                        @if ($foto)
                            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                                    <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa del bus" class="h-56 w-full object-cover">
                            </div>
                        @elseif ($fotoActual)
                            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                                    <img src="{{ asset('storage/' . $fotoActual) }}" alt="Foto actual del bus" class="h-56 w-full object-cover">
                            </div>
                        @endif
                    </div>

                    {{-- Panel JSON removido por simplicidad; la estructura se guarda implícitamente --}}

                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#003366] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#00284d]">
                            {{ $busId ? 'Actualizar' : 'Guardar' }}
                        </button>

                        @if ($busId)
                            <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Listado de buses</h2>
                        <p class="text-sm text-slate-500">Muestra categoría, placa, estado, asiento lógico y foto.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse ($buses as $bus)
                        <article class="overflow-hidden rounded-2xl border border-slate-200 transition hover:border-[#003366]/20 hover:shadow-md">
                            <div class="grid gap-0 md:grid-cols-[160px_minmax(0,1fr)]">
                                <div class="bg-slate-100">
                                        @if ($bus->foto_url)
                                            <img src="{{ $bus->foto_url }}" alt="Foto de {{ $bus->placa }}" class="h-full min-h-44 w-full object-cover">
                                    @else
                                        <div class="flex min-h-44 items-center justify-center bg-slate-100 text-sm font-semibold text-slate-400">Sin foto</div>
                                    @endif
                                </div>

                                <div class="p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="space-y-3">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-base font-bold text-slate-900">{{ $bus->placa }}</h3>
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $bus->categoria?->nombre ?? 'Sin categoría' }}</span>
                                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $bus->estado === 'disponible' ? 'bg-[#003366]/10 text-[#003366]' : ($bus->estado === 'en_ruta' ? 'bg-slate-200 text-slate-700' : 'bg-[#CC0000]/10 text-[#CC0000]') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $bus->estado)) }}
                                                </span>
                                            </div>

                                            <p class="text-sm text-slate-600">
                                                {{ $bus->marca_chasis }} · {{ $bus->carroceria }} · {{ $bus->anio }}
                                            </p>

                                            <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                                <p><span class="font-semibold text-slate-900">Asientos:</span> {{ $bus->numero_asientos }}</p>
                                                <p><span class="font-semibold text-slate-900">Mapa:</span> {{ $bus->mapa_asientos_resumen }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="edit({{ $bus->id }})" class="rounded-xl border border-[#003366]/20 bg-[#003366]/10 px-4 py-2 text-sm font-semibold text-[#003366] transition hover:bg-[#003366]/20">
                                                Editar
                                            </button>
                                            <button type="button" wire:click="delete({{ $bus->id }})" wire:confirm="¿Eliminar este bus?" class="rounded-xl border border-[#CC0000]/20 bg-[#CC0000]/10 px-4 py-2 text-sm font-semibold text-[#CC0000] transition hover:bg-[#CC0000]/20">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                            Aún no se han creado buses.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $buses->links() }}
                </div>
            </section>
        </div>
    </div>
</div>