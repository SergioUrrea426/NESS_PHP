@extends('layouts.ness')

@section('title', 'Registro de Primera Vez - NESS SOFT')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="min-h-[82vh] flex items-center justify-center p-4 rounded-3xl">
    <main class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-slate-200">
        <header class="px-8 pt-8 pb-4 text-center">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('img/NESSlogoopti.png') }}" alt="NESS SOFT Logo" class="h-20 w-auto object-contain">
            </div>
            <h1 class="text-xl font-bold text-[#0b3b80] tracking-tight">Registro de Primera Vez</h1>
            <p class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-semibold">
                NESS SOFT · Alta de Cuenta
            </p>
        </header>

        @if ($errors->any())
            <div class="mx-8 mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="px-8 pb-8 space-y-4">
            @csrf

            <div>
                <label for="nombres" class="block text-sm font-medium text-slate-700 mb-1">Nombres completos</label>
                <input type="text" id="nombres" name="nombres" value="{{ old('nombres') }}" required
                       placeholder="Ej. Carlos Andrés Pérez"
                       class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0b3b80]">
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Nombre de usuario</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required
                       placeholder="Ej. carlos.perez"
                       class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0b3b80]">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       placeholder="carlos.perez@nesssoft.local"
                       class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0b3b80]">
            </div>

            <div>
                <label for="telefono" class="block text-sm font-medium text-slate-700 mb-1">Teléfono / WhatsApp</label>
                <input type="text" id="telefono" name="telefono" value="{{ old('telefono') }}"
                       placeholder="Ej. 573001234567"
                       class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0b3b80]">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
                <input type="password" id="password" name="password" required
                       placeholder="Mínimo 8 caracteres"
                       class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0b3b80]">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirmar contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="Repite tu contraseña"
                       class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#0b3b80]">
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-lg bg-[#0b3b80] hover:bg-[#092d61] text-white text-sm font-semibold shadow transition cursor-pointer">
                Crear cuenta
            </button>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-medium text-[#0b3b80] hover:underline">
                    ¿Ya tienes cuenta? Inicia sesión
                </a>
            </div>
        </form>
    </main>
</div>
@endsection