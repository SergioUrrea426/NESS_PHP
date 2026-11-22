<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagos extends Model
{
    protected $table = 'comercial.pagos';

    protected $primaryKey = 'id_pago';

    public $timestamps = false;

    protected $fillable = [
        'id_venta',
        'id_metodo_pago',
        'fecha_pago',
        'valor',
        'referencia',
        'estado',
    ];

    protected $casts = [
        'id_venta' => 'int',
        'id_metodo_pago' => 'int',
        'fecha_pago' => 'datetime',
        'valor' => 'decimal:2',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Ventas::class, 'id_venta', 'id_venta');
    }
}
