<?php

use App\Http\Controllers\Seguridad\AuthController;
use App\Http\Controllers\Seguridad\CiberseguridadController;
use App\Http\Controllers\Seguridad\RegistroController;
use App\Http\Controllers\Seguridad\TwoFactorController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Middleware\CheckSessionTimeout;
use App\Http\Middleware\SetPostgresSessionContext;
use App\Models\Seguridad\Usuario;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Middleware\EnsureRoleHierarchyAccess;


Route::inertia('/', 'welcome')->name('home');

// Rutas públicas de autenticación, registro y 2FA WhatsApp
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.attempt');

    Route::get('/registro', [RegistroController::class, 'show'])->name('register.show');
    Route::post('/registro', [RegistroController::class, 'store'])->name('register.store');

    Route::get('/2fa/whatsapp', [TwoFactorController::class, 'show'])->name('2fa.whatsapp.show');
    Route::post('/2fa/whatsapp/verificar', [TwoFactorController::class, 'verify'])
        ->middleware('throttle:2fa')
        ->name('2fa.whatsapp.verify');
    Route::post('/2fa/whatsapp/reenviar', [TwoFactorController::class, 'resend'])
        ->middleware('throttle:2fa')
        ->name('2fa.whatsapp.reenviar');
});

// Rutas autenticadas con inactividad y contexto PostgreSQL
Route::middleware(['auth', CheckSessionTimeout::class, SetPostgresSessionContext::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard con datos reales desde PostgreSQL
    Route::get('/dashboard', function () {
        $clientesTotal = Schema::hasTable('comercial.clientes')
            ? DB::table('comercial.clientes')->count()
            : 0;

        $cotizacionesTotal = Schema::hasTable('comercial.cotizaciones')
            ? DB::table('comercial.cotizaciones')->count()
            : 0;

        $oportunidadesTotal = Schema::hasTable('comercial.oportunidades')
            ? DB::table('comercial.oportunidades')->count()
            : 0;

        $ventasMes = 0;
        if (Schema::hasTable('comercial.cotizaciones')) {
            $ventasMes = (float) DB::table('comercial.cotizaciones')
                ->where('id_estado_cotizacion', 3)
                ->sum('total');
        }

        $kpis = [
            'clientes' => $clientesTotal,
            'cotizaciones' => $cotizacionesTotal,
            'oportunidades' => $oportunidadesTotal,
            'ventas' => $ventasMes,
            'usuarios_activos' => Usuario::where('estado', 'ACTIVO')->count(),
        ];

        return view('dashboard', compact('kpis'));
    })->name('dashboard');

    // Módulo comercial: renderiza la vista existente con valores por defecto mientras se integra el controlador
    Route::get('/comercial/clientes', function () {
        $tieneTabla = Schema::hasTable('comercial.clientes');

        $totalClientes = $tieneTabla ? DB::table('comercial.clientes')->count() : 0;

        $clientesActivos = 0;
        if ($tieneTabla) {
            if (Schema::hasColumn('comercial.clientes', 'estado')) {
                $clientesActivos = DB::table('comercial.clientes')->where('estado', 'ACTIVO')->count();
            } elseif (Schema::hasColumn('comercial.clientes', 'activo')) {
                $clientesActivos = DB::table('comercial.clientes')->where('activo', true)->count();
            }
        }

        $prospectos = ($tieneTabla && Schema::hasColumn('comercial.clientes', 'etapa'))
            ? DB::table('comercial.clientes')->where('etapa', 'PROSPECTO')->count()
            : 0;

        $clientes = new LengthAwarePaginator(
            collect(),
            0,
            15,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $filtros = [
            'search' => request('search', ''),
            'personeria' => request('personeria', ''),
            'etapa' => request('etapa', ''),
        ];

        return view('comercial.Clientes', compact(
            'totalClientes',
            'clientesActivos',
            'prospectos',
            'clientes',
            'filtros'
        ));
    })->name('comercial.clientes.index');

    // Usuarios y roles conectados a PostgreSQL
    Route::get('/seguridad/usuarios', [UsuarioController::class, 'index'])
        ->middleware('can:gestionar-usuarios')
        ->name('seguridad.usuarios.index');
    Route::patch('/seguridad/usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])
        ->middleware('can:gestionar-usuarios')
        ->name('seguridad.usuarios.toggle-estado');

    // Monitoreo de Seguridad y Ciberseguridad (solo ADSIS)
    Route::prefix('seguridad/ciberseguridad')
        ->name('seguridad.ciberseguridad.')
        ->middleware('can:ver-ciberseguridad')
        ->group(function () {
            Route::get('/', [CiberseguridadController::class, 'index'])->name('index');
        });
    Route::prefix('seguridad/usuarios')
        ->name('seguridad.usuarios.')
        ->middleware(EnsureRoleHierarchyAccess::class . ':ADSIS,AC')
        ->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('index');
            Route::patch('/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('toggle-estado');
        });

    Route::prefix('seguridad/ciberseguridad')
        ->name('seguridad.ciberseguridad.')
        ->middleware('can:ver-ciberseguridad')
        ->group(function () {
            Route::get('/', [CiberseguridadController::class, 'index'])->name('index');
        });

    Route::prefix('seguridad/usuarios')
        ->name('seguridad.usuarios.')
        ->middleware(EnsureRoleHierarchyAccess::class . ':ADSIS,AC')
        ->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('index');
            Route::post('/', [UsuarioController::class, 'store'])->name('store');
            Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
            Route::patch('/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('toggle-estado');
        });
    Route::prefix('seguridad/usuarios')
        ->name('seguridad.usuarios.')
        ->middleware(EnsureRoleHierarchyAccess::class . ':ADSIS,AC')
        ->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('index');
            Route::post('/', [UsuarioController::class, 'store'])->name('store');
            Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
            Route::patch('/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('toggle-estado');
        });
});

require __DIR__ . '/settings.php';
