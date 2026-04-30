<div class="min-h-screen bg-slate-50 py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-slate-950 p-8 text-white shadow-2xl shadow-slate-300/40">
            <span class="inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-300">Panel de administración</span>
            <h1 class="mt-4 text-3xl font-black tracking-tight">Acceso maestro del sistema</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Desde aquí accedes rápidamente a los catálogos de flota que sostienen la operación de ventas y programación.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <a href="{{ route('catalogos.buses') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Buses</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Registra la flota, su foto, estado operativo y mapa lógico de asientos.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 transition group-hover:bg-emerald-200">Abrir</span>
                </div>
            </a>

            <a href="{{ route('catalogos.categorias-bus') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/60 transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Categorías de bus</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Administra las clases comerciales que agrupan la flota y alimentan el catálogo.</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 transition group-hover:bg-emerald-200">Abrir</span>
                </div>
            </a>
        </div>
    </div>
</div>
