<?php

namespace App\Livewire\Web;

use App\Models\Pago;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class PagoWeb extends Component
{
    use WithFileUploads;

    public $venta;
    public $comprobante;
    public $metodo_pago = 'paypal';
    public $referencia = '';
    public $codigo_deuna = '';
    public $numero_tarjeta = '';
    public $titular_tarjeta = '';
    public $expiracion_tarjeta = '';
    public $codigo_seguridad = '';
    public $banco_destino = 'Banco del Austro - Cuenta Corriente: 123456789';
    public $mensaje_exito = '';

    public function mount($ventaId): void
    {
        $this->venta = Venta::where('cliente_id', Auth::id())
            ->findOrFail($ventaId);

        if ($this->venta->estado !== 'Pendiente') {
            session()->flash('info', 'Esta venta ya ha sido procesada o esta en validacion.');
        }
    }

    protected function rules(): array
    {
        $rules = [
            'metodo_pago' => ['required', Rule::in(array_keys($this->metodosDisponibles()))],
            'referencia' => ['nullable', 'string', 'max:120'],
            'comprobante' => ['nullable', 'image', 'max:10240'],
        ];

        if ($this->metodo_pago === 'paypal') {
            $rules['referencia'] = ['required', 'string', 'min:6', 'max:120'];
        }

        if ($this->metodo_pago === 'tarjeta') {
            $rules['titular_tarjeta'] = ['required', 'string', 'min:3', 'max:120'];
            $rules['numero_tarjeta'] = ['required', 'string', 'min:13', 'max:23'];
            $rules['expiracion_tarjeta'] = ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/'];
            $rules['codigo_seguridad'] = ['required', 'digits_between:3,4'];
        }

        if ($this->metodo_pago === 'deuna') {
            $rules['codigo_deuna'] = ['nullable', 'string', 'min:4', 'max:120'];
            $rules['comprobante'] = ['required_without:codigo_deuna', 'nullable', 'image', 'max:10240'];
        }

        if ($this->metodo_pago === 'transferencia') {
            $rules['comprobante'] = ['required', 'image', 'max:10240'];
            $rules['referencia'] = ['nullable', 'string', 'max:120'];
        }

        return $rules;
    }

    public function guardarPago()
    {
        $this->validate();
        $this->validarTarjetaSiAplica();

        try {
            DB::transaction(function () {
                $venta = Venta::where('cliente_id', Auth::id())
                    ->lockForUpdate()
                    ->findOrFail($this->venta->id);

                if ($venta->estado !== 'Pendiente') {
                    throw ValidationException::withMessages([
                        'metodo_pago' => 'Esta venta ya fue procesada.',
                    ]);
                }

                $ruta = $this->guardarComprobanteSiExiste($venta->id);
                $referencia = $this->referenciaPago();
                $estadoVenta = $this->estadoVentaDespuesDelPago();
                $estadoPago = $this->estadoPagoDespuesDelPago();

                $venta->update([
                    'comprobante' => $ruta,
                    'estado' => $estadoVenta,
                ]);

                Pago::create([
                    'venta_id' => $venta->id,
                    'monto' => $venta->total,
                    'fecha' => now(),
                    'metodo_pago' => $this->metodo_pago,
                    'referencia' => $referencia,
                    'observaciones' => $this->observacionesPago(),
                    'estado' => $estadoPago,
                    'codigo_autorizacion' => in_array($this->metodo_pago, ['paypal', 'tarjeta'], true) ? $referencia : null,
                    'validado_at' => $estadoPago === 'confirmado' ? now() : null,
                ]);

                $this->venta = $venta->fresh();
            });

            $this->mensaje_exito = match ($this->metodo_pago) {
                'paypal', 'tarjeta' => 'Pago confirmado. Tu venta ya esta pagada.',
                'deuna' => 'Pago Deuna registrado. Nuestro equipo validara el codigo o QR.',
                default => 'Comprobante subido con exito. Su pago esta siendo validado por nuestro equipo.',
            };

            session()->flash('success', $this->mensaje_exito);

            return redirect()->route('mis-viajes');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrio un error al procesar el pago: ' . $e->getMessage());
        }
    }

    public function updatedMetodoPago(): void
    {
        $this->resetValidation();
        $this->reset([
            'comprobante',
            'referencia',
            'codigo_deuna',
            'numero_tarjeta',
            'titular_tarjeta',
            'expiracion_tarjeta',
            'codigo_seguridad',
        ]);
    }

    private function metodosDisponibles(): array
    {
        return [
            'paypal' => 'PayPal',
            'tarjeta' => 'Tarjeta',
            'deuna' => 'Deuna',
            'transferencia' => 'Transferencia Bancaria',
        ];
    }

    private function guardarComprobanteSiExiste(int $ventaId): ?string
    {
        if (! $this->comprobante) {
            return null;
        }

        $nombreArchivo = 'comprobante_' . $ventaId . '_' . time() . '.' . $this->comprobante->extension();

        return $this->comprobante->storeAs('comprobantes', $nombreArchivo, 'public');
    }

    private function referenciaPago(): ?string
    {
        if ($this->metodo_pago === 'tarjeta') {
            $ultimosDigitos = substr(preg_replace('/\D/', '', $this->numero_tarjeta), -4);

            return 'TARJETA-' . $ultimosDigitos . '-' . now()->format('YmdHis');
        }

        if ($this->metodo_pago === 'deuna') {
            return $this->codigo_deuna ?: $this->referencia;
        }

        return $this->referencia ?: null;
    }

    private function estadoVentaDespuesDelPago(): string
    {
        return in_array($this->metodo_pago, ['paypal', 'tarjeta'], true)
            ? 'Pagada'
            : 'Pendiente de Validación';
    }

    private function estadoPagoDespuesDelPago(): string
    {
        return in_array($this->metodo_pago, ['paypal', 'tarjeta'], true)
            ? 'confirmado'
            : 'pendiente_validacion';
    }

    private function observacionesPago(): string
    {
        return match ($this->metodo_pago) {
            'paypal' => 'Confirmacion externa PayPal registrada por referencia de transaccion.',
            'tarjeta' => 'Autorizacion de tarjeta registrada. No se almacena numero completo ni CVV.',
            'deuna' => 'Pago Deuna pendiente de verificacion interna por codigo o QR.',
            default => 'Transferencia bancaria pendiente de validacion interna.',
        };
    }

    private function validarTarjetaSiAplica(): void
    {
        if ($this->metodo_pago !== 'tarjeta') {
            return;
        }

        $numero = preg_replace('/\D/', '', $this->numero_tarjeta);

        if (! $this->numeroTarjetaValido($numero)) {
            throw ValidationException::withMessages([
                'numero_tarjeta' => 'El numero de tarjeta no es valido.',
            ]);
        }

        [$mes, $anio] = explode('/', $this->expiracion_tarjeta);
        $fechaExpiracion = Carbon::createFromDate(2000 + (int) $anio, (int) $mes, 1)->endOfMonth();

        if ($fechaExpiracion->isPast()) {
            throw ValidationException::withMessages([
                'expiracion_tarjeta' => 'La tarjeta esta vencida.',
            ]);
        }
    }

    private function numeroTarjetaValido(string $numero): bool
    {
        if ($numero === '' || strlen($numero) < 13) {
            return false;
        }

        $suma = 0;
        $alternar = false;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];

            if ($alternar) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $suma += $digito;
            $alternar = ! $alternar;
        }

        return $suma % 10 === 0;
    }

    public function render()
    {
        return view('livewire.web.pago-web', [
            'metodosPago' => $this->metodosDisponibles(),
        ]);
    }
}
