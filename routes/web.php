<?php

use App\Http\Controllers\Comercial\ClientesController;
use App\Http\Controllers\Seguridad\AuthController;
use App\Http\Controllers\Seguridad\CiberseguridadController;
use App\Http\Controllers\Seguridad\TwoFactorController;
use App\Http\Middleware\CheckSessionTimeout;
use App\Http\Middleware\SetPostgresSessionContext;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

// Rutas públicas de autenticación y 2FA WhatsApp
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/2fa/whatsapp', [TwoFactorController::class, 'show'])->name('2fa.whatsapp.show');
    Route::post('/2fa/whatsapp/verificar', [TwoFactorController::class, 'verify'])->name('2fa.whatsapp.verify');
    Route::post('/2fa/whatsapp/reenviar', [TwoFactorController::class, 'resend'])->name('2fa.whatsapp.resend');
});

// Rutas autenticadas con control de inactividad y contexto en PostgreSQL
Route::middleware(['auth', 'verified', CheckSessionTimeout::class, SetPostgresSessionContext::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::inertia('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/comercial/clientes', [ClientesController::class, 'index'])
        ->name('comercial.clientes.index');

    // Monitoreo de Seguridad y Ciberseguridad (solo ADSIS)
    Route::prefix('seguridad/ciberseguridad')
        ->name('seguridad.ciberseguridad.')
        ->middleware('can:ver-ciberseguridad')
        ->group(function () {
            Route::get('/', [CiberseguridadController::class, 'index'])->name('index');
        });
});

require __DIR__.'/settings.php';