<?php

use App\Enums\Comercial\EtapaCliente;
use App\Models\Comercial\Clientes;

test('casts customer stage values to and from the backed enum', function () {
    $cliente = new Clientes;
    $cliente->setRawAttributes(['etapa_cliente' => 'PROSPECTO']);

    expect($cliente->etapa_cliente)->toBe(EtapaCliente::Prospecto);

    $cliente->etapa_cliente = EtapaCliente::EnRiesgo;

    expect($cliente->getAttributes()['etapa_cliente'])->toBe('EN_RIESGO');
});

test('uses the customer table custom timestamp columns', function () {
    $cliente = new Clientes;

    expect($cliente->getCreatedAtColumn())->toBe('fecha_creacion');
    expect($cliente->getUpdatedAtColumn())->toBe('fecha_actualizacion');
    expect($cliente->usesTimestamps())->toBeTrue();
});
