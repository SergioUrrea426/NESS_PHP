<div class="soc-layout-shell">
    <aside class="soc-sidebar" aria-label="Navegación principal">
        <div>
            <div class="soc-brand-box">
                <div class="soc-brand-logo-wrap">
                    <img src="{{ asset('img/NESSlogoopti.png') }}" alt="NESS SOFT" onerror="this.parentElement.style.display='none';">
                </div>
                <div>
                    <div class="soc-brand-title">NESS SOFT</div>
                    <div class="soc-brand-sub">Security &amp; Ops</div>
                </div>
            </div>

            <div class="soc-operator-card">
                <div class="soc-operator-name">
                    {{ auth()->user()->nombres ?? auth()->user()->username ?? 'Operador' }}
                </div>
                <div class="soc-operator-meta">
                    <span>● En línea</span>
                    <span class="soc-role-pill">Rol: {{ optional(auth()->user()->rol)->codigo ?? 'ADSIS' }}</span>
                </div>
            </div>

            <div class="soc-nav-group-label">Módulos</div>
            <nav class="soc-nav-list">
                <a href="{{ route('dashboard') }}" class="soc-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Panel General</span>
                </a>

                @if (Route::has('seguridad.usuarios.index'))
                    <a href="{{ route('seguridad.usuarios.index') }}" class="soc-nav-link {{ request()->routeIs('seguridad.usuarios.*') ? 'is-active' : '' }}">
                        <i data-lucide="users"></i>
                        <span>Usuarios y roles</span>
                    </a>
                @endif

                @if (Route::has('seguridad.ciberseguridad.index'))
                    <a href="{{ route('seguridad.ciberseguridad.index') }}" class="soc-nav-link {{ request()->routeIs('seguridad.ciberseguridad.*') ? 'is-active' : '' }}">
                        <i data-lucide="shield-check"></i>
                        <span>Monitoreo y Ciberseguridad</span>
                    </a>
                @endif
            </nav>
        </div>

        <div class="soc-sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="soc-logout-btn">
                    <i data-lucide="log-out"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="soc-main-area">
        @yield('content')
    </main>
</div>