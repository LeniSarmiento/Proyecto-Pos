<?php
/**
 * Vista de Mis Ventas del Vendedor (frontend)
 */
?>
<section class="hero" style="padding: var(--spacing-xl) 0;">
    <div class="container" style="text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: var(--spacing-sm);">Mis Ventas Registradas</h1>
        <p style="font-size: 1.125rem; opacity: 0.9;">
            Consulta el historial completo de tus transacciones realizadas en el sistema
        </p>
    </div>
</section>

<section style="padding: var(--spacing-2xl) 0;">
    <div class="container">
        <!-- Filtro Rápido -->
        <div class="card mb-lg">
            <div class="card-body">
                <input type="text" id="pos-search-sale" class="form-input" placeholder="🔍 Buscar por número de ticket o venta..." oninput="searchPosSales(event)">
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>IGV</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="pos-sales-body">
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                            Cargando historial de ventas...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    let posSales = [];

    async function loadPosSales() {
        try {
            const response = await fetch('actions/get_recent_sales.php');
            const result = await response.json();
            if (result.success) {
                posSales = result.data;
                renderPosSales(posSales);
            }
        } catch (error) {
            console.error(error);
        }
    }

    function renderPosSales(sales) {
        const tbody = document.getElementById('pos-sales-body');
        if (!tbody) return;

        if (sales.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No se registran ventas</td></tr>';
            return;
        }

        tbody.innerHTML = sales.map(s => `
            <tr>
                <td><strong>${s.sale_number}</strong></td>
                <td>${new Date(s.created_at).toLocaleDateString()} ${new Date(s.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</td>
                <td>${s.customer_name || 'Cliente Walk-in'}</td>
                <td><span style="text-transform: capitalize;">${s.payment_method}</span></td>
                <td>S/ ${parseFloat(s.tax).toFixed(2)}</td>
                <td><strong style="color: var(--color-primary);">${formatCurrency(s.total)}</strong></td>
                <td>
                    <span style="padding: 4px 8px; background: rgba(16,185,129,0.1); color: var(--color-success); border-radius:12px; font-size:0.75rem; font-weight:700;">
                        ${s.payment_status}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    function searchPosSales(event) {
        const query = event.target.value.toLowerCase();
        const filtered = posSales.filter(s =>
            s.sale_number.toLowerCase().includes(query) ||
            (s.customer_name && s.customer_name.toLowerCase().includes(query))
        );
        renderPosSales(filtered);
    }

    loadPosSales();
</script>