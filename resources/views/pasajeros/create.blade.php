<x-layouts.app title="Registrar Pasajero">
    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Registrar nuevo pasajero</h1>
                <p class="mt-2 text-sm text-gray-600">Complete los datos del cliente para continuar con la venta o reserva.</p>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 mb-6">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->has('general'))
                <div class="rounded-lg bg-red-50 border border-red-200 p-4 mb-6">
                    <p class="text-sm text-red-700">{{ $errors->first('general') }}</p>
                </div>
            @endif

            <form action="{{ route('pasajeros.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="nombre_completo" class="block text-sm font-medium text-gray-700">Nombre completo</label>
                    <input
                        type="text"
                        name="nombre_completo"
                        id="nombre_completo"
                        value="{{ old('nombre_completo') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ej. Juan Pérez"
                        required
                    >
                    @error('nombre_completo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cedula" class="block text-sm font-medium text-gray-700">Cédula</label>
                    <input
                        type="text"
                        name="cedula"
                        id="cedula"
                        value="{{ old('cedula') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="1234567890"
                        maxlength="10"
                        pattern="\d{10}"
                        required
                    >
                    @error('cedula')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                    <input
                        type="email"
                        name="correo"
                        id="correo"
                        value="{{ old('correo') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="correo@ejemplo.com"
                        required
                    >
                    @error('correo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input
                        type="tel"
                        name="telefono"
                        id="telefono"
                        value="{{ old('telefono') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0998765432"
                        required
                    >
                    @error('telefono')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ url()->previous() }}" class="text-sm text-gray-600 hover:text-gray-900">Cancelar</a>
                    <button type="submit" class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Guardar pasajero
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
