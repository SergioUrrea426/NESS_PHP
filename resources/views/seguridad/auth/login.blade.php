@extends('layouts.ness')

@section('title', 'Iniciar Sesión - NESS SOFT')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="min-h-[82vh] flex items-center justify-center p-2 sm:p-4 rounded-3xl">
    <main class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-slate-200 text-slate-800">

        <header class="px-8 pt-8 pb-4 text-center">
            <div class="flex justify-center mb-3">
                <img src="{{ asset('img/NESSlogoopti.png') }}" alt="NESS SOFT Logo"
                     class="h-24 w-auto object-contain transition-all duration-300 hover:scale-105">
            </div>
            <h1 class="text-xl font-bold text-[#0b3b80] tracking-tight">CRM - CORPORATIVO</h1>
            <p class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-semibold">
                Bienvenido a la plataforma · Acceso Seguro
            </p>
        </header>

        <form id="loginForm" method="POST" action="{{ route('login.attempt') }}" class="px-8 pb-8 space-y-4" novalidate>
            @csrf

            @if (session('status'))
                <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-700 font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 font-medium">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <label for="username" class="block text-sm font-medium text-slate-700 mb-1">
                    Usuario o correo electrónico
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                           class="input-field block w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#00a2e8] focus:border-[#00a2e8] focus:bg-white @error('username') input-error @enderror"
                           placeholder="admin.ness o usuario@nesssoft.com">
                </div>
                <span id="usernameError" class="text-xs text-red-500 hidden mt-1">
                    Ingresa tu usuario o correo electrónico.
                </span>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Contraseña
                    </label>
                    <a href="mailto:soporte@nesssoft.com?subject=Recuperar%20contrase%C3%B1a"
                       class="text-xs font-semibold text-[#00a2e8] hover:text-[#0b3b80] transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <div class="relative rounded-lg shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="input-field block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#00a2e8] focus:border-[#00a2e8] focus:bg-white @error('password') input-error @enderror"
                           placeholder="••••••••">
                    <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                            aria-label="Mostrar u ocultar contraseña">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <span id="passwordError" class="text-xs text-red-500 hidden mt-1">
                    La contraseña no puede estar vacía.
                </span>
            </div>

            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center">
                    <input id="remember-me" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                           class="h-4 w-4 text-[#0b3b80] focus:ring-[#00a2e8] border-slate-300 rounded cursor-pointer">
                    <label for="remember-me" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                        Recordarme en este dispositivo
                    </label>
                </div>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full mt-2 bg-gradient-to-r from-[#0b3b80] to-[#00a2e8] hover:from-[#072758] hover:to-[#0b3b80] text-white font-medium py-2.5 px-4 rounded-lg shadow-lg shadow-[#0b3b80]/30 focus:outline-none focus:ring-2 focus:ring-[#00a2e8] focus:ring-offset-2 transition-all duration-200 text-sm flex items-center justify-center gap-2">
                <span id="btnText">Ingresar al Sistema</span>
                <i class="fa-solid fa-arrow-right text-xs" id="btnIcon"></i>
            </button>

            {{-- Acceso de Registro por Primera Vez --}}
            <div class="pt-1 text-center">
                <p class="text-xs text-slate-500 mb-2">¿Es tu primera vez en la plataforma?</p>
                <a href="{{ route('register.show') }}"
                   id="btnRegistroPrimeraVez"
                   class="w-full py-2.5 px-4 rounded-lg border border-[#0b3b80]/30 bg-slate-50 hover:bg-sky-50 text-[#0b3b80] hover:text-[#072758] font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-200">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Registrarse por primera vez</span>
                </a>
            </div>

            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">Ingreso Seguro</span>
                </div>
            </div>

            <div class="flex justify-center">
                <a href="{{ route('2fa.whatsapp.show') }}"
                   class="w-full group relative flex items-center justify-between px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-50/90 via-sky-100/60 to-slate-50/90 hover:from-sky-100 hover:via-sky-200/50 hover:to-sky-50 border border-sky-200/80 hover:border-sky-400/80 text-slate-700 shadow-sm hover:shadow-md hover:shadow-sky-500/10 transition-all duration-300">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-[#0b3b80] to-sky-600 text-white shadow-sm shadow-[#0b3b80]/20 group-hover:scale-105 transition-transform duration-200 shrink-0">
                        <i class="fa-solid fa-shield-halved text-xs"></i>
                    </span>
                    <div class="flex flex-col text-center mx-auto">
                        <span class="text-[11px] font-bold text-[#072758] uppercase tracking-wider group-hover:text-sky-700 transition-colors">
                            Autenticación 2FA
                        </span>
                        <span class="text-[10px] text-slate-500 font-medium group-hover:text-slate-600 transition-colors">
                            Doble Factor por WhatsApp
                        </span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] text-sky-500/70 group-hover:text-sky-600 group-hover:translate-x-0.5 transition-all duration-200 shrink-0"></i>
                </a>
            </div>
        </form>

        <footer class="px-8 py-4 bg-slate-50/80 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                ¿Necesitas acceso?
                <a href="mailto:soporte@nesssoft.com" class="font-semibold text-[#0b3b80] hover:text-[#00a2e8] transition-colors">
                    Contactar a soporte
                </a>
            </p>
        </footer>
    </main>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/login.js') }}" defer></script>
@endpush