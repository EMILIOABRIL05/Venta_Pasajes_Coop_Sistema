<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Traits\RequiresRole;
use App\Models\Bus;
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

    public ?string $fotoActual = null;

    public TemporaryUploadedFile|string|null $foto = null;

    public function mount(): void
    {
        $this->requireRole('admin|oficinista');
        $this->resetForm();
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

        if ($this->busId) {
            $bus = Bus::query()->findOrFail($this->busId);

            if ($this->foto instanceof TemporaryUploadedFile) {
                $this->deletePhotoIfExists($bus->foto);
                $payload['foto'] = $this->foto->store('buses', 'public');
            } else {
                $payload['foto'] = $bus->foto;
            }

            $bus->update($payload);
            session()->flash('message', 'Bus actualizado correctamente.');
        } else {
            if ($this->foto instanceof TemporaryUploadedFile) {
                $payload['foto'] = $this->foto->store('buses', 'public');
            }

            Bus::create($payload);
            session()->flash('message', 'Bus creado correctamente.');
        }

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
                ->latest()
                ->paginate(8),
        ]);
    }
}