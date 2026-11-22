<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    protected $table = 'comercial.detalle_venta';

    protected $primaryKey = 'id_detalle';

    public $timestamps = false;

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'porcentaje_descuento',
        'porcentaje_impuesto',
        'total',
    ];

    protected $casts = [
        'id_venta' => 'int',
        'id_producto' => 'int',
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'porcentaje_impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Ventas::class, 'id_venta', 'id_venta');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'id_producto', 'id_producto');
    }
}
