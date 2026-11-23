<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegistroController extends Controller
{
    public function show(): View
    {
        return view('seguridad.auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:pgsql.seguridad.usuarios,username'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:pgsql.seguridad.usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Asignar rol inicial seguro por defecto (nunca ADSIS desde auto-registro)
        $rolInicial = Rol::where('codigo', 'ASC')->where('estado', true)->first()
            ?? Rol::where('codigo', '<>', 'ADSIS')->where('estado', true)->orderBy('nivel_jerarquia')->first();

        if (!$rolInicial) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'No hay un rol inicial configurado en seguridad.roles.']);
        }

        $hashArgon2id = Hash::driver('argon2id')->make($datos['password'], [
            'memory' => 65536,
            'time' => 4,
            'threads' => 1,
        ]);

        $usuario = Usuario::create([
            'id_rol' => $rolInicial->id_rol,
            'nombres' => trim($datos['nombres']),
            'username' => strtolower(trim($datos['username'])),
            'email' => strtolower(trim($datos['email'])),
            'password_hash' => $hashArgon2id,
            'telefono' => !empty($datos['telefono']) ? trim($datos['telefono']) : null,
            'estado' => 'ACTIVO',
            'activo_2fa' => false,
            'requiere_cambio_password' => false,
        ]);

        // Registrar alta de cuenta en seguridad.bitacora_eventos para el visor de ciberseguridad
        DB::statement(
            'SELECT seguridad.fn_registrar_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::uuid, ?, ?)',
            [
                'SEGURIDAD',
                $usuario->id_usuario,
                'SEGURIDAD',
                'usuarios',
                $usuario->id_usuario,
                'REGISTRO_PRIMERA_VEZ',
                null,
                'ACTIVO',
                $request->ip(),
                $request->userAgent(),
                null,
                'MEDIA',
                "Auto-registro de primera vez para {$usuario->username} con rol {$rolInicial->codigo}",
            ]
        );

        return redirect()
            ->route('login')
            ->with('status', 'Cuenta registrada correctamente. Ya puedes iniciar sesión con tu usuario y contraseña.');
    }
}