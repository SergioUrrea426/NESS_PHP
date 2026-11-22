<?php

namespace App\Enums\Comercial;

enum TipoMovimientoFidelizacion: string
{
    case Acumulacion = 'ACUMULACION';
    case Redencion = 'REDENCION';
    case Ajuste = 'AJUSTE';
}
