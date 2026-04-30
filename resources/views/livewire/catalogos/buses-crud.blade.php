<div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.10),_transparent_38%),linear-gradient(180deg,#f8fafc_0%,#ffffff_65%)] py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-2xl shadow-slate-300/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Flota</span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight">Buses</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Registra la flota, sube la foto del bus y define su mapa lógico de asientos para la operación del sistema.</p>
                </div>

                <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                    Nuevo bus
                </button>
            </div>

            @if (session('message'))
                <div class="mt-6 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-200">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-2xl border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm font-medium text-rose-200">
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

                <form wire:submit.prevent="save" class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="categoria_bus_id">Categoría</label>
                        <select id="categoria_bus_id" wire:model="categoria_bus_id" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione una categoría...</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_bus_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="placa">Placa</label>
                            <input id="placa" type="text" wire:model="placa" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="ABC-1234">
                            @error('placa') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="anio">Año</label>
                            <input id="anio" type="number" wire:model="anio" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="2026">
                            @error('anio') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="marca_chasis">Marca del chasis</label>
                            <input id="marca_chasis" type="text" wire:model="marca_chasis" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Mercedes Benz">
                            @error('marca_chasis') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="carroceria">Carrocería</label>
                            <input id="carroceria" type="text" wire:model="carroceria" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Marcopolo">
                            @error('carroceria') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="numero_asientos">Número de asientos</label>
                            <input id="numero_asientos" type="number" wire:model="numero_asientos" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="40">
                            @error('numero_asientos') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="estado">Estado</label>
                            <select id="estado" wire:model="estado" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="disponible">Disponible</option>
                                <option value="en_ruta">En ruta</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                            @error('estado') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="filas">Filas</label>
                            <input id="filas" type="number" wire:model="filas" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="10">
                            @error('filas') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-4 text-sm font-semibold text-slate-700">
                            <input type="checkbox" wire:model="pasillo" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Pasillo central
                        </label>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="foto">Foto del bus</label>
                        <input id="foto" type="file" wire:model="foto" accept="image/*" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700">
                        @error('foto') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

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

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-2 text-sm font-semibold text-slate-700">Estructura JSON de asientos</div>
                        <pre class="overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-6 text-emerald-300">{{ json_encode(['filas' => (int) $filas, 'pasillo' => (bool) $pasillo], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
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
                        <article class="overflow-hidden rounded-2xl border border-slate-200 transition hover:border-emerald-200 hover:shadow-md">
                            <div class="grid gap-0 md:grid-cols-[160px_minmax(0,1fr)]">
                                <div class="bg-slate-100">
                                    @if ($bus->foto)
                                        <img src="{{ asset('storage/' . $bus->foto) }}" alt="Foto de {{ $bus->placa }}" class="h-full min-h-44 w-full object-cover">
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
                                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $bus->estado === 'disponible' ? 'bg-emerald-100 text-emerald-700' : ($bus->estado === 'en_ruta' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $bus->estado)) }}
                                                </span>
                                            </div>

                                            <p class="text-sm text-slate-600">
                                                {{ $bus->marca_chasis }} · {{ $bus->carroceria }} · {{ $bus->anio }}
                                            </p>

                                            <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                                <p><span class="font-semibold text-slate-900">Asientos:</span> {{ $bus->numero_asientos }}</p>
                                                <p><span class="font-semibold text-slate-900">Mapa:</span> {{ $bus->mapa_asientos['filas'] ?? 'N/D' }} filas, {{ !empty($bus->mapa_asientos['pasillo']) ? 'con pasillo' : 'sin pasillo' }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="edit({{ $bus->id }})" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                                                Editar
                                            </button>
                                            <button type="button" wire:click="delete({{ $bus->id }})" wire:confirm="¿Eliminar este bus?" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
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