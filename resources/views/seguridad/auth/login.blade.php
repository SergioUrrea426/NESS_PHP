@extends('layouts.ness')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">
        <div class="mb-6 text-center">
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-sky-500/15 text-sky-300 border border-sky-500/30 mb-2">
                NESS SOFT · ACCESO SEGURO
            </span>
            <h1 class="text-2xl font-bold text-white">Iniciar Sesión</h1>
            <p class="text-sm text-slate-400 mt-1">
                Autenticación con Argon2id, bloqueo por intentos y 2FA por WhatsApp.
            </p>
        </div>

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-1">
                    Usuario o correo electrónico
                </label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                       class="w-full rounded-xl bg-slate-950 border border-slate-700 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">
                    Contraseña
                </label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-xl bg-slate-950 border border-slate-700 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none">
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-sm transition">
                Continuar
            </button>
        </form>
    </div>
</div>
@endsection