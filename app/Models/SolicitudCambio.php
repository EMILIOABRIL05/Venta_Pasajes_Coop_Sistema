<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudCambio extends Model
{
    protected $table = 'solicitudes_cambio';

    protected $fillable = [
        'user_id',
        'evaluador_id',
        'tipo_solicitud',
        'origen_solicitud',
        'descripcion',
        'prioridad',
        'estado_pipeline',
        'motivo_rechazo',
    ];

    protected $casts = [
        'prioridad'       => 'string',
        'estado_pipeline' => 'string',
        'origen_solicitud' => 'string',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }

    public function reporteTecnico(): HasOne
    {
        return $this->hasOne(ReporteTecnicoCambio::class, 'solicitud_cambio_id');
    }
}
