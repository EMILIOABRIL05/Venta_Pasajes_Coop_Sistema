<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCambio;
use App\Models\ReporteTecnicoCambio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SolicitudCambioController extends Controller
{
    public function create()
    {
        return view('solicitudes-cambio.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_solicitud'   => 'required|string|max:255',
            'origen_solicitud' => 'nullable|string|max:255',
            'descripcion'      => 'required|string',
            'prioridad'        => 'required|in:Baja,Media,Alta',
            'estado_pipeline'  => 'nullable|in:Propuesto,En Desarrollo,Validado en Sandbox,Mergado,Desplegado,Rechazado',
            'modulo_afectado'  => 'nullable|string|max:255',
            'github_issue_id'  => 'nullable|string|max:255',
            'git_branch'       => 'nullable|string|max:255',
            'commit_hash'      => 'nullable|string|max:255',
            'sandbox_status'   => 'nullable|string',
            'sandbox_modules'  => 'nullable|array',
            'risk_analysis'    => 'nullable|string',
            'rollback_plan'    => 'nullable|string',
        ]);

        $user = Auth::user();
        $isDeveloper = $user->hasRole('developer') || $user->hasRole('admin');

        return DB::transaction(function () use ($validated, $user, $isDeveloper) {
            $solicitud = SolicitudCambio::create([
                'user_id'         => $user->id,
                'tipo_solicitud'  => $validated['tipo_solicitud'],
                'origen_solicitud' => $isDeveloper ? ($validated['origen_solicitud'] ?? null) : null,
                'descripcion'     => $validated['descripcion'],
                'prioridad'       => $validated['prioridad'],
                'estado_pipeline' => $isDeveloper
                    ? ($validated['estado_pipeline'] ?? 'Propuesto')
                    : 'Propuesto',
            ]);

            if ($isDeveloper) {
                $sandboxModules = $validated['sandbox_modules'] ?? [];
                $sandboxStatus = $validated['sandbox_status'] ?? 'No probado';

                if (!empty($sandboxModules)) {
                    $sandboxStatus = 'Probados: ' . implode(', ', $sandboxModules);
                }

                ReporteTecnicoCambio::create([
                    'solicitud_cambio_id' => $solicitud->id,
                    'developer_id'        => $user->id,
                    'modulo_afectado'     => $validated['modulo_afectado'] ?? null,
                    'github_issue_id'     => $validated['github_issue_id'] ?? null,
                    'git_branch'          => $validated['git_branch'] ?? null,
                    'commit_hash'         => $validated['commit_hash'] ?? null,
                    'sandbox_status'      => $sandboxStatus,
                    'risk_analysis'       => $validated['risk_analysis'] ?? null,
                    'rollback_plan'       => $validated['rollback_plan'] ?? null,
                ]);
            }

            return redirect()
                ->route('solicitudes-cambio.create')
                ->with('success', 'Solicitud de cambio enviada correctamente.');
        });
    }

    public function index()
    {
        $user = Auth::user();
        $isDeveloper = $user->hasRole('developer') || $user->hasRole('admin');

        $query = SolicitudCambio::with(['usuario', 'evaluador', 'reporteTecnico'])
            ->latest();

        if (! $isDeveloper) {
            $query->where('user_id', $user->id);
        }

        $solicitudes = $query->paginate(15);

        return view('solicitudes-cambio.index', compact('solicitudes', 'isDeveloper'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $isDeveloper = $user->hasRole('developer') || $user->hasRole('admin');

        $solicitud = SolicitudCambio::with(['usuario', 'evaluador', 'reporteTecnico'])
            ->findOrFail($id);

        if (! $isDeveloper && $solicitud->user_id !== $user->id) {
            abort(403);
        }

        return view('solicitudes-cambio.show', compact('solicitud', 'isDeveloper'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $isDeveloper = $user->hasRole('developer') || $user->hasRole('admin');

        if (! $isDeveloper) {
            abort(403);
        }

        $validated = $request->validate([
            'estado_pipeline'  => 'required|in:Propuesto,En Desarrollo,Validado en Sandbox,Mergado,Desplegado,Rechazado',
            'motivo_rechazo'   => 'nullable|string|required_if:estado_pipeline,Rechazado',
        ]);

        $solicitud = SolicitudCambio::findOrFail($id);

        return DB::transaction(function () use ($solicitud, $validated, $user) {
            $solicitud->update([
                'estado_pipeline' => $validated['estado_pipeline'],
                'evaluador_id'    => $user->id,
                'motivo_rechazo'  => $validated['estado_pipeline'] === 'Rechazado'
                    ? $validated['motivo_rechazo']
                    : null,
            ]);

            return redirect()
                ->route('solicitudes-cambio.show', $solicitud->id)
                ->with('success', 'Estado actualizado a: ' . $validated['estado_pipeline']);
        });
    }

    public function edit($id)
    {
        $user = Auth::user();
        $isDeveloper = $user->hasRole('developer') || $user->hasRole('admin');

        if (! $isDeveloper) {
            abort(403);
        }

        $solicitud = SolicitudCambio::with(['usuario', 'evaluador', 'reporteTecnico'])
            ->findOrFail($id);

        return view('solicitudes-cambio.edit', compact('solicitud', 'isDeveloper'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isDeveloper = $user->hasRole('developer') || $user->hasRole('admin');

        if (! $isDeveloper) {
            abort(403);
        }

        $solicitud = SolicitudCambio::with('reporteTecnico')->findOrFail($id);

        $validated = $request->validate([
            'tipo_solicitud'   => 'required|string|max:255',
            'origen_solicitud' => 'nullable|string|max:255',
            'descripcion'      => 'required|string',
            'prioridad'        => 'required|in:Baja,Media,Alta',
            'estado_pipeline'  => 'nullable|in:Propuesto,En Desarrollo,Validado en Sandbox,Mergado,Desplegado,Rechazado',
            'modulo_afectado'  => 'nullable|string|max:255',
            'github_issue_id'  => 'nullable|string|max:255',
            'git_branch'       => 'nullable|string|max:255',
            'commit_hash'      => 'nullable|string|max:255',
            'sandbox_status'   => 'nullable|string',
            'sandbox_modules'  => 'nullable|array',
            'risk_analysis'    => 'nullable|string',
            'rollback_plan'    => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated, $solicitud, $user) {
            $solicitud->update([
                'tipo_solicitud'   => $validated['tipo_solicitud'],
                'origen_solicitud' => $validated['origen_solicitud'] ?? $solicitud->origen_solicitud,
                'descripcion'      => $validated['descripcion'],
                'prioridad'        => $validated['prioridad'],
                'estado_pipeline'  => $validated['estado_pipeline'] ?? $solicitud->estado_pipeline,
                'evaluador_id'     => $user->id,
            ]);

            $sandboxModules = $validated['sandbox_modules'] ?? [];
            $sandboxStatus = $validated['sandbox_status'] ?? 'No probado';

            if (!empty($sandboxModules)) {
                $sandboxStatus = 'Probados: ' . implode(', ', $sandboxModules);
            }

            if ($solicitud->reporteTecnico) {
                $solicitud->reporteTecnico->update([
                    'modulo_afectado' => $validated['modulo_afectado'] ?? $solicitud->reporteTecnico->modulo_afectado,
                    'github_issue_id' => $validated['github_issue_id'] ?? $solicitud->reporteTecnico->github_issue_id,
                    'git_branch'      => $validated['git_branch'] ?? $solicitud->reporteTecnico->git_branch,
                    'commit_hash'     => $validated['commit_hash'] ?? $solicitud->reporteTecnico->commit_hash,
                    'sandbox_status'  => $sandboxStatus !== 'No probado' ? $sandboxStatus : $solicitud->reporteTecnico->sandbox_status,
                    'risk_analysis'   => $validated['risk_analysis'] ?? $solicitud->reporteTecnico->risk_analysis,
                    'rollback_plan'   => $validated['rollback_plan'] ?? $solicitud->reporteTecnico->rollback_plan,
                ]);
            } else {
                ReporteTecnicoCambio::create([
                    'solicitud_cambio_id' => $solicitud->id,
                    'developer_id'        => $user->id,
                    'modulo_afectado'     => $validated['modulo_afectado'] ?? null,
                    'github_issue_id'     => $validated['github_issue_id'] ?? null,
                    'git_branch'          => $validated['git_branch'] ?? null,
                    'commit_hash'         => $validated['commit_hash'] ?? null,
                    'sandbox_status'      => $sandboxStatus,
                    'risk_analysis'       => $validated['risk_analysis'] ?? null,
                    'rollback_plan'       => $validated['rollback_plan'] ?? null,
                ]);
            }

            return redirect()
                ->route('solicitudes-cambio.show', $solicitud->id)
                ->with('success', 'Solicitud actualizada correctamente.');
        });
    }

    public function misSolicitudes()
    {
        $solicitudes = SolicitudCambio::with(['evaluador', 'reporteTecnico'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('solicitudes.mis_solicitudes', compact('solicitudes'));
    }
}
