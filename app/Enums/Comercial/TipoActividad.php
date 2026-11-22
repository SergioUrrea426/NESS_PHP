<?php

namespace App\Enums\Comercial;

enum TipoActividad: string
{
    case Llamada = 'LLAMADA';
    case Correo = 'CORREO';
    case Reunion = 'REUNION';
    case Tarea = 'TAREA';
    case Nota = 'NOTA';
}
