<?php

namespace App\Livewire\Catalogos;

use App\Livewire\Traits\RequiresRole;
use App\Models\User;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class CuentasCrud extends Component
{
    use RequiresRole;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $cedula = '';

    public string $telefono = '';

    public string $fecha_nacimiento = '';

    public string $tipo_usuario = 'oficinista';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->requireRole('admin');
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'cedula' => ['required', 'string', 'regex:/^\d{10}$/', Rule::unique('users', 'cedula')->ignore($this->userId)],
            'telefono' => ['required', 'string', 'regex:/^\d{10}$/'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'tipo_usuario' => ['required', Rule::in(['oficinista', 'chofer'])],
            'password' => [$this->userId ? 'nullable' : 'required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'cedula' => trim($data['cedula']),
            'telefono' => trim($data['telefono']),
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'tipo_usuario' => $data['tipo_usuario'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        DB::transaction(function () use ($payload): void {
            Role::firstOrCreate([
                'name' => $payload['tipo_usuario'],
                'guard_name' => 'web',
            ]);

            if ($this->userId) {
                $user = User::query()->findOrFail($this->userId);
                $user->update($payload);
                $user->syncRoles([$payload['tipo_usuario']]);

                session()->flash('message', 'Cuenta actualizada correctamente.');
            } else {
                $user = User::create($payload);
                $user->assignRole($payload['tipo_usuario']);

                session()->flash('message', 'Cuenta creada correctamente.');
            }
        });

        $this->resetForm();
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $user = User::query()->whereIn('tipo_usuario', ['oficinista', 'chofer'])->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->cedula = $user->cedula ?? '';
        $this->telefono = $user->telefono ?? '';
        $this->fecha_nacimiento = optional($user->fecha_nacimiento)->format('Y-m-d') ?? '';
        $this->tipo_usuario = $user->tipo_usuario ?? 'oficinista';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function delete(int $id): void
    {
        $user = User::query()->whereIn('tipo_usuario', ['oficinista', 'chofer'])->findOrFail($id);

        if (auth()->id() === $user->id) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');

            return;
        }

        $user->delete();

        session()->flash('message', 'Cuenta eliminada correctamente.');
        $this->resetForm();
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'userId',
            'name',
            'email',
            'cedula',
            'telefono',
            'fecha_nacimiento',
            'tipo_usuario',
            'password',
            'password_confirmation',
        ]);

        $this->tipo_usuario = 'oficinista';
    }

    public function render()
    {
        return view('livewire.catalogos.cuentas-crud', [
            'usuarios' => User::query()
                ->whereIn('tipo_usuario', ['oficinista', 'chofer'])
                ->latest()
                ->paginate(8),
            'totalOficinistas' => User::query()->where('tipo_usuario', 'oficinista')->count(),
            'totalChoferes' => User::query()->where('tipo_usuario', 'chofer')->count(),
        ]);
    }
}