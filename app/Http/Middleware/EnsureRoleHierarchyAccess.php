<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleHierarchyAccess
{
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        $rolActual = strtoupper((string) optional($usuario->rol)->codigo);
        $permitidos = array_map(fn ($rol) => strtoupper(trim($rol)), $rolesPermitidos);

        if (!empty($permitidos) && !in_array($rolActual, $permitidos, true)) {
            try {
                DB::statement(
                    'SELECT seguridad.fn_registrar_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::uuid, ?, ?)',
                    [
                        'SEGURIDAD',
                        $usuario->id_usuario,
                        'SEGURIDAD',
                        'usuarios',
                        $usuario->id_usuario,
                        'ACCESO_DENEGADO_403',
                        $rolActual ?: null,
                        'DENEGADO',
                        $request->ip(),
                        $request->userAgent(),
                        null,
                        'ALTA',
                        "Intento de acceso no autorizado a {$request->path()} con rol {$rolActual}",
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('No se pudo auditar ACCESO_DENEGADO_403 en bitacora_eventos: ' . $e->getMessage());
            }

            abort(403, 'Tu nivel jerárquico o rol no tiene acceso a esta sección.');
        }

        return $next($request);
    }
}