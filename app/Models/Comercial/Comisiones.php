<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comisiones extends Model
{
    protected $table = 'comercial.comisiones';

    protected $primaryKey = 'id_comision';

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_venta',
        'id_vendedor',
        'porcentaje',
        'valor_comision',
        'estado',
        'fecha_pago',
    ];

    protected $casts = [
        'id_venta' => 'int',
        'id_vendedor' => 'int',
        'porcentaje' => 'decimal:2',
        'valor_comision' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'fecha_pago' => 'datetime',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Ventas::class, 'id_venta', 'id_venta');
    }
}
