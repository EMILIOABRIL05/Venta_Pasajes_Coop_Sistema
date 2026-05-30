<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteTecnicoCambio extends Model
{
    protected $table = 'reportes_tecnicos_cambio';

    protected $fillable = [
        'solicitud_cambio_id',
        'modulo_afectado',
        'developer_id',
        'github_issue_id',
        'git_branch',
        'commit_hash',
        'sandbox_status',
        'risk_analysis',
        'rollback_plan',
    ];

    public function solicitudCambio(): BelongsTo
    {
        return $this->belongsTo(SolicitudCambio::class, 'solicitud_cambio_id');
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'developer_id');
    }
}
