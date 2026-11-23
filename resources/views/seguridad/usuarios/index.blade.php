<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NESS SOFT - Usuarios y roles</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/Usuarios.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --ness-50: #eff8ff;
            --ness-100: #d5ecfc;
            --ness-500: #0284c7;
            --ness-600: #0369a1;
            --ness-800: #075985;
            --bg-main: #f8fbfe;
            --card-bg: #ffffff;
            --border-light: #d9eef8;
            --text-title: #0f172a;
            --text-muted: #64748b;
            --success: #16a34a;
            --danger: #dc2626;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-main);
            color: var(--text-title);
            min-height: 100vh;
            margin: 0;
        }
        .dashboard-shell {
            display: flex;
            min-height: 100vh;
            gap: 24px;
            padding: 16px;
        }
        .dashboard-sidebar {
            width: 245px;
            flex-shrink: 0;
            background: linear-gradient(180deg, #0b4f8a 0%, #062c4f 100%);
            color: #ffffff;
            border-radius: 24px;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 14px 32px rgba(6, 44, 79, 0.18);
        }
        .sidebar-logo-box {
            background: #e6f4fc;
            border-radius: 18px;
            padding: 14px 12px;
            text-align: center;
            margin-bottom: 16px;
        }
        .sidebar-logo-box img {
            max-width: 170px;
            max-height: 62px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: inline-block;
        }
        .sidebar-user {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 14px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .sidebar-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.16);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .sidebar-user-name { font-size: 0.85rem; font-weight: 700; line-height: 1.2; }
        .sidebar-badge {
            background: #0284c7;
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 6px;
            margin-left: 4px;
            vertical-align: middle;
        }
        .sidebar-status { font-size: 0.75rem; color: #86efac; margin-top: 2px; }
        .sidebar-section-title {
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            color: rgba(255, 255, 255, 0.6);
            padding: 0 10px;
            margin: 12px 0 6px;
            text-transform: uppercase;
        }
        .sidebar-nav { display: flex; flex-direction: column; gap: 4px; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
        }
        .sidebar-link:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
        .sidebar-link.active {
            background: #0ea5e9;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 6px 16px rgba(14, 165, 233, 0.35);
        }
        .usuarios-sidebar-footer {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            padding-top: 12px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.75);
        }
        .usuarios-sidebar-footer strong { display: block; font-size: 0.82rem; color: #fff; }
        .usuarios-logout-form { width: 100%; margin: 0; }
        .btn-logout-inline {
            width: 100%;
            background: rgba(239, 68, 68, 0.18);
            color: #fecaca;
            border: 1px solid rgba(248, 113, 113, 0.35);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .btn-logout-inline:hover { background: rgba(239, 68, 68, 0.32); color: #fff; }

        .dashboard-main {
            flex: 1;
            min-width: 0;
            padding: 8px 12px 24px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border: none;
            border-radius: 999px;
            padding: 9px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-secondary {
            background: #ffffff;
            color: var(--text-title);
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 0.82rem;
            text-decoration: none;
        }
        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #d5e6f2;
            border-radius: 999px;
            padding: 8px 14px;
        }
        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 0.86rem;
            color: var(--text-title);
        }
        .form-control {
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #ffffff;
            color: var(--text-title);
            font-size: 0.85rem;
        }
        .ness-table { width: 100%; border-collapse: collapse; }
        .ness-table th {
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-light);
            background: #f8fbfe;
        }
        .ness-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #edf5fa;
            font-size: 0.88rem;
            vertical-align: middle;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-done { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }

        @media (max-width: 900px) {
            .dashboard-shell { flex-direction: column; }
            .dashboard-sidebar { width: 100%; border-radius: 20px; }
        }
    </style>
</head>
<body>
<div class="dashboard-shell">
    <aside class="dashboard-sidebar">
        <div>
            <div class="sidebar-logo-box">
                <img
                    src="{{ asset('img/NESSlogoopti.png') }}"
                    alt="NESS SOFT"
                    onerror="this.style.display='none'; if (this.nextElementSibling) this.nextElementSibling.style.display='block';"
                >
                <span style="display:none; font-weight:800; color:#075985;">NESS SOFT</span>
            </div>

            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><i data-lucide="user"></i></div>
                <div>
                    <div class="sidebar-user-name">
                        {{ auth()->user()->nombres ?? auth()->user()->username ?? 'Administrador' }}
                        <span class="sidebar-badge">{{ optional(auth()->user()->rol)->codigo ?? 'ADSIS' }}</span>
                    </div>
                    <div class="sidebar-status">● En línea</div>
                </div>
            </div>

            <div class="sidebar-section-title">Navegación</div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
                <a href="{{ Route::has('comercial.clientes.index') ? route('comercial.clientes.index') : '#' }}" class="sidebar-link"><i data-lucide="users"></i><span>Clientes</span></a>
                <a href="#" class="sidebar-link"><i data-lucide="file-text"></i><span>Cotizaciones</span></a>
                <a href="#" class="sidebar-link"><i data-lucide="target"></i><span>Oportunidades</span></a>
            </nav>

            <div class="sidebar-section-title">Configuración</div>
            <nav class="sidebar-nav">
                <a href="{{ route('seguridad.usuarios.index') }}" class="sidebar-link active"><i data-lucide="user-cog"></i><span>Usuarios y roles</span></a>
                @if (Route::has('seguridad.ciberseguridad.index'))
                    <a href="{{ route('seguridad.ciberseguridad.index') }}" class="sidebar-link"><i data-lucide="shield-check"></i><span>Seguridad y Permisos</span></a>
                @endif
            </nav>
        </div>

        <div class="usuarios-sidebar-footer">
            <div>
                <strong>NESS SOFT</strong>
                <span>v1.0 Producción</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="usuarios-logout-form">
                @csrf
                <button type="submit" class="btn-logout-inline">Cerrar sesión</button>
            </form>
        </div>
    </aside>

    <main class="dashboard-main">
        <div class="users-management">
            <header class="top-header">
                <div class="header-title">
                    <span class="eyebrow"><i data-lucide="shield-check"></i> Seguridad y acceso</span>
                    <h1>Usuarios y roles</h1>
                    <p>Administra las cuentas, responsabilidades y estado de acceso del equipo NESS SOFT.</p>
                </div>
            </header>

            @if (session('status'))
                <div class="users-alert success" role="status" aria-live="polite">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="users-alert info" role="alert" aria-live="polite">
                    {{ session('error') }}
                </div>
            @endif

            <section class="user-summary-grid" aria-label="Resumen de usuarios">
                <div class="user-summary-card">
                    <span class="summary-label">Total de cuentas</span>
                    <strong id="metric-total">{{ $metricas['total'] ?? 0 }}</strong>
                    <i data-lucide="users"></i>
                </div>
                <div class="user-summary-card">
                    <span class="summary-label">Cuentas activas</span>
                    <strong id="metric-active">{{ $metricas['activas'] ?? 0 }}</strong>
                    <i data-lucide="user-check"></i>
                </div>
                <div class="user-summary-card">
                    <span class="summary-label">Cuentas inactivas</span>
                    <strong id="metric-inactive">{{ $metricas['inactivas'] ?? 0 }}</strong>
                    <i data-lucide="user-x"></i>
                </div>
            </section>

            <form method="GET" action="{{ route('seguridad.usuarios.index') }}" class="controls-card" id="form-filtros-usuarios">
                <div class="search-box">
                    <i data-lucide="search"></i>
                    <input type="search" id="input-search" name="search"
                           value="{{ $filtros['search'] ?? '' }}"
                           placeholder="Buscar por nombre, usuario o correo...">
                </div>
                <select id="select-rol" name="id_rol" class="form-control" aria-label="Filtrar por rol">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" {{ (string) ($filtros['id_rol'] ?? '') === (string) $rol->id_rol ? 'selected' : '' }}>
                            {{ $rol->codigo }} - {{ $rol->nombre }}
                        </option>
                    @endforeach
                </select>
                <select id="select-estado" name="estado" class="form-control" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO" {{ ($filtros['estado'] ?? '') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                    <option value="INACTIVO" {{ ($filtros['estado'] ?? '') === 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                </select>
                <button type="submit" class="btn-primary">Filtrar</button>
                @if (!empty($filtros['search']) || !empty($filtros['id_rol']) || !empty($filtros['estado']))
                    <a href="{{ route('seguridad.usuarios.index') }}" class="btn-secondary">Limpiar</a>
                @endif
            </form>

            <section class="table-card">
                <div class="table-heading">
                    <div>
                        <h2>Directorio de cuentas</h2>
                        <p id="results-label">{{ $usuarios->count() }} resultado{{ $usuarios->count() === 1 ? '' : 's' }} encontrado{{ $usuarios->count() === 1 ? '' : 's' }}</p>
                    </div>
                    <span class="data-source">PostgreSQL · seguridad.usuarios</span>
                </div>
                <div class="table-scroll">
                    <table class="ness-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Canales</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-usuarios-body">
                            @forelse ($usuarios as $usuario)
                                @php
                                    $estaActivo = strtoupper((string) $usuario->estado) === 'ACTIVO';
                                    $ultimoAcceso = $usuario->ultimo_acceso;
                                    $fechaFormateada = is_object($ultimoAcceso) && method_exists($ultimoAcceso, 'format')
                                        ? $ultimoAcceso->format('d/m/Y H:i')
                                        : ($ultimoAcceso ? (string) $ultimoAcceso : 'Sin acceso');
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $usuario->nombres }}</strong>
                                        <small>{{ '@' . $usuario->username }}</small>
                                    </td>
                                    <td>{{ $usuario->email ?? 'Sin correo' }}</td>
                                    <td>{{ $usuario->telefono ?? 'Sin teléfono' }}</td>
                                    <td>
                                        <span class="role-pill">{{ optional($usuario->rol)->codigo ?? 'N/A' }}</span>
                                        <small>{{ optional($usuario->rol)->nombre ?? 'Rol asignado' }}</small>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $estaActivo ? 'badge-done' : 'badge-danger' }}">
                                            {{ $estaActivo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $fechaFormateada }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('seguridad.usuarios.toggle-estado', $usuario) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="icon-action {{ $estaActivo ? 'danger' : 'success' }}"
                                                    type="submit"
                                                    title="{{ $estaActivo ? 'Desactivar cuenta' : 'Activar cuenta' }}">
                                                <i data-lucide="{{ $estaActivo ? 'user-x' : 'user-check' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="empty-state">No hay usuarios que coincidan con los filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</div>

<script src="{{ asset('js/Usuarios.js') }}"></script>
<script>lucide.createIcons();</script>
</body>
</html>