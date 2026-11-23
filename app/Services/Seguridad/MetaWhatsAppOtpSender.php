<?php

namespace App\Services\Seguridad;

use App\Contracts\WhatsAppOtpSenderInterface;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWhatsAppOtpSender implements WhatsAppOtpSenderInterface
{
    public function enviarCodigoOtp(Usuario $usuario, string $codigo, int $minutosVigencia): bool
    {
        $driver = config('ness_seguridad.whatsapp.driver', 'log');
        $telefonoNormalizado = preg_replace('/\D+/', '', (string) $usuario->telefono);

        if ($driver !== 'meta') {
            if (!app()->environment('local')) {
                Log::warning('[NESS 2FA WhatsApp] Controlador log rechazado fuera de local.');
                return false;
            }

            Log::info('[NESS 2FA WhatsApp - DEV]', [
                'usuario_id' => $usuario->id_usuario,
                'username' => $usuario->username,
                'otp' => $codigo,
                'vigencia_min' => $minutosVigencia,
            ]);
            return true;
        }

        if (empty($telefonoNormalizado)) {
            return false;
        }

        $phoneNumberId = config('ness_seguridad.whatsapp.phone_number_id');
        $token = config('ness_seguridad.whatsapp.token');
        $template = config('ness_seguridad.whatsapp.template_name');
        $lang = config('ness_seguridad.whatsapp.language_code');

        $response = Http::withToken($token)
            ->timeout(10)
            ->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $telefonoNormalizado,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => ['code' => $lang],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $codigo],
                                ['type' => 'text', 'text' => (string) $minutosVigencia],
                            ],
                        ],
                    ],
                ],
            ]);

        return $response->successful();
    }
}