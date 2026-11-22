<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\TipoMovimientoFidelizacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientosFidelizacion extends Model
{
    protected $table = 'comercial.movimientos_fidelizacion';

    protected $primaryKey = 'id_movimiento';

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'id_venta',
        'tipo_movimiento',
        'puntos',
        'descripcion',
        'fecha_movimiento',
    ];

    protected $casts = [
        'id_cliente' => 'int',
        'id_venta' => 'int',
        'tipo_movimiento' => TipoMovimientoFidelizacion::class,
        'puntos' => 'int',
        'fecha_movimiento' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Ventas::class, 'id_venta', 'id_venta');
    }
}
