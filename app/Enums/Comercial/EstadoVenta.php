<?php

namespace App\Enums\Comercial;

enum EstadoVenta: string
{
    case Cotizacion = 'COTIZACION';
    case Confirmada = 'CONFIRMADA';
    case Facturada = 'FACTURADA';
    case Anulada = 'ANULADA';
}
