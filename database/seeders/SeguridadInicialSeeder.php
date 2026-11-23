<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeguridadInicialSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['codigo' => 'ADSIS', 'nombre' => 'Administrador del Sistema', 'descripcion' => 'Seguridad, usuarios, roles y auditoría', 'nivel_jerarquia' => 100, 'estado' => true],
            ['codigo' => 'AC', 'nombre' => 'Administrador Comercial', 'descripcion' => 'Supervisión comercial global y postventa', 'nivel_jerarquia' => 50, 'estado' => true],
            ['codigo' => 'ASC', 'nombre' => 'Asesor Comercial', 'descripcion' => 'Gestión de cartera asignada', 'nivel_jerarquia' => 10, 'estado' => true],
        ];

        foreach ($roles as $rol) {
            DB::table('seguridad.roles')->updateOrInsert(
                ['codigo' => $rol['codigo']],
                $rol
            );
        }

        $idAdsis = DB::table('seguridad.roles')->where('codigo', 'ADSIS')->value('id_rol');

        $hashArgon2id = Hash::driver('argon2id')->make('NessAdmin2026!', [
            'memory' => 65536,
            'time' => 4,
            'threads' => 1,
        ]);

        DB::table('seguridad.usuarios')->updateOrInsert(
            ['username' => 'admin.ness'],
            [
                'id_rol' => $idAdsis,
                'nombres' => 'Administrador Sistema NESS',
                'email' => 'admin@nesssoft.local',
                'password_hash' => $hashArgon2id,
                'telefono' => '573001234567',
                'estado' => 'ACTIVO',
                'activo_2fa' => false,
                'requiere_cambio_password' => true,
            ]
        );
    }
}