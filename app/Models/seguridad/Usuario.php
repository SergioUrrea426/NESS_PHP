<?php

//Modelos Eloquent sobre el Esquema seguridad
//Mapeamos las tablas reales del DDL (seguridad.roles, seguridad.usuarios, seguridad.bitacora_eventos).

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    protected $table = 'seguridad.usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'nombres',
        'username',
        'email',
        'password_hash',
        'telefono',
        'estado',
        'activo_2fa',
        'ultimo_acceso',
        'intentos_fallidos',
        'bloqueo_hasta',
        'requiere_cambio_password',
        'fecha_actualizacion',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'activo_2fa' => 'boolean',
        'ultimo_acceso' => 'datetime',
        'intentos_fallidos' => 'integer',
        'bloqueo_hasta' => 'datetime',
        'requiere_cambio_password' => 'boolean',
        'fecha_actualizacion' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function estaBloqueado(): bool
    {
        return $this->bloqueo_hasta !== null && now('America/Bogota')->lt($this->bloqueo_hasta);
    }

    public function estaActivo(): bool
    {
        return strtoupper((string) $this->estado) === 'ACTIVO';
    }
}