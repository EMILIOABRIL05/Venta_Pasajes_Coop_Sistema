<div class="min-h-screen bg-[#F3F4F6] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-[#003366] rounded-t-2xl p-8 shadow-lg text-white text-center">
            <h1 class="text-3xl font-bold uppercase tracking-wider">Finalizar Pago</h1>
            <p class="mt-2 text-blue-100 italic">Cooperativa de Transportes Ambato</p>
        </div>

        <div class="bg-white rounded-b-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-[#1F2937]">Resumen de Compra</h2>
                            <p class="text-sm text-gray-500">ID de Venta: #{{ $venta->id }}</p>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-sm text-gray-400 block uppercase tracking-tighter">Total a Pagar</span>
                            <span class="text-3xl font-black text-[#003366]">${{ number_format($venta->total, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if (session('info'))
                    <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-[#003366]">
                        {{ session('info') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-[#CC0000]">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="bg-blue-50 border-l-4 border-[#003366] p-6 mb-8 rounded-r-lg">
                    <h3 class="font-bold text-[#003366] mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Instrucciones de Pago
                    </h3>

                    @if ($metodo_pago === 'paypal')
                        <p class="text-gray-700 text-sm leading-relaxed">
                            Completa el pago en PayPal y registra el ID de transaccion para confirmar la venta.
                        </p>
                    @elseif ($metodo_pago === 'tarjeta')
                        <p class="text-gray-700 text-sm leading-relaxed">
                            Ingresa los datos de autorizacion de la tarjeta. El sistema no guarda el numero completo ni el CVV.
                        </p>
                    @elseif ($metodo_pago === 'deuna')
                        <p class="text-gray-700 text-sm leading-relaxed">
                            Ingresa el codigo Deuna o sube la imagen QR/comprobante para que ventanilla valide el pago.
                        </p>
                    @else
                        <p class="text-gray-700 text-sm leading-relaxed">
                            Usa la siguiente cuenta y sube el comprobante de transferencia:
                        </p>
                        <div class="mt-4 bg-white p-4 rounded border border-blue-100 font-mono text-sm text-[#003366]">
                            {{ $banco_destino }} <br>
                            Beneficiario: Cooperativa Ambato
                        </div>
                    @endif
                </div>

                <form wire:submit.prevent="guardarPago" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-[#1F2937]" for="metodo_pago">
                            Metodo de pago
                        </label>
                        <select
                            id="metodo_pago"
                            wire:model.live="metodo_pago"
                            class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]"
                        >
                            @foreach ($metodosPago as $valor => $label)
                                <option value="{{ $valor }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('metodo_pago')
                            <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($metodo_pago === 'paypal')
                        <div>
                            <label class="block text-sm font-medium text-[#1F2937]" for="referencia">
                                ID de transaccion PayPal
                            </label>
                            <input
                                id="referencia"
                                type="text"
                                wire:model="referencia"
                                placeholder="Ej: PAYPAL-9AB12345"
                                class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]"
                            >
                            @error('referencia')
                                <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    @if ($metodo_pago === 'tarjeta')
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#1F2937]" for="titular_tarjeta">
                                    Titular de la tarjeta
                                </label>
                                <input id="titular_tarjeta" type="text" wire:model="titular_tarjeta" class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]">
                                @error('titular_tarjeta') <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-[#1F2937]" for="numero_tarjeta">
                                    Numero de tarjeta
                                </label>
                                <input id="numero_tarjeta" type="text" inputmode="numeric" wire:model="numero_tarjeta" placeholder="4111 1111 1111 1111" class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]">
                                @error('numero_tarjeta') <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1F2937]" for="expiracion_tarjeta">
                                    Expiracion
                                </label>
                                <input id="expiracion_tarjeta" type="text" wire:model="expiracion_tarjeta" placeholder="MM/AA" class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]">
                                @error('expiracion_tarjeta') <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1F2937]" for="codigo_seguridad">
                                    CVV
                                </label>
                                <input id="codigo_seguridad" type="password" inputmode="numeric" wire:model="codigo_seguridad" class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]">
                                @error('codigo_seguridad') <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif

                    @if ($metodo_pago === 'deuna')
                        <div>
                            <label class="block text-sm font-medium text-[#1F2937]" for="codigo_deuna">
                                Codigo Deuna
                            </label>
                            <input
                                id="codigo_deuna"
                                type="text"
                                wire:model="codigo_deuna"
                                placeholder="Ej: DEUNA-QR-123456"
                                class="mt-1 block w-full rounded-xl border-gray-300 text-sm focus:border-[#003366] focus:ring-[#003366]"
                            >
                            @error('codigo_deuna')
                                <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    @if (in_array($metodo_pago, ['deuna', 'transferencia'], true))
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#1F2937]">
                                {{ $metodo_pago === 'deuna' ? 'Subir QR o comprobante Deuna' : 'Subir comprobante' }} (JPG, PNG - Max. 10MB)
                            </label>

                            <div class="relative">
                                <input type="file" wire:model="comprobante" id="comprobante" class="hidden">
                                <label for="comprobante" class="cursor-pointer flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 transition-all duration-300 hover:border-[#003366] group">
                                    @if ($comprobante)
                                        <div class="flex flex-col items-center px-4 text-center">
                                            <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="mt-2 text-sm text-gray-600 font-medium break-all">{{ $comprobante->getClientOriginalName() }}</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center px-4 text-center">
                                            <svg class="w-12 h-12 text-gray-400 group-hover:text-[#003366] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <p class="mt-2 text-sm text-gray-500">Haz clic para seleccionar la imagen</p>
                                        </div>
                                    @endif
                                </label>
                            </div>

                            @error('comprobante')
                                <span class="text-[#CC0000] text-xs font-semibold mt-1">{{ $message }}</span>
                            @enderror

                            <div wire:loading wire:target="comprobante" class="w-full bg-gray-200 rounded-full h-2 mt-4">
                                <div class="bg-[#003366] h-2 rounded-full animate-pulse w-full"></div>
                                <p class="text-[10px] text-[#003366] mt-1 text-center font-bold">CARGANDO ARCHIVO...</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col gap-4 pt-4 sm:flex-row sm:items-center">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="flex-1 bg-[#003366] text-white py-4 px-6 rounded-xl font-bold uppercase tracking-widest hover:bg-[#002244] transform transition-all active:scale-95 shadow-lg shadow-blue-900/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="guardarPago">Confirmar Pago</span>
                            <span wire:loading wire:target="guardarPago">Procesando...</span>
                        </button>

                        <a href="{{ route('dashboard') }}"
                           class="px-6 py-4 border-2 border-gray-200 text-gray-500 rounded-xl font-bold hover:bg-gray-50 transition-colors uppercase text-sm text-center">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <p class="mt-8 text-center text-gray-400 text-sm italic">
            Al confirmar el pago, aceptas los terminos y condiciones de la Cooperativa Ambato.
        </p>
    </div>
</div>
