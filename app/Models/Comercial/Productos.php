<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\NaturalezaProducto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Productos extends Model
{
    protected $table = 'comercial.productos';

    protected $primaryKey = 'id_producto';

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'id_categoria',
        'id_marca',
        'id_unidad_medida',
        'naturaleza',
        'precio_base',
        'estado',
    ];

    protected $casts = [
        'id_categoria' => 'int',
        'id_marca' => 'int',
        'id_unidad_medida' => 'int',
        'naturaleza' => NaturalezaProducto::class,
        'precio_base' => 'decimal:2',
        'estado' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto', 'id_producto');
    }

    public function casosPostventa(): HasMany
    {
        return $this->hasMany(CasosPostventa::class, 'id_producto', 'id_producto');
    }
}
