<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\Usuario;
use App\Services\Seguridad\AuthSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthSecurityService $authSecurityService
    ) {}

    public function showLoginForm(): View
    {
        return view('seguridad.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Ingresa tu nombre de usuario o correo institucional.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);
        $credentials['username'] = strtolower(trim($credentials['username']));

        $ip = $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 255);

        $resultado = $this->authSecurityService->intentarLogin(
            $credentials['username'],
            $credentials['password'],
            $ip,
            $userAgent
        );

        if ($resultado['status'] === 'blocked') {
            $hasta = optional($resultado['bloqueo_hasta'])->format('d/m/Y H:i:s') ?? 'unos minutos';
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => "Cuenta bloqueada temporalmente por intentos fallidos. Intenta nuevamente después de {$hasta}.",
                ]);
        }

        if ($resultado['status'] === 'invalid_credentials') {
            // Mensaje genérico para no revelar si el usuario existe (HU-001 Condición 04)
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'Credenciales inválidas o cuenta inactiva.',
                ]);
        }

        if ($resultado['status'] === '2fa_required') {
            $request->session()->put('ness_2fa_pending', [
                'id_usuario' => $resultado['usuario']->id_usuario,
                'desafio_id' => $resultado['desafio']['desafio_id'],
                'vigencia_segundos' => $resultado['desafio']['vigencia_segundos'],
                'debe_activar_2fa' => $resultado['debe_activar_2fa'],
                'enviado' => $resultado['desafio']['enviado'],
            ]);

            if (!$resultado['desafio']['enviado']) {
                return redirect()
                    ->route('2fa.whatsapp.show')
                    ->withErrors(['codigo_otp' => 'No fue posible enviar el código por WhatsApp. Verifica el teléfono registrado e intenta nuevamente.']);
            }

            return redirect()
                ->route('2fa.whatsapp.show')
                ->with('status', 'Enviamos un código de verificación de 6 dígitos a tu WhatsApp.');
        }

        return $this->iniciarSesionLaravel($request, $resultado['usuario'], $resultado['sesion_uuid']);
    }

    public function logout(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        $sesionUuid = $request->session()->get('ness_sesion_uuid');

        if ($usuario) {
            $this->authSecurityService->registrarEventoSeguridad(
                $usuario->id_usuario,
                'LOGOUT',
                'ACTIVA',
                'CERRADA',
                $request->ip(),
                substr((string) $request->userAgent(), 0, 255),
                $sesionUuid,
                'BAJA',
                'Cierre de sesión voluntario'
            );
        }

        Auth::logout();
        $request->session()->forget(['ness_sesion_uuid', 'ness_last_activity', 'ness_2fa_pending']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sesión cerrada correctamente.');
    }

    public function iniciarSesionLaravel(Request $request, Usuario $usuario, string $sesionUuid): RedirectResponse
    {
        Auth::login($usuario);
        $request->session()->regenerate();
        $request->session()->put('ness_sesion_uuid', $sesionUuid);
        $request->session()->put('ness_last_activity', now('America/Bogota')->timestamp);

        if ($usuario->requiere_cambio_password) {
            return redirect()
                ->route('dashboard')
                ->with('warning', 'Debes actualizar tu contraseña temporal antes de continuar.');
        }

        return redirect()->intended(route('dashboard'));
    }
}