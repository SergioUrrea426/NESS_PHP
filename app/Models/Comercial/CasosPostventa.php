<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\TipoCaso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasosPostventa extends Model
{
    protected $table = 'comercial.casos_postventa';

    protected $primaryKey = 'id_caso';

    public $timestamps = false;

    protected $fillable = [
        'tipo_caso',
        'id_cliente',
        'id_venta',
        'id_producto',
        'id_responsable',
        'asunto',
        'descripcion',
        'prioridad',
        'estado',
        'resultado',
        'valor_reembolso',
        'fecha_reporte',
        'fecha_cierre',
        'calificacion',
    ];

    protected $casts = [
        'tipo_caso' => TipoCaso::class,
        'id_cliente' => 'int',
        'id_venta' => 'int',
        'id_producto' => 'int',
        'id_responsable' => 'int',
        'valor_reembolso' => 'decimal:2',
        'fecha_reporte' => 'datetime',
        'fecha_cierre' => 'datetime',
        'calificacion' => 'int',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Ventas::class, 'id_venta', 'id_venta');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'id_producto', 'id_producto');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividades::class, 'id_caso', 'id_caso');
    }
}
