<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Traits\RequiresRole;
use App\Models\Asiento;
use App\Models\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class BusesCrud extends Component
{
    use RequiresRole;
    use WithFileUploads;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public ?int $busId = null;

    public string $placa = '';

    public string $marca_chasis = '';

    public string $carroceria = '';

    public string $anio = '';

    public string $filas = '10';

    // Pasillo fijo: interprovincial 2+2
    public bool $pasillo = true;

    public string $estado = 'disponible';

    public array $asientosCategorias = [];

    public ?string $fotoActual = null;

    public TemporaryUploadedFile|string|null $foto = null;

    public function mount(): void
    {
        $this->requireRole('admin|oficinista');
        $this->resetForm();
        $this->cargarCategoriasAsientosPorDefecto();
    }

    protected function rules(): array
    {
        return [
            'placa' => [
                'required',
                'string',
                'max:20',
                Rule::unique('buses', 'placa')->ignore($this->busId),
            ],
            'marca_chasis' => ['required', 'string', 'max:120'],
            'carroceria' => ['required', 'string', 'max:120'],
            'anio' => ['required', 'integer', 'min:1900', 'max:' . now()->year],
            'filas' => ['required', 'integer', 'min:1', 'max:60'],
            'estado' => ['required', Rule::in(['disponible', 'en_ruta', 'mantenimiento'])],
            'foto' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $payload = $this->buildPayload($data);

        DB::transaction(function () use ($payload) {
            if ($this->busId) {
                $bus = Bus::query()->findOrFail($this->busId);

                if ($this->foto instanceof TemporaryUploadedFile) {
                    $this->deletePhotoIfExists($bus->foto);
                    $payload['foto'] = $this->foto->store('buses', 'public');
                } else {
                    $payload['foto'] = $bus->foto;
                }

                $bus->update($payload);
                $this->sincronizarAsientos($bus);
                session()->flash('message', 'Bus actualizado correctamente.');
            } else {
                if ($this->foto instanceof TemporaryUploadedFile) {
                    $payload['foto'] = $this->foto->store('buses', 'public');
                }

                $bus = Bus::create($payload);
                $this->sincronizarAsientos($bus);
                session()->flash('message', 'Bus creado correctamente.');
            }
        });

        $this->resetForm();
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $bus = Bus::query()->findOrFail($id);

        $this->busId = $bus->id;
        $this->placa = $bus->placa;
        $this->marca_chasis = $bus->marca_chasis;
        $this->carroceria = $bus->carroceria;
        $this->anio = (string) $bus->anio;
        $this->filas = (string) ($bus->mapa_asientos['filas'] ?? Bus::estructuraAsientosBase()['filas']);
        // pasillo es fijo en true; no editable en UI
        $this->pasillo = true;
        $this->estado = $bus->estado;
        $this->fotoActual = $bus->foto;
        $this->foto = null;
        $this->cargarCategoriasAsientosDesdeBus($bus);

    }

    public function delete(int $id): void
    {
        // Solo admin puede eliminar buses
        if (! auth()->user()?->hasRole('admin')) {
            session()->flash('error', 'No tienes permisos para eliminar buses.');
            return;
        }

        $bus = Bus::query()->findOrFail($id);
        $this->deletePhotoIfExists($bus->foto);
        $bus->delete();

        session()->flash('message', 'Bus eliminado correctamente.');
        $this->resetForm();
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $baseSeatMap = Bus::estructuraAsientosBase();

        $this->reset([
            'busId',
            'placa',
            'marca_chasis',
            'carroceria',
            'anio',
            'fotoActual',
            'foto',
        ]);

        $this->filas = (string) $baseSeatMap['filas'];
        $this->pasillo = (bool) $baseSeatMap['pasillo'];
        $this->estado = 'disponible';
        $this->cargarCategoriasAsientosPorDefecto();
    }

    public function updatedFilas(): void
    {
        $this->cargarCategoriasAsientosPorDefecto();
    }

    private function buildPayload(array $data): array
    {
        $filas = (int) $data['filas'];
        // Pasillo central forzado a true (estándar interprovincial)
        $pasillo = true;

        return [
            'placa' => strtoupper(trim($data['placa'])),
            'marca_chasis' => trim($data['marca_chasis']),
            'carroceria' => trim($data['carroceria']),
            'anio' => (int) $data['anio'],
            'numero_asientos' => Bus::calcularCapacidad($filas, $pasillo),
            'mapa_asientos' => Bus::generarEstructuraAsientos($filas, $pasillo),
            'estado' => $data['estado'],
        ];
    }

    private function cargarCategoriasAsientosPorDefecto(): void
    {
        $total = Bus::calcularCapacidad((int) $this->filas, true);
        $this->asientosCategorias = [];

        for ($numero = 1; $numero <= $total; $numero++) {
            $this->asientosCategorias[(string) $numero] = 'estandar';
        }

        if ($this->busId) {
            $bus = Bus::query()->find($this->busId);

            if ($bus) {
                $this->cargarCategoriasAsientosDesdeBus($bus);
            }
        }
    }

    private function cargarCategoriasAsientosDesdeBus(Bus $bus): void
    {
        $filas = is_array($bus->mapa_asientos) && isset($bus->mapa_asientos['filas'])
            ? (int) $bus->mapa_asientos['filas']
            : (int) $this->filas;

        $total = Bus::calcularCapacidad($filas, true);
        $categorias = array_fill(1, $total, 'estandar');

        foreach ($bus->asientos()->orderBy('numero')->get() as $asiento) {
            $categorias[$asiento->numero] = $asiento->categoria;
        }

        $this->asientosCategorias = [];

        foreach ($categorias as $numero => $categoria) {
            $this->asientosCategorias[(string) $numero] = $categoria;
        }
    }

    public function alternarCategoriaAsiento(int $numero): void
    {
        $clave = (string) $numero;
        $categoriaActual = $this->asientosCategorias[$clave] ?? 'estandar';
        $this->asientosCategorias[$clave] = $categoriaActual === 'vip' ? 'estandar' : 'vip';
    }

    private function sincronizarAsientos(Bus $bus): void
    {
        $filas = is_array($bus->mapa_asientos) && isset($bus->mapa_asientos['filas'])
            ? (int) $bus->mapa_asientos['filas']
            : (int) $this->filas;

        $total = Bus::calcularCapacidad($filas, true);

        Asiento::query()
            ->where('bus_id', $bus->id)
            ->where('numero', '>', $total)
            ->delete();

        for ($numero = 1; $numero <= $total; $numero++) {
            Asiento::updateOrCreate(
                [
                    'bus_id' => $bus->id,
                    'numero' => $numero,
                ],
                [
                    'categoria' => $this->asientosCategorias[(string) $numero] ?? 'estandar',
                ]
            );
        }
    }

    private function deletePhotoIfExists(?string $photoPath): void
    {
        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }
    }

    public function render()
    {
        return view('livewire.catalogos.buses-crud', [
            'buses' => Bus::query()
                ->with('asientos')
                ->latest()
                ->paginate(8),
        ]);
    }
}