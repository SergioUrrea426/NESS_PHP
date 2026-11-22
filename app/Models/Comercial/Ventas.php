<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\EstadoVenta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ventas extends Model
{
    protected $table = 'comercial.ventas';

    protected $primaryKey = 'id_venta';

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'id_cliente',
        'id_oportunidad',
        'id_vendedor',
        'estado',
        'fecha_confirmacion',
        'fecha_vencimiento',
        'condiciones_pago',
        'subtotal',
        'descuento_total',
        'impuesto_total',
        'total',
        'prefijo',
        'numero_factura',
        'fecha_factura',
        'cufe',
        'observaciones',
    ];

    protected $casts = [
        'id_cliente' => 'int',
        'id_oportunidad' => 'int',
        'id_vendedor' => 'int',
        'estado' => EstadoVenta::class,
        'fecha_confirmacion' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'impuesto_total' => 'decimal:2',
        'total' => 'decimal:2',
        'numero_factura' => 'int',
        'fecha_factura' => 'datetime',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }

    public function oportunidad(): BelongsTo
    {
        return $this->belongsTo(Oportunidades::class, 'id_oportunidad', 'id_oportunidad');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta', 'id_venta');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pagos::class, 'id_venta', 'id_venta');
    }

    public function comisiones(): HasMany
    {
        return $this->hasMany(Comisiones::class, 'id_venta', 'id_venta');
    }

    public function movimientosFidelizacion(): HasMany
    {
        return $this->hasMany(MovimientosFidelizacion::class, 'id_venta', 'id_venta');
    }

    public function casosPostventa(): HasMany
    {
        return $this->hasMany(CasosPostventa::class, 'id_venta', 'id_venta');
    }
}
