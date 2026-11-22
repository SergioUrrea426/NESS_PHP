<?php
// app/Http/Controllers/Seguridad/CiberseguridadController.php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Models\Seguridad\BitacoraEvento;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CiberseguridadController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'tipo_evento' => ['nullable', 'in:SEGURIDAD,TRANSACCION'],
            'severidad' => ['nullable', 'string', 'max:20'],
            'accion' => ['nullable', 'string', 'max:20'],
            'id_usuario' => ['nullable', 'integer'],
        ]);

        $query = BitacoraEvento::with('usuario.rol')
            ->orderByDesc('id_evento');

        if (!empty($filtros['tipo_evento'])) {
            $query->where('tipo_evento', $filtros['tipo_evento']);
        }
        if (!empty($filtros['severidad'])) {
            $query->where('severidad', strtoupper($filtros['severidad']));
        }
        if (!empty($filtros['accion'])) {
            $query->where('accion', 'ILIKE', '%' . $filtros['accion'] . '%');
        }
        if (!empty($filtros['id_usuario'])) {
            $query->where('id_usuario', (int) $filtros['id_usuario']);
        }

        $eventos = $query->paginate(20)->withQueryString();

        $usuariosRiesgo = Usuario::with('rol')
            ->where(function ($q) {
                $q->where('intentos_fallidos', '>', 0)
                  ->orWhereNotNull('bloqueo_hasta')
                  ->orWhere('estado', '<>', 'ACTIVO');
            })
            ->orderByDesc('intentos_fallidos')
            ->get();

        $kpis = [
            'eventos_hoy' => BitacoraEvento::whereDate('fecha_evento', now('America/Bogota')->toDateString())->count(),
            'alertas_altas' => BitacoraEvento::where('severidad', 'ALTA')->count(),
            'cuentas_bloqueadas' => Usuario::whereNotNull('bloqueo_hasta')
                ->where('bloqueo_hasta', '>', now('America/Bogota'))
                ->count(),
            'usuarios_con_2fa' => Usuario::where('activo_2fa', true)->count(),
        ];

        return view('seguridad.ciberseguridad.index', compact('eventos', 'usuariosRiesgo', 'kpis', 'filtros'));
    }
}