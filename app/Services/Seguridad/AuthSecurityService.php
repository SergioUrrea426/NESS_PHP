<?php
// app/Services/Seguridad/AuthSecurityService.php

namespace App\Services\Seguridad;

use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthSecurityService
{
    public function __construct(
        private readonly WhatsAppTwoFactorService $twoFactorService
    ) {}

    /**
     * Retorna:
     * - ['status' => 'blocked', 'bloqueo_hasta' => ...]
     * - ['status' => 'invalid_credentials']
     * - ['status' => '2fa_required', 'usuario' => Usuario, 'desafio' => array]
     * - ['status' => 'authenticated', 'usuario' => Usuario, 'sesion_uuid' => string]
     */
    public function intentarLogin(string $usernameOrEmail, string $password, ?string $ip, ?string $userAgent): array
    {
        $usuario = Usuario::with('rol')
            ->where(function ($q) use ($usernameOrEmail) {
                $q->where('username', $usernameOrEmail)
                  ->orWhere('email', $usernameOrEmail);
            })
            ->first();

        if (!$usuario || !$usuario->estaActivo()) {
            $this->registrarEventoSeguridad(
                $usuario?->id_usuario,
                'LOGIN_RECHAZADO',
                $usuario?->estado ?? 'NO_EXISTE',
                'DENEGADO',
                $ip,
                $userAgent,
                null,
                'MEDIA',
                'Intento con cuenta inactiva o inexistente'
            );
            return ['status' => 'invalid_credentials'];
        }

        if ($usuario->estaBloqueado()) {
            $this->registrarEventoSeguridad(
                $usuario->id_usuario,
                'LOGIN_BLOQUEADO',
                'ACTIVO',
                'BLOQUEADO',
                $ip,
                $userAgent,
                null,
                'ALTA',
                'Intento durante bloqueo temporal activo'
            );
            return [
                'status' => 'blocked',
                'bloqueo_hasta' => $usuario->bloqueo_hasta,
            ];
        }

        $hashGuardado = (string) $usuario->password_hash;

        $passwordValido = str_starts_with($hashGuardado, '$argon2')
            ? Hash::driver('argon2id')->check($password, $hashGuardado)
            : (str_starts_with($hashGuardado, '$2y$') || str_starts_with($hashGuardado, '$2a$') || str_starts_with($hashGuardado, '$2b$')
                ? Hash::check($password, $hashGuardado)
                : false);

        if (!$passwordValido) {
            $maxIntentos = (int) config('ness_seguridad.max_intentos_login', 5);
            $minutosBloqueo = (int) config('ness_seguridad.minutos_bloqueo_login', 15);

            $resultado = DB::selectOne(
                'SELECT * FROM seguridad.fn_registrar_intento_fallido(?, ?, ?, ?, ?)',
                [$usuario->id_usuario, $maxIntentos, $minutosBloqueo, $ip, $userAgent]
            );

            if ($resultado && !empty($resultado->bloqueado_hasta)) {
                return [
                    'status' => 'blocked',
                    'bloqueo_hasta' => $resultado->bloqueo_hasta,
                ];
            }

            return ['status' => 'invalid_credentials'];
        }

        // Contraseña válida: si tiene activo_2fa o su rol exige 2FA, emitimos desafío por WhatsApp
        $rolExige2fa = in_array(
            strtoupper((string) optional($usuario->rol)->codigo),
            config('ness_seguridad.roles_2fa_obligatorio', []),
            true
        );

        if ($usuario->activo_2fa || $rolExige2fa) {
            $desafio = $this->twoFactorService->emitirDesafio($usuario);
            return [
                'status' => '2fa_required',
                'usuario' => $usuario,
                'desafio' => $desafio,
                'debe_activar_2fa' => !$usuario->activo_2fa,
            ];
        }

        return $this->completarAutenticacion($usuario, $ip, $userAgent);
    }

    public function completarAutenticacion(Usuario $usuario, ?string $ip, ?string $userAgent): array
    {
        $sesionUuid = (string) Str::uuid();

        DB::statement(
            'SELECT seguridad.fn_reiniciar_intentos_login(?, ?, ?, ?::uuid)',
            [$usuario->id_usuario, $ip, $userAgent, $sesionUuid]
        );

        return [
            'status' => 'authenticated',
            'usuario' => $usuario->fresh(['rol']),
            'sesion_uuid' => $sesionUuid,
        ];
    }

    public function registrarEventoSeguridad(
        ?int $idUsuario,
        string $accion,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $ip,
        ?string $userAgent,
        ?string $sesionUuid,
        string $severidad = 'MEDIA',
        ?string $observaciones = null
    ): void {
        DB::statement(
            'SELECT seguridad.fn_registrar_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::uuid, ?, ?)',
            [
                'SEGURIDAD',
                $idUsuario,
                'SEGURIDAD',
                'usuarios',
                $idUsuario,
                $accion,
                $estadoAnterior,
                $estadoNuevo,
                $ip,
                $userAgent,
                $sesionUuid,
                $severidad,
                $observaciones,
            ]
        );
    }
}