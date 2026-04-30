<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-xl shadow-slate-200/60 backdrop-blur">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Catálogos</span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Categorías de bus</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Administra las categorías que agrupan la flota y determinan su presentación comercial.</p>
                </div>

                <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Nueva categoría
                </button>
            </div>

            @if (session('message'))
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $categoriaId ? 'Editar categoría' : 'Crear categoría' }}</h2>
                        <p class="text-sm text-slate-500">Define nombre y descripción comercial.</p>
                    </div>
                </div>

                <form wire:submit.prevent="save" class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="nombre">Nombre</label>
                        <input id="nombre" type="text" wire:model="nombre" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ej. Ejecutivo">
                        @error('nombre') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="descripcion">Descripción</label>
                        <textarea id="descripcion" wire:model="descripcion" rows="5" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ej. Buses de servicio con mayor confort y amenidades."></textarea>
                        @error('descripcion') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            {{ $categoriaId ? 'Actualizar' : 'Guardar' }}
                        </button>

                        @if ($categoriaId)
                            <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60">
                <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Listado de categorías</h2>
                        <p class="text-sm text-slate-500">Cada registro muestra cuántos buses tiene asociados.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse ($categorias as $categoria)
                        <article class="rounded-2xl border border-slate-200 p-5 transition hover:border-emerald-200 hover:shadow-md">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $categoria->nombre }}</h3>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $categoria->buses_count }} buses</span>
                                    </div>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                                        {{ $categoria->descripcion ?: 'Sin descripción registrada.' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="edit({{ $categoria->id }})" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">
                                        Editar
                                    </button>
                                    <button type="button" wire:click="delete({{ $categoria->id }})" wire:confirm="¿Eliminar esta categoría?" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                            Aún no se han creado categorías.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $categorias->links() }}
                </div>
            </section>
        </div>
    </div>
</div>