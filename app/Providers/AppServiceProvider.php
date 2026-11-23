<?php

namespace App\Providers;

use App\Contracts\WhatsAppOtpSenderInterface;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Usuario;
use App\Services\Seguridad\MetaWhatsAppOtpSender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->ensureEphemeralAppKey();

        $this->app->bind(WhatsAppOtpSenderInterface::class, MetaWhatsAppOtpSender::class);

        $this->app->singleton(CreatesNewUsers::class, function () {
            return new class implements CreatesNewUsers {
                public function create(array $input): Usuario
                {
                    Validator::make($input, [
                        'nombres' => ['required', 'string', 'max:150'],
                        'username' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:pgsql.seguridad.usuarios,username'],
                        'email' => ['required', 'string', 'email', 'max:150', 'unique:pgsql.seguridad.usuarios,email'],
                        'telefono' => ['nullable', 'string', 'max:30'],
                        'password' => ['required', 'confirmed', Password::min(8)],
                    ])->validate();

                    $rolInicial = Rol::where('codigo', 'ASC')->where('estado', true)->first()
                        ?? Rol::where('codigo', '<>', 'ADSIS')->where('estado', true)->orderBy('nivel_jerarquia')->first();

                    $hashArgon2id = Hash::driver('argon2id')->make($input['password'], [
                        'memory' => 65536,
                        'time' => 4,
                        'threads' => 1,
                    ]);

                    return Usuario::create([
                        'id_rol' => $rolInicial->id_rol,
                        'nombres' => trim($input['nombres']),
                        'username' => strtolower(trim($input['username'])),
                        'email' => strtolower(trim($input['email'])),
                        'password_hash' => $hashArgon2id,
                        'telefono' => !empty($input['telefono']) ? trim($input['telefono']) : null,
                        'estado' => 'ACTIVO',
                        'activo_2fa' => false,
                        'requiere_cambio_password' => false,
                    ]);
                }
            };
        });
    }

    public function boot(): void
    {
        Gate::define('ver-ciberseguridad', function ($usuario) {
            return strtoupper((string) optional($usuario->rol)->codigo) === 'ADSIS';
        });
    }

    private function ensureEphemeralAppKey(): void
    {
        $appKey = (string) config('app.key');

        if ($appKey === '') {
            config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        }
    }
}