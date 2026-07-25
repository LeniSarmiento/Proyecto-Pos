<?php
/**
 * Vista de Inicio del Panel Admin
 * Resumen de estadísticas y ventas recientes
 */
?>
<div style="margin-bottom: var(--spacing-2xl);">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-sm);">Dashboard</h1>
    <p class="text-muted">Resumen general de tu negocio</p>
</div>

<!-- Estadísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">VENTAS HOY</div>
        <div class="stat-value" id="stat-sales-today">S/ 0.00</div>
        <div class="text-muted" style="font-size: 0.875rem; margin-top: var(--spacing-xs);" id="stat-sales-today-count">0 transacciones</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-label">VENTAS ESTE MES</div>
        <div class="stat-value" id="stat-sales-month">S/ 0.00</div>
        <div class="text-muted" style="font-size: 0.875rem; margin-top: var(--spacing-xs);">En crecimiento</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-label">PRODUCTOS BAJO STOCK</div>
        <div class="stat-value" id="stat-low-stock">0</div>
        <div class="text-muted" style="font-size: 0.875rem; margin-top: var(--spacing-xs);">Requieren atención</div>
    </div>
    
    <div class="stat-card info">
        <div class="stat-label">TOTAL PRODUCTOS</div>
        <div class="stat-value" id="stat-total-products">0</div>
        <div class="text-muted" style="font-size: 0.875rem; margin-top: var(--spacing-xs);">En el catálogo</div>
    </div>
</div>

<!-- Ventas Recientes -->
<div class="recent-sales">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
        <h2 style="font-size: 1.25rem;">Ventas Recientes</h2>
        <a href="dashboard.php?page=sales" class="btn btn-outline btn-sm">Ver todas</a>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Método</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="recent-sales-body">
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding: 2rem;">
                        Cargando ventas recientes...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Cargar estadísticas
    async function loadDashboardStats() {
        try {
            // Obtener productos para estadísticas de stock
            const responseProducts = await fetch('actions/get_products.php');
            const resultProducts = await responseProducts.json();
            
            let totalProducts = 0;
            let lowStockCount = 0;
            
            if (resultProducts.success) {
                totalProducts = resultProducts.data.length;
                lowStockCount = resultProducts.data.filter(p => p.stock <= p.min_stock).length;
            }
            
            // Obtener ventas para estadísticas de ventas
            const responseSales = await fetch('actions/get_recent_sales.php');
            const resultSales = await responseSales.json();
            
            let salesToday = 0;
            let salesTodayCount = 0;
            let salesMonth = 0;
            
            if (resultSales.success) {
                const todayStr = new Date().toISOString().split('T')[0];
                const currentMonth = new Date().getMonth();
                const currentYear = new Date().getFullYear();
                
                resultSales.data.forEach(sale => {
                    const saleDate = new Date(sale.created_at);
                    const saleDateStr = sale.created_at.split('T')[0];
                    
                    if (saleDateStr === todayStr) {
                        salesToday += parseFloat(sale.total);
                        salesTodayCount++;
                    }
                    
                    if (saleDate.getMonth() === currentMonth && saleDate.getFullYear() === currentYear) {
                        salesMonth += parseFloat(sale.total);
                    }
                });
            }
            
            document.getElementById('stat-sales-today').textContent = formatCurrency(salesToday);
            document.getElementById('stat-sales-today-count').textContent = `${salesTodayCount} transacciones`;
            document.getElementById('stat-sales-month').textContent = formatCurrency(salesMonth);
            document.getElementById('stat-low-stock').textContent = lowStockCount;
            document.getElementById('stat-total-products').textContent = totalProducts;
            
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }
    
    // Cargar ventas recientes
    async function loadRecentSales() {
        try {
            const response = await fetch('actions/get_recent_sales.php');
            const result = await response.json();
            
            const tbody = document.getElementById('recent-sales-body');
            
            if (result.success && result.data.length > 0) {
                tbody.innerHTML = result.data.slice(0, 5).map(sale => `
                    <tr>
                        <td><strong>${sale.sale_number}</strong></td>
                        <td>${new Date(sale.created_at).toLocaleDateString()}</td>
                        <td>${sale.customer_name || 'Cliente Walk-in'}</td>
                        <td><span style="text-transform: capitalize;">${sale.payment_method}</span></td>
                        <td><strong>${formatCurrency(sale.total)}</strong></td>
                        <td>
                            <span style="padding: 4px 8px; background: var(--color-success); color: white; border-radius: 12px; font-size: 0.75rem;">
                                ${sale.payment_status}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted" style="padding: 2rem;">
                            No hay ventas recientes
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading recent sales:', error);
            document.getElementById('recent-sales-body').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-error" style="padding: 2rem;">
                        Error al cargar ventas
                    </td>
                </tr>
            `;
        }
    }
    
    // Inicializar
    loadDashboardStats();
    loadRecentSales();
</script>