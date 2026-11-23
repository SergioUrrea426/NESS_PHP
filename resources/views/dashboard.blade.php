<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NESS SOFT - Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="clients-page dashboard-page">
    <svg class="icon-sprite" aria-hidden="true" focusable="false">
        <symbol id="icon-user" viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M5 21a7 7 0 0 1 14 0"></path>
        </symbol>
        <symbol id="icon-dashboard" viewBox="0 0 24 24">
            <rect x="3" y="3" width="8" height="8" rx="1.5"></rect>
            <rect x="13" y="3" width="8" height="5" rx="1.5"></rect>
            <rect x="13" y="10" width="8" height="11" rx="1.5"></rect>
            <rect x="3" y="13" width="8" height="8" rx="1.5"></rect>
        </symbol>
        <symbol id="icon-users" viewBox="0 0 24 24">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>
        </symbol>
        <symbol id="icon-file-text" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <path d="M14 2v6h6M8 13h8M8 17h8"></path>
        </symbol>
        <symbol id="icon-target" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"></circle>
            <circle cx="12" cy="12" r="5"></circle>
            <circle cx="12" cy="12" r="1"></circle>
        </symbol>
        <symbol id="icon-dollar-sign" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </symbol>
        <symbol id="icon-user-cog" viewBox="0 0 24 24">
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M2 21v-2a7 7 0 0 1 10.5-6.06M19.4 15a1.7 1.7 0 0 0-2.8 0l-.2.4a1.7 1.7 0 0 1-1.6 1l-.5-.1a1.7 1.7 0 0 0-1.4 2.4l.2.4a1.7 1.7 0 0 1 0 1.8l-.2.4a1.7 1.7 0 0 0 1.4 2.4l.5-.1a1.7 1.7 0 0 1 1.6 1l.2.4a1.7 1.7 0 0 0 2.8 0l.2-.4a1.7 1.7 0 0 1 1.6-1l.5.1a1.7 1.7 0 0 0 1.4-2.4l-.2-.4a1.7 1.7 0 0 1 0-1.8l.2-.4a1.7 1.7 0 0 0-1.4-2.4l-.5.1a1.7 1.7 0 0 1-1.6-1z"></path>
            <circle cx="18" cy="19" r="1.5"></circle>
        </symbol>
        <symbol id="icon-shield-check" viewBox="0 0 24 24">
            <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"></path>
            <path d="m9 12 2 2 4-4"></path>
        </symbol>
        <symbol id="icon-shield" viewBox="0 0 24 24">
            <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"></path>
        </symbol>
        <symbol id="icon-search" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7"></circle>
            <path d="m20 20-4-4"></path>
        </symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14"></path>
        </symbol>
        <symbol id="icon-log-out" viewBox="0 0 24 24">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
        </symbol>
    </svg>

    @php
        $usuarioActual = auth()->user();
    @endphp

    <aside class="sidebar" aria-label="Navegación principal">
        <div class="sidebar-main">
            <div class="brand-container">
                <img
                    src="{{ asset('img/NESSlogoopti.png') }}"
                    alt="NESS SOFT"
                    class="brand-logo-img"
                    onerror="this.style.display='none';"
                >
            </div>

            <div class="user-card">
                <div class="user-avatar">
                    <svg class="icon" aria-hidden="true"><use href="#icon-user"></use></svg>
                </div>
                <div class="user-info">
                    <div class="name">
                        <span>{{ $usuarioActual?->nombres ?? $usuarioActual?->name ?? 'Administrador' }}</span>
                        <span class="role-badge">{{ $usuarioActual?->rol?->codigo ?? 'Usuario' }}</span>
                    </div>
                    <div class="user-status"><span class="status-dot"></span>En línea</div>
                </div>
            </div>

            <p class="nav-section-title">Navegación</p>
            <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="{{ route('dashboard') }}" class="nav-link active" aria-current="page">
                            <svg class="icon" aria-hidden="true"><use href="#icon-dashboard"></use></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('comercial.clientes.index') }}" class="nav-link">
                            <svg class="icon" aria-hidden="true"><use href="#icon-users"></use></svg>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('comercial.clientes.index') }}" class="nav-link">
                            <svg class="icon" aria-hidden="true"><use href="#icon-file-text"></use></svg>
                            <span>Cotizaciones</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('comercial.clientes.index') }}" class="nav-link">
                            <svg class="icon" aria-hidden="true"><use href="#icon-target"></use></svg>
                            <span>Oportunidades</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <p class="nav-section-title">Configuración</p>
            <nav>
                <ul class="nav-menu">
                    @if (Route::has('seguridad.usuarios.index'))
                        <li>
                            <a href="{{ route('seguridad.usuarios.index') }}" class="nav-link">
                                <svg class="icon" aria-hidden="true"><use href="#icon-user-cog"></use></svg>
                                <span>Usuarios y roles</span>
                            </a>
                        </li>
                    @endif
                    @if (Route::has('seguridad.ciberseguridad.index'))
                        <li>
                            <a href="{{ route('seguridad.ciberseguridad.index') }}" class="nav-link">
                                <svg class="icon" aria-hidden="true"><use href="#icon-shield-check"></use></svg>
                                <span>Seguridad y permisos</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

        <div class="sidebar-footer-stack">
            <div class="sidebar-footer">
                <svg class="icon" aria-hidden="true"><use href="#icon-shield"></use></svg>
                <div>
                    <h4>NESS SOFT</h4>
                    <p>v1.0 Producción</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="btn-sidebar-logout">
                    <svg class="icon" aria-hidden="true"><use href="#icon-log-out"></use></svg>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-wrapper clients-management dashboard-main">
        <header class="top-header">
            <div class="header-title">
                <span class="eyebrow">
                    <svg class="icon" aria-hidden="true"><use href="#icon-dashboard"></use></svg>
                    Panel general
                </span>
                <h1>Dashboard</h1>
                <p>Resumen general del estado del sistema NESS SOFT.</p>
            </div>

            <div class="dashboard-header-controls">
                <label class="search-box dashboard-search-box">
                    <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                    <span class="sr-only">Buscar en el dashboard</span>
                    <input
                        type="search"
                        id="dashboard-search"
                        placeholder="Buscar clientes, cotizaciones, oportunidades..."
                    >
                </label>

                <a href="{{ route('comercial.clientes.index') }}" class="btn-primary">
                    <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                    <span>Nuevo registro</span>
                </a>
            </div>
        </header>

        <section class="client-summary-grid" aria-label="Indicadores generales">
            <div class="client-summary-card">
                <span>Clientes registrados</span>
                <strong id="dashboard-clientes-total">{{ number_format($kpis['clientes'] ?? 0, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-users"></use></svg>
            </div>

            <div class="client-summary-card">
                <span>Cotizaciones activas</span>
                <strong id="dashboard-cotizaciones-total">{{ number_format($kpis['cotizaciones'] ?? 0, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-file-text"></use></svg>
            </div>

            <div class="client-summary-card">
                <span>Oportunidades</span>
                <strong id="dashboard-oportunidades-total">{{ number_format($kpis['oportunidades'] ?? 0, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-target"></use></svg>
            </div>

            <div class="client-summary-card">
                <span>Ventas del mes</span>
                <strong id="dashboard-ventas-total">$ {{ number_format($kpis['ventas'] ?? 0, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-dollar-sign"></use></svg>
            </div>
        </section>

        <section class="dashboard-panels-grid" aria-label="Accesos rápidos">
            <a href="{{ route('comercial.clientes.index') }}" class="client-summary-card dashboard-info-card">
                <span>Gestión comercial</span>
                <strong>Resumen de clientes recientes</strong>
                <p class="dashboard-card-desc">Accede al directorio de cuentas comerciales.</p>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-users"></use></svg>
            </a>

            <a href="{{ Route::has('seguridad.usuarios.index') ? route('seguridad.usuarios.index') : '#' }}" class="client-summary-card dashboard-info-card">
                <span>Administración de accesos</span>
                <strong>Gestión de usuarios y roles</strong>
                <p class="dashboard-card-desc">Administra cuentas, roles y estado de acceso del equipo.</p>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-user-cog"></use></svg>
            </a>
        </section>
    </main>

    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>