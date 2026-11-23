<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NESS SOFT') — CRM y Seguridad</title>
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen antialiased">
    @auth
        <nav class="bg-slate-900 border-b border-slate-800 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="font-bold tracking-wider text-sky-400 text-lg">
                    NESS SOFT
                </a>
                <a href="{{ route('dashboard') }}"
                   class="text-sm {{ request()->routeIs('dashboard') ? 'text-white font-semibold' : 'text-slate-300 hover:text-white' }}">
                    Panel General
                </a>
                <a href="{{ route('seguridad.usuarios.index') }}"
                   class="text-sm {{ request()->routeIs('seguridad.usuarios.*') ? 'text-sky-400 font-semibold' : 'text-slate-300 hover:text-white' }}">
                    Usuarios y roles
                </a>
                @can('ver-ciberseguridad')
                    <a href="{{ route('seguridad.ciberseguridad.index') }}"
                       class="text-sm font-medium {{ request()->routeIs('seguridad.ciberseguridad.*') ? 'text-emerald-400' : 'text-slate-300 hover:text-white' }}">
                        Monitoreo y Ciberseguridad
                    </a>
                @endcan
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right">
                    <div class="text-sm font-semibold text-white">{{ auth()->user()->nombres }}</div>
                    <div class="text-xs text-slate-400">
                        Rol: {{ optional(auth()->user()->rol)->codigo ?? 'N/A' }}
                        @if(auth()->user()->activo_2fa)
                            · <span class="text-emerald-400">2FA WhatsApp Activo</span>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-rose-600/20 text-rose-300 border border-rose-500/40 hover:bg-rose-600/30 text-xs font-medium">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </nav>
    @endauth

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        @if (session('status'))
            <div class="mb-4 rounded-lg bg-sky-500/15 border border-sky-500/40 px-4 py-3 text-sm text-sky-200">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 rounded-lg bg-amber-500/15 border border-amber-500/40 px-4 py-3 text-sm text-amber-200">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-rose-500/15 border border-rose-500/40 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>