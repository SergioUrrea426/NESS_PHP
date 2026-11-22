<?php

use App\Http\Controllers\comercial\ClientesController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('comercial/clientes', [ClientesController::class, 'index'])
        ->name('comercial.clientes.index');
});

require __DIR__.'/settings.php';
