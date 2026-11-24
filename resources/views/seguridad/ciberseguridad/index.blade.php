<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NESS SOFT · SOC Ciberseguridad</title>
    <link rel="stylesheet" href="{{ asset('css/ciberseguridad.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="soc-body">
<div class="soc-shell">
    <!-- SIDEBAR IZQUIERDO SOC -->
    <aside class="soc-sidebar" aria-label="Navegación SOC">
        <div>
            <div class="soc-brand">
                <div class="soc-brand-logo">
                    <img src="{{ asset('img/NESSlogoopti.png') }}" alt="NESS SOFT"
                         onerror="this.parentElement.style.display='none';">
                </div>
                <div>
                    <div class="soc-brand-name">NESS SOFT</div>
                    <div class="soc-brand-tag">SOC COMMAND</div>
                </div>
            </div>

            <div class="soc-operator">
                <div class="soc-operator-top">
                    <span class="soc-pulse"></span>
                    <span class="soc-operator-status">OPERADOR ACTIVO</span>
                </div>
                <div class="soc-operator-user">
                    {{ auth()->user()->nombres ?? auth()->user()->username ?? 'Analista SOC' }}
                </div>
                <div class="soc-operator-role">
                    ROL: {{ optional(auth()->user()->rol)->codigo ?? 'ADSIS' }}
                </div>
            </div>

            <div class="soc-menu-label">Navegación del sistema</div>
            <nav class="soc-menu">
                <a href="{{ route('dashboard') }}" class="soc-menu-item">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Panel General</span>
                </a>

                @if (Route::has('seguridad.usuarios.index'))
                    <a href="{{ route('seguridad.usuarios.index') }}" class="soc-menu-item">
                        <i data-lucide="users"></i>
                        <span>Usuarios y roles</span>
                    </a>
                @endif

                <a href="{{ route('seguridad.ciberseguridad.index') }}" class="soc-menu-item is-active">
                    <i data-lucide="shield-check"></i>
                    <span>Monitoreo y Ciberseguridad</span>
                </a>
            </nav>
        </div>

        <div class="soc-sidebar-bottom">
            <div class="soc-integrity-pill">
                <i data-lucide="lock"></i>
                <span>SHA-256 Chain Active</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="soc-logout">
                    <i data-lucide="log-out"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- ÁREA PRINCIPAL SOC -->
    <main class="soc-main">
        <header class="soc-topbar">
            <div>
                <div class="soc-kicker">
                    <span class="soc-pulse"></span>
                    <span>TELEMETRÍA EN TIEMPO REAL · AUDITORÍA FORENSE</span>
                </div>
                <h1>Monitoreo de Seguridad y Ciberseguridad</h1>
                <p>Bitácora inmutable con hash SHA-256, intentos fallidos, bloqueos temporales y estado 2FA.</p>
            </div>

            <a href="{{ route('seguridad.ciberseguridad.index') }}" class="soc-btn-primary">
                <i data-lucide="refresh-cw"></i>
                <span>Actualizar bitácora</span>
            </a>
        </header>

        <!-- KPIS -->
        <section class="soc-kpis" aria-label="Métricas de seguridad">
            <article class="soc-kpi kpi-cyan">
                <div class="soc-kpi-head">
                    <span>Eventos hoy</span>
                    <i data-lucide="activity"></i>
                </div>
                <div class="soc-kpi-num">{{ $kpis['eventos_hoy'] ?? 0 }}</div>
            </article>

            <article class="soc-kpi kpi-crimson">
                <div class="soc-kpi-head">
                    <span>Eventos severidad ALTA</span>
                    <i data-lucide="flame"></i>
                </div>
                <div class="soc-kpi-num">{{ $kpis['alertas_altas'] ?? 0 }}</div>
            </article>

            <article class="soc-kpi kpi-amber">
                <div class="soc-kpi-head">
                    <span>Cuentas bloqueadas ahora</span>
                    <i data-lucide="shield-off"></i>
                </div>
                <div class="soc-kpi-num">{{ $kpis['cuentas_bloqueadas'] ?? 0 }}</div>
            </article>

            <article class="soc-kpi kpi-emerald">
                <div class="soc-kpi-head">
                    <span>Usuarios con 2FA activo</span>
                    <i data-lucide="key-round"></i>
                </div>
                <div class="soc-kpi-num">{{ $kpis['usuarios_con_2fa'] ?? 0 }}</div>
            </article>
        </section>

        <!-- FILTROS -->
        <section class="soc-card soc-filters-box" aria-label="Filtros de bitácora">
            <form id="sec-filtros-form" method="GET" action="{{ route('seguridad.ciberseguridad.index') }}" class="soc-filters-grid">
                <select name="tipo_evento" id="sec-tipo-evento" class="soc-control" aria-label="Tipo de evento">
                    <option value="">Todos los tipos</option>
                    <option value="SEGURIDAD" @selected(($filtros['tipo_evento'] ?? '') === 'SEGURIDAD')>SEGURIDAD</option>
                    <option value="TRANSACCION" @selected(($filtros['tipo_evento'] ?? '') === 'TRANSACCION')>TRANSACCION</option>
                </select>

                <select name="severidad" id="sec-severidad" class="soc-control" aria-label="Severidad">
                    <option value="">Todas las severidades</option>
                    <option value="ALTA" @selected(strtoupper((string) ($filtros['severidad'] ?? '')) === 'ALTA')>ALTA</option>
                    <option value="MEDIA" @selected(strtoupper((string) ($filtros['severidad'] ?? '')) === 'MEDIA')>MEDIA</option>
                    <option value="BAJA" @selected(strtoupper((string) ($filtros['severidad'] ?? '')) === 'BAJA')>BAJA</option>
                </select>

                <input type="text"
                       name="accion"
                       id="sec-accion"
                       value="{{ $filtros['accion'] ?? '' }}"
                       placeholder="Filtrar acción (ej. LOGIN_FALLIDO)"
                       class="soc-control">

                <div class="soc-filter-btns">
                    <button type="submit" class="soc-btn-primary">Filtrar</button>
                    <a href="{{ route('seguridad.ciberseguridad.index') }}" class="soc-btn-ghost">Limpiar</a>
                </div>
            </form>
        </section>

        <div id="sec-copy-feedback" class="soc-toast" role="status" aria-live="polite"></div>

        <!-- CUENTAS EN RIESGO -->
        <section class="soc-card">
            <div class="soc-card-head">
                <div>
                    <h2>Cuentas con intentos fallidos, bloqueos o inactividad</h2>
                    <p>Monitoreo preventivo de credenciales, bloqueos activos y estado de doble factor</p>
                </div>
                <span class="soc-chip">seguridad.usuarios</span>
            </div>

            <div class="soc-table-responsive">
                <table class="soc-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Intentos fallidos</th>
                            <th>Bloqueo hasta</th>
                            <th>2FA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuariosRiesgo as $u)
                            @php
                                $estado = strtoupper((string) ($u->estado ?? ''));
                                $bloqueoFmt = is_object($u->bloqueo_hasta ?? null) && method_exists($u->bloqueo_hasta, 'format')
                                    ? $u->bloqueo_hasta->format('Y-m-d H:i:s')
                                    : ($u->bloqueo_hasta ?? '—');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $u->username ?? '—' }}</strong>
                                    <div class="soc-subtext">{{ $u->email ?? '' }}</div>
                                </td>
                                <td><span class="soc-chip">{{ optional($u->rol)->codigo ?? 'N/A' }}</span></td>
                                <td>
                                    <span class="soc-badge {{ $estado === 'ACTIVO' ? 'badge-ok' : 'badge-alert' }}">
                                        {{ $u->estado ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="soc-mono text-amber">{{ (int) ($u->intentos_fallidos ?? 0) }}</td>
                                <td class="soc-mono text-crimson">{{ $bloqueoFmt }}</td>
                                <td>
                                    <span class="soc-badge {{ !empty($u->activo_2fa) ? 'badge-ok' : 'badge-warn' }}">
                                        {{ !empty($u->activo_2fa) ? 'Activo' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="soc-empty">Sin cuentas con alertas activas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- BITÁCORA INMUTABLE -->
        <section class="soc-card">
            <div class="soc-card-head">
                <div>
                    <h2>Eventos en seguridad.bitacora_eventos</h2>
                    <p>Registros protegidos contra edición o borrado mediante triggers en PostgreSQL</p>
                </div>
                <span class="soc-chip">SHA-256 Inmutable</span>
            </div>

            <div class="soc-table-responsive">
                <table class="soc-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tipo / Sev.</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Anterior → Nuevo</th>
                            <th>IP</th>
                            <th>Hash Integridad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventos as $ev)
                            @php
                                $sev = strtoupper((string) ($ev->severidad ?? 'BAJA'));
                                $sevClass = in_array($sev, ['ALTA', 'CRITICA'], true)
                                    ? 'badge-alert'
                                    : ($sev === 'MEDIA' ? 'badge-warn' : 'badge-ok');
                                $fechaEv = is_object($ev->fecha_evento ?? null) && method_exists($ev->fecha_evento, 'format')
                                    ? $ev->fecha_evento->format('Y-m-d H:i:s')
                                    : ($ev->fecha_evento ?? '—');
                                $hashFull = (string) ($ev->hash_integridad ?? '');
                            @endphp
                            <tr>
                                <td class="soc-mono">#{{ $ev->id_evento ?? '—' }}</td>
                                <td class="soc-mono">{{ $fechaEv }}</td>
                                <td>
                                    <span class="soc-chip">{{ $ev->tipo_evento ?? 'SEGURIDAD' }}</span>
                                    <span class="soc-badge {{ $sevClass }}">{{ $sev }}</span>
                                </td>
                                <td>{{ optional($ev->usuario)->username ?? 'Sistema' }}</td>
                                <td>
                                    <strong>{{ $ev->accion ?? 'EVENTO' }}</strong>
                                    @if(!empty($ev->observaciones))
                                        <div class="soc-subtext">{{ $ev->observaciones }}</div>
                                    @endif
                                </td>
                                <td class="soc-mono">
                                    {{ $ev->estado_anterior ?? '—' }} → {{ $ev->estado_nuevo ?? '—' }}
                                </td>
                                <td class="soc-mono">{{ $ev->direccion_ip ?? '—' }}</td>
                                <td class="soc-mono">
                                    @if($hashFull !== '')
                                        <div class="soc-hash-group">
                                            <code class="soc-hash-preview">{{ \Illuminate\Support\Str::limit($hashFull, 14) }}</code>
                                            <button type="button"
                                                    class="soc-copy-btn"
                                                    data-copy-hash="{{ $hashFull }}"
                                                    title="Copiar hash SHA-256 completo">
                                                Copiar
                                            </button>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="soc-empty">No hay eventos registrados con los filtros actuales.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($eventos, 'links'))
                <div class="soc-pagination">
                    {{ $eventos->links() }}
                </div>
            @endif
        </section>
    </main>
</div>

<script src="{{ asset('js/ciberseguridad.js') }}"></script>
</body>
</html>