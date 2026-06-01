<div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
    <section class="rounded-2xl bg-[#003366] p-6 text-white shadow-lg">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Entrega final</p>
                <h1 class="mt-2 text-3xl font-black">Datos reales sembrados</h1>
                <p class="mt-2 max-w-3xl text-sm text-blue-100">
                    Resumen operativo de paradas, rutas, frecuencias, precios y categorias de asiento cargadas por seeders.
                </p>
            </div>

            <button type="button" onclick="window.datosEntrega?.imprimirResumen()" class="rounded-xl bg-white px-4 py-3 text-sm font-bold text-[#003366] transition hover:bg-slate-100">
                Imprimir resumen
            </button>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-5">
        @foreach($totales as $label => $total)
            <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black text-[#003366]">{{ $total }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-xl font-black text-slate-900">Rutas y frecuencias</h2>
                <p class="text-sm text-slate-500">Precios base y numero de salidas configuradas para la operacion.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-3 pr-4">Origen</th>
                            <th class="py-3 pr-4">Destino</th>
                            <th class="py-3 pr-4 text-right">Precio</th>
                            <th class="py-3 pr-4 text-right">Duracion</th>
                            <th class="py-3 text-right">Frecuencias</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rutas as $ruta)
                            <tr>
                                <td class="py-3 pr-4 font-semibold text-slate-900">{{ $ruta->origen->ciudad ?? 'N/D' }}</td>
                                <td class="py-3 pr-4 text-slate-700">{{ $ruta->destino->ciudad ?? 'N/D' }}</td>
                                <td class="py-3 pr-4 text-right font-bold text-[#003366]">${{ number_format($ruta->precio_base, 2) }}</td>
                                <td class="py-3 pr-4 text-right text-slate-600">{{ $ruta->tiempo_estimado_minutos }} min</td>
                                <td class="py-3 text-right text-slate-600">{{ $ruta->frecuencias_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </article>

        <div class="space-y-6">
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-900">Categorias de asiento</h2>
                <div class="mt-4 space-y-3">
                    @foreach($categorias as $categoria)
                        <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3">
                            <span class="seat-category-pill" style="--seat-color: {{ $categoria->color_hex }}">
                                {{ $categoria->nombre }}
                            </span>
                            <span class="font-bold text-slate-800">+${{ number_format($categoria->recargo, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-900">Buses categorizados</h2>
                <div class="mt-4 space-y-3">
                    @foreach($buses as $bus)
                        @php
                            $asientosPorCategoria = collect($bus->mapa_asientos['asientos'] ?? [])->groupBy('categoria_id')->map->count();
                        @endphp
                        <div class="rounded-xl border border-slate-100 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-bold text-slate-900">{{ $bus->placa }}</p>
                                <p class="text-sm font-semibold text-[#003366]">{{ $bus->numero_asientos }} asientos</p>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($categorias as $categoria)
                                    <span class="seat-category-pill" style="--seat-color: {{ $categoria->color_hex }}">
                                        {{ $categoria->nombre }} {{ $asientosPorCategoria->get($categoria->id, 0) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </div>
    </section>
</div>
