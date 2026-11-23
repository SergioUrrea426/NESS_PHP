<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NESS SOFT - Directorio de clientes</title>
    @vite(['resources/css/app.css', 'resources/js/pages/clientes.tsx'])
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="clients-page">
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
        <symbol id="icon-contact" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
            <circle cx="9" cy="10" r="2.5"></circle>
            <path d="M5.5 17a3.5 3.5 0 0 1 7 0M15 9h3M15 13h3"></path>
        </symbol>
        <symbol id="icon-badge-check" viewBox="0 0 24 24">
            <path d="m12 3 2.3 1.3 2.7-.1 1.3 2.3 2.3 1.3-.1 2.7L21 13l-1.3 2.3.1 2.7-2.3 1.3-1.3 2.3-2.7-.1L12 23l-2.3-1.3-2.7.1-1.3-2.3-2.3-1.3.1-2.7L2 13l1.3-2.3-.1-2.7 2.3-1.3 1.3-2.3 2.7.1z"></path>
            <path d="m8.5 12.5 2.2 2.2 4.8-5"></path>
        </symbol>
        <symbol id="icon-trending-up" viewBox="0 0 24 24">
            <path d="m3 17 6-6 4 4 8-8"></path>
            <path d="M15 7h6v6"></path>
        </symbol>
        <symbol id="icon-search" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="7"></circle>
            <path d="m20 20-4-4"></path>
        </symbol>
        <symbol id="icon-plus" viewBox="0 0 24 24">
            <path d="M12 5v14M5 12h14"></path>
        </symbol>
        <symbol id="icon-file-plus" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <path d="M14 2v6h6M12 12v6M9 15h6"></path>
        </symbol>
        <symbol id="icon-eye" viewBox="0 0 24 24">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
            <circle cx="12" cy="12" r="3"></circle>
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
                        <span>{{ $usuarioActual?->name ?? 'Administrador' }}</span>
                        <span class="role-badge">{{ $usuarioActual?->rol?->codigo ?? 'Usuario' }}</span>
                    </div>
                    <div class="user-status"><span class="status-dot"></span>En línea</div>
                </div>
            </div>

            <p class="nav-section-title">Navegación</p>
            <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="{{ route('dashboard') }}" class="nav-link">
                            <svg class="icon" aria-hidden="true"><use href="#icon-dashboard"></use></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('comercial.clientes.index') }}" class="nav-link active" aria-current="page">
                            <svg class="icon" aria-hidden="true"><use href="#icon-users"></use></svg>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <button type="button" class="nav-link">
                            <svg class="icon" aria-hidden="true"><use href="#icon-file-text"></use></svg>
                            <span>Cotizaciones</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="nav-link">
                            <svg class="icon" aria-hidden="true"><use href="#icon-target"></use></svg>
                            <span>Oportunidades</span>
                        </button>
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

    <main class="main-wrapper clients-management">
        <header class="top-header">
            <div class="header-title">
                <span class="eyebrow">
                    <svg class="icon" aria-hidden="true"><use href="#icon-contact"></use></svg>
                    Gestión comercial
                </span>
                <h1>Directorio de clientes</h1>
                <p>Consulta, segmenta y acompaña las cuentas registradas en NESS SOFT.</p>
            </div>
            <button type="button" class="btn-primary">
                <svg class="icon" aria-hidden="true"><use href="#icon-plus"></use></svg>
                Nuevo cliente
            </button>
        </header>

        <section class="client-summary-grid" aria-label="Resumen de clientes">
            <div class="client-summary-card">
                <span>Clientes registrados</span>
                <strong id="metric-clientes-total">{{ number_format($totalClientes, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-contact"></use></svg>
            </div>
            <div class="client-summary-card">
                <span>Clientes activos</span>
                <strong id="metric-clientes-activos">{{ number_format($clientesActivos, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-badge-check"></use></svg>
            </div>
            <div class="client-summary-card">
                <span>Prospectos</span>
                <strong id="metric-clientes-prospectos">{{ number_format($prospectos, 0, ',', '.') }}</strong>
                <svg class="summary-icon" aria-hidden="true"><use href="#icon-trending-up"></use></svg>
            </div>
        </section>

        <form
            id="clientes-filtros"
            method="GET"
            action="{{ route('comercial.clientes.index') }}"
            class="clients-controls"
            aria-label="Filtros de clientes"
        >
            <label class="search-box">
                <svg class="icon" aria-hidden="true"><use href="#icon-search"></use></svg>
                <span class="sr-only">Buscar clientes</span>
                <input
                    type="search"
                    id="search-cliente"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    placeholder="Buscar por NIT, nombre o correo..."
                >
            </label>

            <label class="filter-field">
                <span class="sr-only">Filtrar por personería</span>
                <select id="filter-personeria" name="tipo_persona" class="form-control">
                    <option value="">Todas las personerías</option>
                    <option value="JURIDICA" @selected(request('tipo_persona') === 'JURIDICA')>Persona jurídica</option>
                    <option value="NATURAL" @selected(request('tipo_persona') === 'NATURAL')>Persona natural</option>
                </select>
            </label>

            <label class="filter-field">
                <span class="sr-only">Filtrar por etapa</span>
                <select id="filter-estado" name="etapa_cliente" class="form-control">
                    <option value="">Todas las etapas</option>
                    @foreach (\App\Enums\Comercial\EtapaCliente::cases() as $etapa)
                        <option value="{{ $etapa->value }}" @selected(request('etapa_cliente') === $etapa->value)>
                            {{ ucfirst(strtolower(str_replace('_', ' ', $etapa->value))) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="btn-filter">Filtrar</button>
            <a href="{{ route('comercial.clientes.index') }}" class="btn-reset">Limpiar</a>
        </form>

        @if ($errors->any())
            <div class="clientes-alert error" role="alert">
                No se pudieron aplicar los filtros. Revisa los valores e inténtalo de nuevo.
            </div>
        @endif

        <section class="table-card client-table-card">
            <div class="table-heading">
                <div>
                    <h2>Cuentas comerciales</h2>
                    <p id="clientes-results">{{ number_format($clientes->total(), 0, ',', '.') }} resultado(s)</p>
                </div>
                <span class="data-source">Base de datos</span>
            </div>

            <div class="table-scroll">
                <table class="ness-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Contacto</th>
                            <th>Personería</th>
                            <th>Ubicación</th>
                            <th>Etapa</th>
                            <th><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody id="tabla-clientes-body">
                        @if ($clientes->isEmpty())
                            <tr>
                                <td colspan="7" class="empty-state">No hay clientes que coincidan con los filtros.</td>
                            </tr>
                        @else
                            @foreach ($clientes as $cliente)
                            @php
                                $nombreCliente = $cliente->razon_social
                                    ?: (trim(($cliente->nombres ?? '').' '.($cliente->apellidos ?? '')) ?: 'Sin nombre');
                                $statusClass = match ($cliente->etapa_cliente) {
                                    \App\Enums\Comercial\EtapaCliente::Activo,
                                    \App\Enums\Comercial\EtapaCliente::Fiel => 'client-status-active',
                                    \App\Enums\Comercial\EtapaCliente::Prospecto,
                                    \App\Enums\Comercial\EtapaCliente::Nuevo => 'client-status-prospect',
                                    \App\Enums\Comercial\EtapaCliente::EnRiesgo => 'client-status-risk',
                                    \App\Enums\Comercial\EtapaCliente::Inactivo => 'client-status-inactive',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $nombreCliente }}</strong>
                                    <small>{{ $cliente->correo ?: 'Sin correo' }}</small>
                                </td>
                                <td>
                                    {{ $cliente->numero_documento }}
                                    @if ($cliente->digito_verificacion)
- {{ $cliente->digito_verificacion }}
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $cliente->telefono ?: 'Sin teléfono' }}</div>
                                    <small>{{ $cliente->correo ?: 'Sin correo' }}</small>
                                </td>
                                <td>
                                    <span class="client-persona-pill">
                                        {{ $cliente->tipo_persona === 'JURIDICA' ? 'Persona jurídica' : 'Persona natural' }}
                                    </span>
                                </td>
                                <td>{{ $cliente->id_ubicacion ? 'Ubicación #'.$cliente->id_ubicacion : 'Sin ubicación' }}</td>
                                <td>
                                    <span class="client-status {{ $statusClass }}">
                                        {{ ucfirst(strtolower(str_replace('_', ' ', $cliente->etapa_cliente->value))) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="client-actions">
                                        <button
                                            class="icon-action"
                                            type="button"
                                            title="Nueva cotización"
                                            aria-label="Nueva cotización para {{ $nombreCliente }}"
                                        >
                                            <svg class="icon" aria-hidden="true"><use href="#icon-file-plus"></use></svg>
                                        </button>
                                        <button
                                            class="icon-action"
                                            type="button"
                                            title="Ver ficha"
                                            aria-label="Ver ficha de {{ $nombreCliente }}"
                                        >
                                            <svg class="icon" aria-hidden="true"><use href="#icon-eye"></use></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            @if ($clientes->hasPages())
                <div class="pagination">
                    {{ $clientes->links() }}
                </div>
            @endif
        </section>
    </main>
</body>
</html>