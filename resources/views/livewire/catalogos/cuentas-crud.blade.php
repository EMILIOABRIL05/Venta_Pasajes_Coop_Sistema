<div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(0,51,102,0.14),_transparent_34%),linear-gradient(180deg,#f3f4f6_0%,#ffffff_66%)] py-10">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[#003366] text-white shadow-2xl shadow-slate-300/50">
            <div class="grid gap-6 p-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)] lg:p-8">
                <div>
                    <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em] text-blue-100">Catálogos / Cuentas</span>
                    <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Cuentas para Oficinistas y Choferes</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-100 sm:text-base">
                        Gestiona el alta, edición y eliminación lógica de las cuentas operativas del sistema con datos de contacto, cédula y rol asignado.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">Oficinistas</p>
                        <p class="mt-2 text-3xl font-black">{{ $totalOficinistas }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-100">Choferes</p>
                        <p class="mt-2 text-3xl font-black">{{ $totalChoferes }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#CC0000]/30 bg-[#CC0000]/15 p-4 backdrop-blur">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-red-100">Acceso</p>
                        <p class="mt-2 text-sm font-semibold text-red-50">Solo administradores</p>
                    </div>
                </div>
            </div>

            @if (session('message'))
                <div class="mx-6 mb-6 rounded-2xl border border-[#003366]/20 bg-[#003366]/10 px-4 py-3 text-sm font-medium text-[#003366]">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mx-6 mb-6 rounded-2xl border border-[#CC0000]/30 bg-[#CC0000]/10 px-4 py-3 text-sm font-medium text-red-100">
                    {{ session('error') }}
                </div>
            @endif
        </section>

        <div class="grid gap-6 xl:grid-cols-[430px_minmax(0,1fr)]">
            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ $userId ? 'Editar cuenta' : 'Crear cuenta' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Completa los datos obligatorios y asigna el rol operativo correspondiente.</p>
                    </div>

                    <button type="button" wire:click="resetForm" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        Nuevo
                    </button>
                </div>

                <form wire:key="cuenta-form-{{ $userId ?? 'new' }}" wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700" for="name">Nombre completo</label>
                        <input id="name" type="text" wire:model="name" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="Ej. Manuel Cusme">
                        @error('name') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="cedula">Cédula</label>
                            <input id="cedula" type="text" wire:model="cedula" inputmode="numeric" maxlength="10" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="10 dígitos">
                            @error('cedula') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="telefono">Teléfono</label>
                            <input id="telefono" type="text" wire:model="telefono" inputmode="numeric" maxlength="10" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="0987654321">
                            @error('telefono') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="email">Correo electrónico</label>
                            <input id="email" type="email" wire:model="email" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="persona@cooperativa.test">
                            @error('email') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input id="fecha_nacimiento" type="date" wire:model="fecha_nacimiento" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                            @error('fecha_nacimiento') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="tipo_usuario">Tipo de cuenta</label>
                            <select id="tipo_usuario" wire:model="tipo_usuario" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]">
                                <option value="oficinista">Oficinista</option>
                                <option value="chofer">Chofer</option>
                            </select>
                            @error('tipo_usuario') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado del acceso</p>
                            <p class="mt-2 text-sm font-semibold text-slate-800">
                                {{ $userId ? 'La contraseña es opcional si no se desea cambiar.' : 'La contraseña es obligatoria para nuevas cuentas.' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="password">Contraseña</label>
                            <input id="password" type="password" wire:model="password" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="********">
                            @error('password') <p class="mt-2 text-sm text-[#CC0000]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700" for="password_confirmation">Confirmar contraseña</label>
                            <input id="password_confirmation" type="password" wire:model="password_confirmation" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#003366] focus:ring-[#003366]" placeholder="********">
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#003366] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#00284d]">
                            {{ $userId ? 'Actualizar cuenta' : 'Guardar cuenta' }}
                        </button>

                        @if ($userId)
                            <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancelar
                            </button>
                        @endif
                    </div>
                </form>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Listado de cuentas</h2>
                        <p class="text-sm text-slate-500">Se muestran únicamente las cuentas de operación creadas para oficinistas y choferes.</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    @forelse ($usuarios as $usuario)
                        <article class="rounded-2xl border border-slate-200 p-5 transition hover:border-[#003366]/30 hover:shadow-md">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $usuario->name }}</h3>
                                        <span class="rounded-full bg-[#003366]/10 px-3 py-1 text-xs font-semibold text-[#003366]">{{ ucfirst($usuario->tipo_usuario) }}</span>
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $usuario->cedula }}</span>
                                    </div>

                                    <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                                        <p><span class="font-semibold text-slate-900">Correo:</span> {{ $usuario->email }}</p>
                                        <p><span class="font-semibold text-slate-900">Teléfono:</span> {{ $usuario->telefono }}</p>
                                        <p><span class="font-semibold text-slate-900">Nacimiento:</span> {{ $usuario->fecha_nacimiento?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                        <p><span class="font-semibold text-slate-900">ID:</span> {{ $usuario->id }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="edit({{ $usuario->id }})" class="rounded-xl border border-[#003366]/20 bg-[#003366]/10 px-4 py-2 text-sm font-semibold text-[#003366] transition hover:bg-[#003366]/20">
                                        Editar
                                    </button>
                                    <button type="button" wire:click="delete({{ $usuario->id }})" wire:confirm="¿Eliminar esta cuenta?" class="rounded-xl border border-[#CC0000]/20 bg-[#CC0000]/10 px-4 py-2 text-sm font-semibold text-[#CC0000] transition hover:bg-[#CC0000]/20">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                            No hay cuentas de oficinistas o choferes registradas todavía.
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $usuarios->links() }}
                </div>
            </section>
        </div>
    </div>
</div>
