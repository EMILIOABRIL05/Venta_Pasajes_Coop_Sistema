<?php

namespace App\Livewire\Chofer;

use App\Livewire\Traits\RequiresRole;
use App\Models\Boleto;
use App\Models\BoletoValidacion;
use App\Models\Pago;
use App\Models\Pasajero;
use App\Models\Venta;
use App\Models\Viaje;
use App\Services\PricingService;
use App\Support\AsientosDisponibles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PanelPrincipal extends Component
{
    use RequiresRole;

    public function boot(PricingService $pricingService): void
    {
        $this->pricingService = $pricingService;
    }

    private PricingService $pricingService;

    public ?Viaje $viajeActual = null;

    public string $estadoActual = 'en_terminal';

    public ?string $mensajeAbordaje = null;

    public ?string $tipoMensajeAbordaje = null;

    public ?string $mensajeExpress = null;

    public ?string $tipoMensajeExpress = null;

    public function mount(): void
    {
        $this->requireRole(['chofer', 'admin']);
        $this->resolverViajeActual();
        $this->sincronizarEstadoUiDesdeViaje();
    }

    protected function resolverViajeActual(): void
    {
        $user = Auth::user();
        $request = request();

        if ($user->hasRole('admin') && $request->filled('viaje_id')) {
            $this->viajeActual = Viaje::query()
                ->with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus'])
                ->find((int) $request->get('viaje_id'));

            return;
        }

        $queryBase = Viaje::query()
            ->with(['frecuencia.ruta.origen', 'frecuencia.ruta.destino', 'bus'])
            ->whereDate('fecha', now()->toDateString())
            ->whereNotIn('estado', ['Finalizada', 'cancelado']);

        if ($user->hasRole('chofer')) {
            $this->viajeActual = (clone $queryBase)
                ->where('chofer_user_id', $user->id)
                ->orderByDesc('id')
                ->first();

            return;
        }

        $this->viajeActual = $queryBase->orderByDesc('id')->first();
    }

    protected function sincronizarEstadoUiDesdeViaje(): void
    {
        if (! $this->viajeActual) {
            return;
        }

        $map = [
            'En Terminal' => 'en_terminal',
            'En Curso' => 'en_curso',
            'Finalizada' => 'finalizada',
            'programado' => 'en_terminal',
        ];

        $this->estadoActual = $map[$this->viajeActual->estado] ?? 'en_terminal';
    }

    public function cambiarEstado(string $estado): void
    {
        if (! $this->viajeActual) {
            $this->estadoActual = $estado;

            return;
        }

        $mapUiToDb = [
            'en_terminal' => 'En Terminal',
            'en_curso' => 'En Curso',
            'finalizada' => 'Finalizada',
        ];

        $nuevoEstado = $mapUiToDb[$estado] ?? null;
        if (! $nuevoEstado) {
            return;
        }

        $estadosPermitidos = ['En Terminal', 'En Curso', 'Finalizada', 'programado', 'cancelado'];
        if (! in_array($nuevoEstado, $estadosPermitidos, true)) {
            return;
        }

        $viaje = Viaje::find($this->viajeActual->id);
        if (! $viaje) {
            return;
        }

        if (in_array($viaje->estado, ['Finalizada', 'cancelado'], true)) {
            return;
        }

        if ($viaje->estado === 'En Curso' && $nuevoEstado !== 'Finalizada') {
            return;
        }

        if ($viaje->estado === 'En Terminal' && $nuevoEstado === 'Finalizada') {
            return;
        }

        DB::transaction(function () use ($viaje, $nuevoEstado) {
            $viaje->update(['estado' => $nuevoEstado]);

            if ($nuevoEstado === 'En Curso') {
                $viaje->bus()->update(['estado' => 'en_ruta']);
            } elseif (in_array($nuevoEstado, ['Finalizada', 'cancelado'], true)) {
                $viaje->bus()->update(['estado' => 'disponible']);
            }
        });

        $this->viajeActual->refresh();
        $this->estadoActual = $estado;
    }

    public function validarAbordaje(string $uuid): void
    {
        $this->mensajeAbordaje = null;
        $this->tipoMensajeAbordaje = null;

        abort_unless(Auth::user()->can('scan_qr'), 403);

        if (! $this->viajeActual) {
            $this->tipoMensajeAbordaje = 'error';
            $this->mensajeAbordaje = 'No tienes una hoja de ruta asignada para hoy.';

            return;
        }

        $uuid = strtolower(trim($uuid));

        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid)) {
            $this->tipoMensajeAbordaje = 'error';
            $this->mensajeAbordaje = 'El código escaneado no es un UUID de boleto válido.';

            return;
        }

        try {
            DB::transaction(function () use ($uuid) {
                $viajeId = $this->viajeActual->id;
                $viaje = Viaje::query()->whereKey($viajeId)->lockForUpdate()->firstOrFail();

                $boleto = Boleto::query()->whereKey($uuid)->lockForUpdate()->first();

                if (! $boleto) {
                    throw ValidationException::withMessages([
                        'uuid' => 'Boleto no encontrado.',
                    ]);
                }

                if ((int) $boleto->frecuencia_id !== (int) $viaje->frecuencia_id) {
                    throw ValidationException::withMessages([
                        'uuid' => 'Este boleto no corresponde a la frecuencia de tu hoja de ruta actual.',
                    ]);
                }

                $existente = BoletoValidacion::query()
                    ->where('boleto_id', $boleto->id)
                    ->lockForUpdate()
                    ->first();

                if ($existente && $existente->observaciones === 'A bordo') {
                    throw ValidationException::withMessages([
                        'uuid' => 'Este pasajero ya figura como a bordo.',
                    ]);
                }

                if ($existente) {
                    $existente->update([
                        'usuario_id' => Auth::id(),
                        'fecha_validacion' => now(),
                        'estado' => 'validado',
                        'observaciones' => 'A bordo',
                    ]);

                    return;
                }

                BoletoValidacion::create([
                    'boleto_id' => $boleto->id,
                    'usuario_id' => Auth::id(),
                    'fecha_validacion' => now(),
                    'estado' => 'validado',
                    'observaciones' => 'A bordo',
                ]);
            });

            $this->tipoMensajeAbordaje = 'success';
            $this->mensajeAbordaje = 'Pasajero registrado como a bordo.';
        } catch (ValidationException $e) {
            $this->tipoMensajeAbordaje = 'error';
            $this->mensajeAbordaje = collect($e->errors())->flatten()->first();
        }
    }

    public function ventaExpress(): void
    {
        $this->mensajeExpress = null;
        $this->tipoMensajeExpress = null;

        abort_unless(Auth::user()->can('sell_onboard'), 403);

        if (! $this->viajeActual) {
            $this->tipoMensajeExpress = 'error';
            $this->mensajeExpress = 'No hay hoja de ruta activa.';

            return;
        }

        if (! in_array($this->viajeActual->estado, ['En Terminal', 'En Curso'], true)) {
            $this->tipoMensajeExpress = 'error';
            $this->mensajeExpress = 'La venta express solo está permitida con el viaje en terminal o en curso.';

            return;
        }

        try {
            DB::transaction(function () {
                $viaje = Viaje::query()
                    ->whereKey($this->viajeActual->id)
                    ->lockForUpdate()
                    ->with(['bus.asientos', 'frecuencia.ruta'])
                    ->firstOrFail();

                $asientoLibre = AsientosDisponibles::primerDisponible($viaje, true);

                if ($asientoLibre === null) {
                    throw ValidationException::withMessages([
                        'asiento' => 'No quedan asientos libres en este bus.',
                    ]);
                }

                $precioBase = (float) ($viaje->frecuencia->ruta->precio_base ?? 0);
                if ($precioBase <= 0) {
                    throw ValidationException::withMessages([
                        'precio' => 'La ruta no tiene precio base configurado.',
                    ]);
                }

                $categoriaAsiento = $viaje->bus?->asientos
                    ->firstWhere('numero', (int) $asientoLibre)
                    ?->categoria ?? 'estandar';

                $recargo = $categoriaAsiento === 'vip'
                    ? round($precioBase * 0.5, 2)
                    : 0.0;

                $precioFinal = $this->pricingService->calcularPrecioFinal($precioBase, $recargo);

                $pasajero = Pasajero::query()->firstOrCreate(
                    ['cedula' => '9000000001'],
                    ['nombre_completo' => 'Venta Express (Bus)', 'edad' => 30]
                );

                $venta = Venta::create([
                    'user_id' => Auth::id(),
                    'total' => $precioFinal,
                    'estado' => 'Pagada',
                ]);

                Boleto::create([
                    'venta_id' => $venta->id,
                    'pasajero_id' => $pasajero->id,
                    'frecuencia_id' => $viaje->frecuencia_id,
                    'numero_asiento' => $asientoLibre,
                    'categoria_asiento' => $categoriaAsiento,
                    'precio_final' => $precioFinal,
                ]);

                Pago::create([
                    'venta_id' => $venta->id,
                    'monto' => $precioFinal,
                    'fecha' => now(),
                    'metodo_pago' => 'venta_express',
                    'observaciones' => "Venta a bordo registrada desde panel chofer. Asiento {$asientoLibre} {$categoriaAsiento}.",
                ]);
            });

            $this->viajeActual->refresh();

            $this->tipoMensajeExpress = 'success';
            $this->mensajeExpress = 'Venta express registrada con precio por categoría de asiento.';
        } catch (ValidationException $e) {
            $this->tipoMensajeExpress = 'error';
            $this->mensajeExpress = collect($e->errors())->flatten()->first();
        }
    }

    public function render()
    {
        $pasajerosAbordo = 0;
        $capacidadTotal = 40;
        $resumenAsientos = null;

        if ($this->viajeActual) {
            $capacidadTotal = (int) ($this->viajeActual->bus->numero_asientos ?? 40);
            $pasajerosAbordo = BoletoValidacion::query()
                ->where('observaciones', 'A bordo')
                ->whereHas('boleto', function ($q) {
                    $q->where('frecuencia_id', $this->viajeActual->frecuencia_id);
                })
                ->count();

            $resumenAsientos = AsientosDisponibles::resumen($this->viajeActual);
        }

        return view('livewire.chofer.panel-principal', [
            'pasajerosAbordo' => $pasajerosAbordo,
            'capacidadTotal' => $capacidadTotal,
            'resumenAsientos' => $resumenAsientos,
        ])->layout('layouts.app');
    }
}
