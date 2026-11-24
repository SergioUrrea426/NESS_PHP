<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarioAuth = $request->user();
        $codigoAuth = strtoupper((string) optional($usuarioAuth?->rol)->codigo);
        $nivelAuth = (int) (optional($usuarioAuth?->rol)->nivel_jerarquia ?? 0);

        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'id_rol' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $alcanceQuery = Usuario::query();

        if ($codigoAuth !== 'ADSIS') {
            $alcanceQuery->whereHas('rol', function ($q) use ($nivelAuth) {
                $q->where('nivel_jerarquia', '<', $nivelAuth)
                    ->whereRaw('UPPER(codigo) <> ?', ['ADSIS']);
            });
        }

        $query = (clone $alcanceQuery)
            ->with('rol')
            ->orderByDesc('id_usuario');

        if (!empty($filtros['search'])) {
            $termino = '%' . trim($filtros['search']) . '%';
            $query->where(function ($q) use ($termino) {
                $q->where('nombres', 'ILIKE', $termino)
                    ->orWhere('username', 'ILIKE', $termino)
                    ->orWhere('email', 'ILIKE', $termino);
            });
        }

        if (!empty($filtros['id_rol'])) {
            $query->where('id_rol', (int) $filtros['id_rol']);
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', strtoupper($filtros['estado']));
        }

        $usuarios = $query->get();

        $rolesQuery = Rol::where('estado', true);
        if ($codigoAuth !== 'ADSIS') {
            $rolesQuery->where('nivel_jerarquia', '<', $nivelAuth)
                ->whereRaw('UPPER(codigo) <> ?', ['ADSIS']);
        }

        $roles = $rolesQuery
            ->orderByDesc('nivel_jerarquia')
            ->orderBy('codigo')
            ->get();

        $metricas = [
            'total' => (clone $alcanceQuery)->count(),
            'activas' => (clone $alcanceQuery)->where('estado', 'ACTIVO')->count(),
            'inactivas' => (clone $alcanceQuery)->where('estado', '<>', 'ACTIVO')->count(),
        ];

        return view('seguridad.usuarios.index', compact('usuarios', 'roles', 'metricas', 'filtros'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Usuario::class);

        $authUser = $request->user();
        $codigoAuth = strtoupper((string) optional($authUser?->rol)->codigo);
        $nivelAuth = (int) (optional($authUser?->rol)->nivel_jerarquia ?? 0);

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'max:60',
                'regex:/^[A-Za-z0-9._-]+$/',
                'unique:pgsql.seguridad.usuarios,username',
            ],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:pgsql.seguridad.usuarios,email'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'id_rol' => ['required', 'integer', 'exists:pgsql.seguridad.roles,id_rol'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'password' => ['required', Password::min(8)],
        ]);

        $rolAsignado = Rol::findOrFail($datos['id_rol']);

        if ($codigoAuth !== 'ADSIS') {
            if (strtoupper((string) $rolAsignado->codigo) === 'ADSIS' || (int) $rolAsignado->nivel_jerarquia >= $nivelAuth) {
                abort(403, 'No puedes asignar un rol de igual o mayor jerarquía que el tuyo.');
            }
        }

        $hashArgon2id = Hash::driver('argon2id')->make($datos['password'], [
            'memory' => 65536,
            'time' => 4,
            'threads' => 1,
        ]);

        $nuevoUsuario = DB::transaction(function () use ($request, $datos, $rolAsignado, $hashArgon2id, $authUser) {
            $creado = Usuario::create([
                'id_rol' => $rolAsignado->id_rol,
                'nombres' => trim($datos['nombres']),
                'username' => strtolower(trim($datos['username'])),
                'email' => strtolower(trim($datos['email'])),
                'password_hash' => $hashArgon2id,
                'telefono' => !empty($datos['telefono']) ? trim($datos['telefono']) : null,
                'estado' => strtoupper($datos['estado']),
                'activo_2fa' => false,
                'requiere_cambio_password' => true,
            ]);

            try {
                DB::statement(
                    'SELECT seguridad.fn_registrar_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::uuid, ?, ?)',
                    [
                        'SEGURIDAD',
                        $authUser?->id_usuario,
                        'SEGURIDAD',
                        'usuarios',
                        $creado->id_usuario,
                        'USUARIO_CREADO',
                        null,
                        $creado->estado,
                        $request->ip(),
                        $request->userAgent(),
                        null,
                        'MEDIA',
                        "Usuario {$creado->username} creado con rol {$rolAsignado->codigo} por {$authUser?->username}",
                    ]
                );
            } catch (\Throwable $e) {
                // Evita romper la transacción si el trigger de BD ya audita el evento
            }

            return $creado;
        });

        return redirect()
            ->route('seguridad.usuarios.index')
            ->with('status', "Usuario {$nuevoUsuario->username} creado con rol {$rolAsignado->codigo}.");
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        Gate::authorize('update', $usuario);

        $authUser = $request->user();
        $codigoAuth = strtoupper((string) optional($authUser?->rol)->codigo);
        $nivelAuth = (int) (optional($authUser?->rol)->nivel_jerarquia ?? 0);

        $datos = $request->validate([
            'nombres' => ['required', 'string', 'max:150'],
            'username' => [
                'required',
                'string',
                'max:60',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('pgsql.seguridad.usuarios', 'username')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('pgsql.seguridad.usuarios', 'email')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'telefono' => ['nullable', 'string', 'max:30'],
            'id_rol' => ['required', 'integer', 'exists:pgsql.seguridad.roles,id_rol'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
            'password' => ['nullable', Password::min(8)],
        ]);

        $rolNuevo = Rol::findOrFail($datos['id_rol']);

        if ($codigoAuth !== 'ADSIS') {
            if (strtoupper((string) $rolNuevo->codigo) === 'ADSIS' || (int) $rolNuevo->nivel_jerarquia >= $nivelAuth) {
                abort(403, 'No puedes asignar un rol de igual o mayor jerarquía que el tuyo.');
            }
        }

        if (strtoupper($datos['estado']) !== 'ACTIVO' && (int) $usuario->id_usuario === (int) $authUser?->id_usuario) {
            return back()
                ->withInput()
                ->withErrors(['estado' => 'No puedes desactivar tu propia cuenta.']);
        }

        DB::transaction(function () use ($request, $usuario, $datos, $rolNuevo, $authUser) {
            $estadoAnterior = (string) $usuario->estado;
            $rolAnteriorCodigo = strtoupper((string) optional($usuario->rol)->codigo);
            $rolNuevoCodigo = strtoupper((string) $rolNuevo->codigo);

            $payload = [
                'nombres' => trim($datos['nombres']),
                'username' => strtolower(trim($datos['username'])),
                'email' => strtolower(trim($datos['email'])),
                'telefono' => !empty($datos['telefono']) ? trim($datos['telefono']) : null,
                'id_rol' => $rolNuevo->id_rol,
                'estado' => strtoupper($datos['estado']),
            ];

            if (!empty($datos['password'])) {
                $payload['password_hash'] = Hash::driver('argon2id')->make($datos['password'], [
                    'memory' => 65536,
                    'time' => 4,
                    'threads' => 1,
                ]);
            }

            $usuario->update($payload);

            try {
                DB::statement(
                    'SELECT seguridad.fn_registrar_evento(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::uuid, ?, ?)',
                    [
                        'SEGURIDAD',
                        $authUser?->id_usuario,
                        'SEGURIDAD',
                        'usuarios',
                        $usuario->id_usuario,
                        'USUARIO_ACTUALIZADO',
                        $rolAnteriorCodigo . ':' . $estadoAnterior,
                        $rolNuevoCodigo . ':' . $usuario->estado,
                        $request->ip(),
                        $request->userAgent(),
                        null,
                        'ALTA',
                        "Usuario {$usuario->username} actualizado (rol {$rolAnteriorCodigo} -> {$rolNuevoCodigo}, estado {$usuario->estado}) por {$authUser?->username}",
                    ]
                );
            } catch (\Throwable $e) {
                // Evita romper la transacción si el trigger de BD ya audita el evento
            }
        });

        return redirect()
            ->route('seguridad.usuarios.index')
            ->with('status', "Datos y rol de {$usuario->username} actualizados a {$rolNuevo->codigo}.");
    }

    public function toggleEstado(Usuario $usuario): RedirectResponse
    {
        Gate::authorize('toggleEstado', $usuario);

        $nuevoEstado = strtoupper((string) $usuario->estado) === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';

        $usuario->update([
            'estado' => $nuevoEstado,
        ]);

        return redirect()
            ->route('seguridad.usuarios.index')
            ->with('status', "Estado de {$usuario->username} actualizado a {$nuevoEstado} en la base de datos.");
    }
}