<?php

//Modelos Eloquent sobre el Esquema seguridad
//Mapeamos las tablas reales del DDL (seguridad.roles, seguridad.usuarios, seguridad.bitacora_eventos).

namespace App\Models\Seguridad;

use App\Models\Seguridad\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraEvento extends Model
{
    protected $table = 'seguridad.bitacora_eventos';
    protected $primaryKey = 'id_evento';
    public $timestamps = false;

    protected $casts = [
        'fecha_evento' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}