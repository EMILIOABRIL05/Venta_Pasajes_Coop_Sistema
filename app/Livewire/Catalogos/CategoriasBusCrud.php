<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Traits\RequiresRole;
use App\Models\CategoriaBus;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriasBusCrud extends Component
{
    use RequiresRole;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public ?int $categoriaId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public function mount(): void
    {
        // admin + oficinista pueden leer; solo admin puede crear/editar/eliminar
        $this->requireRole('admin|oficinista');
    }

    protected function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categorias_bus', 'nombre')->ignore($this->categoriaId),
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(): void
    {
        // Solo admin puede crear/editar categorías
        if (!auth()->user()?->hasRole('admin')) {
            session()->flash('error', 'No tienes permisos para crear o editar categorías.');
            return;
        }

        $data = $this->validate();

        $payload = [
            'nombre' => trim($data['nombre']),
            'descripcion' => $data['descripcion'] !== '' ? trim($data['descripcion']) : null,
        ];

        if ($this->categoriaId) {
            CategoriaBus::query()->findOrFail($this->categoriaId)->update($payload);
            session()->flash('message', 'Categoría actualizada correctamente.');
        } else {
            CategoriaBus::create($payload);
            session()->flash('message', 'Categoría creada correctamente.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function edit(CategoriaBus $categoriaBus): void
    {
        $this->categoriaId = $categoriaBus->id;
        $this->nombre = $categoriaBus->nombre;
        $this->descripcion = $categoriaBus->descripcion ?? '';
    }

    public function delete(CategoriaBus $categoriaBus): void
    {
        // Solo admin puede eliminar categorías
        if (!auth()->user()?->hasRole('admin')) {
            session()->flash('error', 'No tienes permisos para eliminar categorías.');
            return;
        }

        if ($categoriaBus->buses()->exists()) {
            session()->flash('error', 'No se puede eliminar una categoría que ya tiene buses asociados.');

            return;
        }

        $categoriaBus->delete();

        session()->flash('message', 'Categoría eliminada correctamente.');
        $this->resetForm();
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['categoriaId', 'nombre', 'descripcion']);
    }

    public function render()
    {
        return view('livewire.catalogos.categorias-bus-crud', [
            'categorias' => CategoriaBus::query()
                ->withCount('buses')
                ->latest()
                ->paginate(8),
        ]);
    }
}