<?php
// app/Http/Middleware/CheckSessionTimeout.php

namespace App\Http\Middleware;

use App\Services\Seguridad\AuthSecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    public function __construct(
        private readonly AuthSecurityService $authSecurityService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $limiteMinutos = (int) config('ness_seguridad.inactividad_minutos', 30);
            $ultimaActividad = $request->session()->get('ness_last_activity');
            $ahora = now('America/Bogota')->timestamp;

            if ($ultimaActividad !== null && ($ahora - (int) $ultimaActividad) >= ($limiteMinutos * 60)) {
                $usuario = Auth::user();
                $sesionUuid = $request->session()->get('ness_sesion_uuid');

                $this->authSecurityService->registrarEventoSeguridad(
                    $usuario->id_usuario,
                    'SESSION_TIMEOUT',
                    'ACTIVA',
                    'EXPIRADA',
                    $request->ip(),
                    substr((string) $request->userAgent(), 0, 255),
                    $sesionUuid,
                    'BAJA',
                    "Cierre automático por {$limiteMinutos} min de inactividad (RNF-004)"
                );

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors(['username' => 'Tu sesión se cerró automáticamente por inactividad.']);
            }

            $request->session()->put('ness_last_activity', $ahora);
        }

        return $next($request);
    }
}