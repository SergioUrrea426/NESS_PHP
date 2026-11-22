<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\EtapaOportunidad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oportunidades extends Model
{
    protected $table = 'comercial.oportunidades';

    protected $primaryKey = 'id_oportunidad';

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_cliente',
        'id_vendedor',
        'etapa',
        'origen',
        'valor_estimado',
        'fecha_cierre_estimada',
        'fecha_cierre',
        'motivo_perdida',
        'observaciones',
    ];

    protected $casts = [
        'id_cliente' => 'int',
        'id_vendedor' => 'int',
        'etapa' => EtapaOportunidad::class,
        'valor_estimado' => 'decimal:2',
        'fecha_cierre_estimada' => 'date',
        'fecha_cierre' => 'date',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Ventas::class, 'id_oportunidad', 'id_oportunidad');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividades::class, 'id_oportunidad', 'id_oportunidad');
    }
}
