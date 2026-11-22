<?php

namespace App\Enums\Comercial;

enum EtapaCliente: string
{
    case Prospecto = 'PROSPECTO';
    case Nuevo = 'NUEVO';
    case Activo = 'ACTIVO';
    case Fiel = 'FIEL';
    case EnRiesgo = 'EN_RIESGO';
    case Inactivo = 'INACTIVO';
}
