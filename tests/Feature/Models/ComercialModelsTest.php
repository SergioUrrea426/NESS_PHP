<?php

use App\Enums\Comercial\EstadoVenta;
use App\Enums\Comercial\EtapaOportunidad;
use App\Enums\Comercial\NaturalezaProducto;
use App\Enums\Comercial\TipoActividad;
use App\Enums\Comercial\TipoCaso;
use App\Enums\Comercial\TipoMovimientoFidelizacion;
use App\Models\Comercial\Actividades;
use App\Models\Comercial\CasosPostventa;
use App\Models\Comercial\Comisiones;
use App\Models\Comercial\Contactos;
use App\Models\Comercial\DetalleVenta;
use App\Models\Comercial\MovimientosFidelizacion;
use App\Models\Comercial\Oportunidades;
use App\Models\Comercial\Pagos;
use App\Models\Comercial\Productos;
use App\Models\Comercial\Ventas;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('casts commercial enum columns to and from their PHP enums', function () {
    $enumAttributes = [
        [new Actividades, 'tipo_actividad', 'LLAMADA', TipoActividad::Llamada],
        [new CasosPostventa, 'tipo_caso', 'GARANTIA', TipoCaso::Garantia],
        [new MovimientosFidelizacion, 'tipo_movimiento', 'REDENCION', TipoMovimientoFidelizacion::Redencion],
        [new Oportunidades, 'etapa', 'NEGOCIACION', EtapaOportunidad::Negociacion],
        [new Productos, 'naturaleza', 'SERVICIO', NaturalezaProducto::Servicio],
        [new Ventas, 'estado', 'FACTURADA', EstadoVenta::Facturada],
    ];

    foreach ($enumAttributes as [$model, $attribute, $databaseValue, $expectedValue]) {
        $model->setRawAttributes([$attribute => $databaseValue]);

        expect($model->getAttribute($attribute))->toBe($expectedValue);

        $model->setAttribute($attribute, $expectedValue);

        expect($model->getAttributes()[$attribute])->toBe($databaseValue);
    }
});

test('maps commercial relationships to their foreign keys', function () {
    $relationships = [
        [new Contactos, 'cliente', BelongsTo::class, 'id_cliente'],
        [new Ventas, 'oportunidad', BelongsTo::class, 'id_oportunidad'],
        [new Ventas, 'detalles', HasMany::class, 'id_venta'],
        [new DetalleVenta, 'producto', BelongsTo::class, 'id_producto'],
        [new Oportunidades, 'actividades', HasMany::class, 'id_oportunidad'],
        [new CasosPostventa, 'actividades', HasMany::class, 'id_caso'],
        [new Productos, 'detallesVenta', HasMany::class, 'id_producto'],
        [new Pagos, 'venta', BelongsTo::class, 'id_venta'],
        [new Comisiones, 'venta', BelongsTo::class, 'id_venta'],
        [new MovimientosFidelizacion, 'venta', BelongsTo::class, 'id_venta'],
    ];

    foreach ($relationships as [$model, $name, $relationClass, $foreignKey]) {
        $relation = $model->{$name}();

        expect($relation)->toBeInstanceOf($relationClass);
        expect($relation->getForeignKeyName())->toBe($foreignKey);
    }
});

test('uses creation-only timestamps for activities and commissions', function () {
    $activities = new Actividades;
    $commissions = new Comisiones;

    expect($activities->getCreatedAtColumn())->toBe('fecha_creacion');
    expect($activities->getUpdatedAtColumn())->toBeNull();
    expect($commissions->getCreatedAtColumn())->toBe('fecha_creacion');
    expect($commissions->getUpdatedAtColumn())->toBeNull();
});
