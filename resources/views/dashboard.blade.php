<section class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon"><i data-lucide="users"></i></div>
        <div>
            <span class="dashboard-kpi-label">Clientes registrados</span>
            <h2 class="dashboard-kpi-value" id="dashboard-clientes-total">{{ number_format($kpis['clientes'] ?? 0, 0, ',', '.') }}</h2>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><i data-lucide="file-text"></i></div>
        <div>
            <span class="dashboard-kpi-label">Cotizaciones activas</span>
            <h2 class="dashboard-kpi-value" id="dashboard-cotizaciones-total">{{ number_format($kpis['cotizaciones'] ?? 0, 0, ',', '.') }}</h2>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><i data-lucide="target"></i></div>
        <div>
            <span class="dashboard-kpi-label">Oportunidades</span>
            <h2 class="dashboard-kpi-value" id="dashboard-oportunidades-total">{{ number_format($kpis['oportunidades'] ?? 0, 0, ',', '.') }}</h2>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon"><i data-lucide="dollar-sign"></i></div>
        <div>
            <span class="dashboard-kpi-label">Ventas del mes</span>
            <h2 class="dashboard-kpi-value" id="dashboard-ventas-total">$ {{ number_format($kpis['ventas'] ?? 0, 0, ',', '.') }}</h2>
        </div>
    </div>
</section>