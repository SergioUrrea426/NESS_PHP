<?php

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contactos extends Model
{
    protected $table = 'comercial.contactos';

    protected $primaryKey = 'id_contacto';

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'nombre',
        'cargo',
        'email',
        'telefono',
        'es_principal',
    ];

    protected $casts = [
        'id_cliente' => 'int',
        'es_principal' => 'boolean',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Clientes::class, 'id_cliente', 'id_cliente');
    }
}
