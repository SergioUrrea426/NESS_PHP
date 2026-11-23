<?php

use App\Http\Controllers\Seguridad\AuthController;
use App\Http\Controllers\Seguridad\CiberseguridadController;
use App\Http\Controllers\Seguridad\RegistroController;
use App\Http\Controllers\Seguridad\TwoFactorController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Middleware\CheckSessionTimeout;
use App\Http\Middleware\SetPostgresSessionContext;
use App\Models\Seguridad\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::inertia('/', 'welcome')->name('home');

// Rutas públicas de autenticación, registro y 2FA WhatsApp
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/registro', [RegistroController::class, 'show'])->name('register.show');
    Route::post('/registro', [RegistroController::class, 'store'])->name('register.store');

    Route::get('/2fa/whatsapp', [TwoFactorController::class, 'show'])->name('2fa.whatsapp.show');
    Route::post('/2fa/whatsapp/verificar', [TwoFactorController::class, 'verify'])->name('2fa.whatsapp.verify');
    Route::post('/2fa/whatsapp/reenviar', [TwoFactorController::class, 'resend'])->name('2fa.whatsapp.resend');
});

// Rutas autenticadas con inactividad y contexto PostgreSQL
Route::middleware(['auth', 'verified', CheckSessionTimeout::class, SetPostgresSessionContext::class])->group(function () {
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

    // Usuarios y roles conectados a PostgreSQL
    Route::get('/seguridad/usuarios', [UsuarioController::class, 'index'])
        ->name('seguridad.usuarios.index');
    Route::patch('/seguridad/usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])
        ->name('seguridad.usuarios.toggle-estado');

    // Monitoreo de Seguridad y Ciberseguridad (solo ADSIS)
    Route::prefix('seguridad/ciberseguridad')
        ->name('seguridad.ciberseguridad.')
        ->middleware('can:ver-ciberseguridad')
        ->group(function () {
            Route::get('/', [CiberseguridadController::class, 'index'])->name('index');
        });
});

require __DIR__.'/settings.php';