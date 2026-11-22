<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\Usuario;
use App\Services\Seguridad\AuthSecurityService;
use App\Services\Seguridad\WhatsAppTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(
        private readonly WhatsAppTwoFactorService $twoFactorService,
        private readonly AuthSecurityService $authSecurityService
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('ness_2fa_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $usuario = Usuario::find($pending['id_usuario']);
        if (!$usuario) {
            $request->session()->forget('ness_2fa_pending');
            return redirect()->route('login');
        }

        return view('seguridad.auth.whatsapp-2fa', [
            'usuario' => $usuario,
            'pending' => $pending,
        ]);
    }

    public function verify(Request $request, AuthController $authController): RedirectResponse
    {
        $pending = $request->session()->get('ness_2fa_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $request->validate([
            'codigo_otp' => ['required', 'digits:6'],
        ], [
            'codigo_otp.required' => 'Ingresa el código OTP enviado por WhatsApp.',
            'codigo_otp.digits' => 'El código OTP debe tener exactamente 6 dígitos numéricos.',
        ]);

        $usuario = Usuario::with('rol')->findOrFail($pending['id_usuario']);
        $ip = $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 255);

        $valido = $this->twoFactorService->verificarYActivar(
            $usuario,
            $pending['desafio_id'],
            $request->input('codigo_otp'),
            (bool) ($pending['debe_activar_2fa'] ?? false),
            $ip
        );

        if (!$valido) {
            $this->authSecurityService->registrarEventoSeguridad(
                $usuario->id_usuario,
                '2FA_WHATSAPP_FALLIDO',
                'PENDIENTE_2FA',
                'DENEGADO',
                $ip,
                $userAgent,
                null,
                'ALTA',
                'Código OTP de WhatsApp inválido o expirado'
            );

            return back()->withErrors([
                'codigo_otp' => 'El código ingresado es inválido o ha expirado. Solicita uno nuevo si es necesario.',
            ]);
        }

        $request->session()->forget('ness_2fa_pending');
        $auth = $this->authSecurityService->completarAutenticacion($usuario, $ip, $userAgent);

        return $authController->iniciarSesionLaravel($request, $auth['usuario'], $auth['sesion_uuid']);
    }

    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('ness_2fa_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        $usuario = Usuario::findOrFail($pending['id_usuario']);
        $nuevoDesafio = $this->twoFactorService->emitirDesafio($usuario);

        $request->session()->put('ness_2fa_pending', [
            'id_usuario' => $usuario->id_usuario,
            'desafio_id' => $nuevoDesafio['desafio_id'],
            'vigencia_segundos' => $nuevoDesafio['vigencia_segundos'],
            'debe_activar_2fa' => $pending['debe_activar_2fa'] ?? false,
            'enviado' => $nuevoDesafio['enviado'],
        ]);

        return back()->with('status', 'Se ha enviado un nuevo código OTP a tu WhatsApp.');
    }
}