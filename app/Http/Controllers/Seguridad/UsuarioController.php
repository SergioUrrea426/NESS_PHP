<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'id_rol' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:ACTIVO,INACTIVO'],
        ]);

        $query = Usuario::with('rol')->orderByDesc('id_usuario');

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

        $roles = Rol::where('estado', true)
            ->orderByDesc('nivel_jerarquia')
            ->orderBy('codigo')
            ->get();

        $metricas = [
            'total' => Usuario::count(),
            'activas' => Usuario::where('estado', 'ACTIVO')->count(),
            'inactivas' => Usuario::where('estado', '<>', 'ACTIVO')->count(),
        ];

        return view('seguridad.usuarios.index', compact('usuarios', 'roles', 'metricas', 'filtros'));
    }

    public function toggleEstado(Usuario $usuario): RedirectResponse
    {
        $nuevoEstado = strtoupper((string) $usuario->estado) === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';

        $usuario->update([
            'estado' => $nuevoEstado,
        ]);

        return redirect()
            ->route('seguridad.usuarios.index')
            ->with('status', "Estado de {$usuario->username} actualizado a {$nuevoEstado} en la base de datos.");
    }
}