@extends('layouts.ness')

@section('title', 'NESS SOFT - Usuarios y roles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/Usuarios.css') }}">
@endpush

@section('content')
<div class="users-management">
    <header class="top-header">
        <div class="header-title">
            <span class="eyebrow"><i data-lucide="shield-check"></i> Seguridad y acceso</span>
            <h1>Usuarios y roles</h1>
            <p>Administra las cuentas, responsabilidades y estado de acceso del equipo NESS SOFT.</p>
        </div>
    </header>

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

    <form method="GET" action="{{ route('seguridad.usuarios.index') }}" class="controls-card">
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
                            <td>
                                {{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y H:i') : 'Sin acceso' }}
                            </td>
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
@endsection

@push('scripts')
    <script src="{{ asset('js/Usuarios.js') }}"></script>
@endpush