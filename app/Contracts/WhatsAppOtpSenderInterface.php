<?php

//SERVICIO 2FA por WhatsAPP
//GENERA EL CODIGO DE 6 DIGITOS EN LARAVEL, LO GUARDA SOLO EN CACHE CON TTL, LO ENVIA POR WHATSAPP Y VALIDA

namespace App\Contracts;

use App\Models\Seguridad\Usuario;

interface WhatsAppOtpSenderInterface
{
    public function enviarCodigoOtp(Usuario $usuario, string $codigo, int $minutosVigencia): bool;
}