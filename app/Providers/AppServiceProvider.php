<?php

namespace App\Providers;

use App\Contracts\WhatsAppOtpSenderInterface;
use App\Services\Seguridad\MetaWhatsAppOtpSender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(WhatsAppOtpSenderInterface::class, MetaWhatsAppOtpSender::class);
    }

    public function boot(): void
    {
        Gate::define('ver-ciberseguridad', function ($usuario) {
            return strtoupper((string) optional($usuario->rol)->codigo) === 'ADSIS';
        });
    }
}