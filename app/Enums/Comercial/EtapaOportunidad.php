<?php

namespace App\Enums\Comercial;

enum EtapaOportunidad: string
{
    case Prospeccion = 'PROSPECCION';
    case Calificacion = 'CALIFICACION';
    case Propuesta = 'PROPUESTA';
    case Negociacion = 'NEGOCIACION';
    case Ganada = 'GANADA';
    case Perdida = 'PERDIDA';
}
