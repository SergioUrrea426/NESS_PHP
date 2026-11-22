<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('requires authentication to open the customer directory', function () {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

    $this->get(route('comercial.clientes.index'))
        ->assertRedirect(route('login'));
});

test('renders filtered customer records and summary counts', function () {
    config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

    DB::statement("ATTACH DATABASE ':memory:' AS comercial");

    Schema::create('comercial.clientes', function (Blueprint $table): void {
        $table->increments('id_cliente');
        $table->string('tipo_persona');
        $table->string('numero_documento');
        $table->string('nombres')->nullable();
        $table->string('apellidos')->nullable();
        $table->string('razon_social')->nullable();
        $table->string('correo');
        $table->string('telefono')->nullable();
        $table->integer('id_ubicacion')->nullable();
        $table->string('etapa_cliente');
        $table->timestamp('fecha_creacion')->nullable();
        $table->timestamp('fecha_actualizacion')->nullable();
    });

    DB::table('comercial.clientes')->insert([
        [
            'tipo_persona' => 'NATURAL',
            'numero_documento' => '123456789',
            'nombres' => 'Ada',
            'apellidos' => 'Lovelace',
            'razon_social' => null,
            'correo' => 'ada@example.test',
            'etapa_cliente' => 'PROSPECTO',
            'fecha_creacion' => '2026-01-01 10:00:00',
        ],
        [
            'tipo_persona' => 'JURIDICA',
            'numero_documento' => '900123456',
            'nombres' => null,
            'apellidos' => null,
            'razon_social' => 'Ada Enterprise',
            'correo' => 'contacto@example.test',
            'etapa_cliente' => 'ACTIVO',
            'fecha_creacion' => '2026-01-02 10:00:00',
        ],
    ]);

    $this->withoutMiddleware([
        Authenticate::class,
        EnsureEmailIsVerified::class,
    ]);

    $this->get(route('comercial.clientes.index', [
        'buscar' => 'ada',
        'tipo_persona' => 'NATURAL',
        'etapa_cliente' => 'PROSPECTO',
    ]))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Ada Enterprise')
        ->assertSee('1 resultado(s)')
        ->assertSee('ada@example.test');
});
