<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\TipoActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Actividades extends Model
{
    protected $table = 'comercial.actividades';

    protected $primaryKey = 'id_actividad';

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    protected $fillable = [
        'tipo_actividad',
        'id_cliente',
        'id_oportunidad',
        'id_caso',
        'asunto',
        'descripcion',
        'fecha_programada',
        'fecha_realizada',
        'estado',
        'resultado',
        'id_usuario_asignado',
    ];

    protected $casts = [
        'tipo_actividad' => TipoActividad::class,
        'id_cliente' => 'int',
        'id_oportunidad' => 'int',
        'id_caso' => 'int',
        'fecha_programada' => 'datetime',
        'fecha_realizada' => 'datetime',
        'id_usuario_asignado' => 'int',
        'fecha_creacion' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }

    public function oportunidad(): BelongsTo
    {
        return $this->belongsTo(Oportunidades::class, 'id_oportunidad', 'id_oportunidad');
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasosPostventa::class, 'id_caso', 'id_caso');
    }
}
