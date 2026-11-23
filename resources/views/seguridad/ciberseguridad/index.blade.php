@extends('layouts.ness')

@section('title', 'NESS SOFT - Monitoreo y Ciberseguridad')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ciberseguridad.css') }}">
@endpush

@section('content')
<div class="sec-monitor">
    <header class="top-header">
        <div class="header-title">
            <span class="eyebrow"><i data-lucide="shield-alert"></i> Auditoría e integridad</span>
            <h1>Monitoreo de Seguridad y Ciberseguridad</h1>
            <p>Bitácora inmutable con hash SHA-256, intentos fallidos, bloqueos temporales y estado 2FA.</p>
        </div>
        <a href="{{ route('seguridad.ciberseguridad.index') }}" class="btn-primary">
            <i data-lucide="refresh-cw"></i> Actualizar bitácora
        </a>
    </header>

    <section class="kpi-grid" aria-label="Indicadores de ciberseguridad">
        <div class="kpi-card">
            <div class="sec-kpi-label">Eventos hoy</div>
            <div class="sec-kpi-value sec-text-sky">{{ $kpis['eventos_hoy'] ?? 0 }}</div>
        </div>
        <div class="kpi-card">
            <div class="sec-kpi-label">Eventos severidad ALTA</div>
            <div class="sec-kpi-value sec-text-rose">{{ $kpis['alertas_altas'] ?? 0 }}</div>
        </div>
        <div class="kpi-card">
            <div class="sec-kpi-label">Cuentas bloqueadas ahora</div>
            <div class="sec-kpi-value sec-text-amber">{{ $kpis['cuentas_bloqueadas'] ?? 0 }}</div>
        </div>
        <div class="kpi-card">
            <div class="sec-kpi-label">Usuarios con 2FA activo</div>
            <div class="sec-kpi-value sec-text-emerald">{{ $kpis['usuarios_con_2fa'] ?? 0 }}</div>
        </div>
    </section>

    <form method="GET" action="{{ route('seguridad.ciberseguridad.index') }}" id="sec-filtros-form" class="controls-card">
        <select name="tipo_evento" id="sec-tipo-evento" class="form-control" aria-label="Filtrar por tipo de evento">
            <option value="">Todos los tipos</option>
            <option value="SEGURIDAD" @selected(($filtros['tipo_evento'] ?? '') === 'SEGURIDAD')>SEGURIDAD</option>
            <option value="TRANSACCION" @selected(($filtros['tipo_evento'] ?? '') === 'TRANSACCION')>TRANSACCION</option>
        </select>

        <select name="severidad" id="sec-severidad" class="form-control" aria-label="Filtrar por severidad">
            <option value="">Todas las severidades</option>
            <option value="ALTA" @selected(($filtros['severidad'] ?? '') === 'ALTA')>ALTA</option>
            <option value="MEDIA" @selected(($filtros['severidad'] ?? '') === 'MEDIA')>MEDIA</option>
            <option value="BAJA" @selected(($filtros['severidad'] ?? '') === 'BAJA')>BAJA</option>
        </select>

        <input type="text"
               name="accion"
               id="sec-accion"
               value="{{ $filtros['accion'] ?? '' }}"
               placeholder="Filtrar acción (ej. LOGIN_FALLIDO)"
               class="form-control">

        <div class="sec-filter-actions">
            <button type="submit" class="btn-primary">Filtrar</button>
            <a href="{{ route('seguridad.ciberseguridad.index') }}" class="sec-btn-secondary">Limpiar</a>
        </div>
    </form>

    <div id="sec-copy-feedback" class="sec-toast" role="status" aria-live="polite"></div>

    <section class="table-card sec-section-gap">
        <div class="table-heading">
            <div>
                <h2>Cuentas con intentos fallidos, bloqueos o inactividad</h2>
                <p>Monitoreo preventivo de credenciales, bloqueos activos y estado de doble factor</p>
            </div>
            <span class="data-source">seguridad.usuarios</span>
        </div>
        <div class="table-scroll">
            <table class="ness-table">
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
                        <tr>
                            <td>
                                <strong>{{ $u->username }}</strong>
                                <small>{{ $u->email }}</small>
                            </td>
                            <td><span class="role-pill">{{ optional($u->rol)->codigo ?? 'N/A' }}</span></td>
                            <td>
                                <span class="status-badge {{ strtoupper((string) $u->estado) === 'ACTIVO' ? 'badge-done' : 'badge-danger' }}">
                                    {{ $u->estado }}
                                </span>
                            </td>
                            <td class="sec-mono sec-text-amber">{{ $u->intentos_fallidos }}</td>
                            <td class="sec-mono sec-text-rose">
                                {{ $u->bloqueo_hasta ? $u->bloqueo_hasta->format('Y-m-d H:i:s') : '—' }}
                            </td>
                            <td>{{ $u->activo_2fa ? 'Activo' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">Sin cuentas con alertas activas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="table-card">
        <div class="table-heading">
            <div>
                <h2>Eventos en seguridad.bitacora_eventos</h2>
                <p>Registros protegidos contra edición o borrado mediante triggers en PostgreSQL</p>
            </div>
            <span class="data-source">SHA-256 Inmutable</span>
        </div>
        <div class="table-scroll">
            <table class="ness-table">
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
                            $sev = strtoupper((string) $ev->severidad);
                        @endphp
                        <tr>
                            <td class="sec-mono">#{{ $ev->id_evento }}</td>
                            <td class="sec-mono">{{ optional($ev->fecha_evento)->format('Y-m-d H:i:s') ?? '—' }}</td>
                            <td>
                                <span class="role-pill">{{ $ev->tipo_evento }}</span>
                                <span class="sec-sev {{ $sev === 'ALTA' ? 'sec-sev-alta' : ($sev === 'MEDIA' ? 'sec-sev-media' : 'sec-sev-baja') }}">
                                    {{ $ev->severidad }}
                                </span>
                            </td>
                            <td>{{ optional($ev->usuario)->username ?? 'Sistema' }}</td>
                            <td><strong>{{ $ev->accion }}</strong></td>
                            <td class="sec-mono">
                                {{ $ev->estado_anterior ?? '—' }} → {{ $ev->estado_nuevo ?? '—' }}
                            </td>
                            <td class="sec-mono">{{ $ev->direccion_ip ?? '—' }}</td>
                            <td class="sec-mono">
                                @if($ev->hash_integridad)
                                    <button type="button"
                                            class="sec-hash-btn"
                                            data-copy-hash="{{ $ev->hash_integridad }}"
                                            title="Copiar hash SHA-256 completo">
                                        {{ substr($ev->hash_integridad, 0, 14) }}…
                                    </button>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">No hay eventos registrados con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sec-pagination">
            {{ $eventos->links() }}
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/ciberseguridad.js') }}"></script>
@endpush