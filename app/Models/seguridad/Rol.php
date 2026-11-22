<?php
//Modelos Eloquent sobre el Esquema seguridad
//Mapeamos las tablas reales del DDL (seguridad.roles, seguridad.usuarios, seguridad.bitacora_eventos).

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'seguridad.roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'nivel_jerarquia',
        'estado',
    ];

    protected $casts = [
        'nivel_jerarquia' => 'integer',
        'estado' => 'boolean',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }

    public function esAdministradorSistema(): bool
    {
        return strtoupper($this->codigo) === 'ADSIS';
    }
}