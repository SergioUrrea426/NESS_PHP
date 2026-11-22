<?php

namespace App\Models\Comercial;

use App\Enums\Comercial\EtapaCliente;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    protected $table = 'comercial.clientes';

    protected $primaryKey = 'id_cliente';

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'tipo_persona',
        'id_tipo_documento',
        'numero_documento',
        'digito_verificacion',
        'nombres',
        'apellidos',
        'razon_social',
        'correo',
        'telefono',
        'direccion',
        'id_ubicacion',
        'id_responsable',
        'acepta_habeas_data',
        'fecha_aceptacion_habeas_data',
        'etapa_cliente',
    ];

    protected $casts = [
        'id_cliente' => 'int',
        'id_tipo_documento' => 'int',
        'id_ubicacion' => 'int',
        'id_responsable' => 'int',
        'acepta_habeas_data' => 'boolean',
        'fecha_aceptacion_habeas_data' => 'datetime',
        'etapa_cliente' => EtapaCliente::class,
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];
}
