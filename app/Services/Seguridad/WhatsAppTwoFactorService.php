<?php
// app/Services/Seguridad/WhatsAppTwoFactorService.php

namespace App\Services\Seguridad;

use App\Contracts\WhatsAppOtpSenderInterface;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WhatsAppTwoFactorService
{
    public function __construct(
        private readonly WhatsAppOtpSenderInterface $sender
    ) {}

    public function emitirDesafio(Usuario $usuario): array
    {
        $ttlSegundos = (int) config('ness_seguridad.otp_ttl_segundos', 300);
        $minutos = max(1, (int) ceil($ttlSegundos / 60));
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $desafioId = (string) Str::uuid();

        Cache::put(
            $this->cacheKey($desafioId),
            [
                'id_usuario' => $usuario->id_usuario,
                'codigo_hash' => hash('sha256', $codigo),
                'intentos' => 0,
            ],
            now('America/Bogota')->addSeconds($ttlSegundos)
        );

        $enviado = $this->sender->enviarCodigoOtp($usuario, $codigo, $minutos);

        return [
            'desafio_id' => $desafioId,
            'enviado' => $enviado,
            'vigencia_segundos' => $ttlSegundos,
        ];
    }

    public function verificarYActivar(Usuario $usuario, string $desafioId, string $codigoIngresado, bool $activar2fa = false, ?string $ip = null): bool
    {
        $key = $this->cacheKey($desafioId);
        $payload = Cache::get($key);

        if (!$payload || (int) $payload['id_usuario'] !== (int) $usuario->id_usuario) {
            return false;
        }

        $maxIntentos = (int) config('ness_seguridad.otp_max_intentos', 5);
        if ((int) $payload['intentos'] >= $maxIntentos) {
            Cache::forget($key);
            return false;
        }

        $esValido = hash_equals($payload['codigo_hash'], hash('sha256', trim($codigoIngresado)));

        if (!$esValido) {
            $payload['intentos'] = (int) $payload['intentos'] + 1;
            Cache::put($key, $payload, now('America/Bogota')->addMinutes(5));
            return false;
        }

        // Uso único: eliminamos el código de caché inmediatamente
        Cache::forget($key);

        if ($activar2fa && !$usuario->activo_2fa) {
            DB::statement(
                "SELECT set_config('app.client_ip', ?, true)",
                [$ip ?? '']
            );
            $usuario->update(['activo_2fa' => true]);
        }

        return true;
    }

    private function cacheKey(string $desafioId): string
    {
        return "ness:2fa:whatsapp:{$desafioId}";
    }
}