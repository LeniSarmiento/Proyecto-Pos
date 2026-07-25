<?php
/**
 * Vista de Historial de Ventas
 */
?>
<div style="margin-bottom: var(--spacing-2xl);">
    <h1 style="font-size: 2rem; margin-bottom: var(--spacing-sm);">Historial de Ventas</h1>
    <p class="text-muted">Consulta, filtra y detalla todas las ventas procesadas por tu negocio</p>
</div>

<!-- Filtros de Ventas -->
<div class="card mb-lg">
    <div class="card-body" style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" id="search-sale" class="form-input" placeholder="🔍 Buscar por número de venta..." oninput="filterSales()">
        </div>
        <div>
            <select id="filter-payment-method" class="form-select" onchange="filterSales()">
                <option value="">Cualquier método</option>
                <option value="cash">Efectivo</option>
                <option value="card">Tarjeta</option>
                <option value="transfer">Transferencia</option>
                <option value="yape">Yape/Plin</option>
            </select>
        </div>
        <div>
            <input type="date" id="filter-date" class="form-input" onchange="filterSales()">
        </div>
    </div>
</div>

<!-- Tabla de Ventas -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Número Venta</th>
                <th>Fecha y Hora</th>
                <th>Vendedor</th>
                <th>Cliente</th>
                <th>Método de Pago</th>
                <th>Subtotal</th>
                <th>IGV (18%)</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="admin-sales-body">
            <tr>
                <td colspan="10" class="text-center text-muted" style="padding: 2rem;">
                    Cargando ventas...
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal Detalle de Venta -->
<div id="sale-detail-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: none; align-items: center; justify-content: center; padding: var(--spacing-md);">
    <div class="card" style="width: 100%; max-width: 650px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--color-border);">
            <div style="display: flex; flex-direction: column;">
                <span class="tag" style="background: var(--color-primary); color: white; margin-bottom: 0.25rem; font-weight:600;" id="detail-tag-number">VTA-XXXX</span>
                <h3 style="margin:0;" id="detail-title">Detalle de Comprobante</h3>
            </div>
            <button onclick="closeSaleDetailModal()" class="btn btn-sm btn-secondary" style="border:none; font-size: 1.25rem;">&times;</button>
        </div>
        <div class="card-body">
            <!-- Info General -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg); margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-md); border-bottom: 1px dashed var(--color-border); font-size: 0.9375rem;">
                <div>
                    <p style="margin-bottom: var(--spacing-xs);"><strong>Fecha de Emisión:</strong> <span id="detail-date" class="text-muted">--/--/----</span></p>
                    <p style="margin-bottom: var(--spacing-xs);"><strong>Vendedor:</strong> <span id="detail-user" class="text-muted">Admin</span></p>
                </div>
                <div>
                    <p style="margin-bottom: var(--spacing-xs);"><strong>Cliente:</strong> <span id="detail-customer" class="text-muted">Cliente Walk-in</span></p>
                    <p style="margin-bottom: var(--spacing-xs);"><strong>Método de Pago:</strong> <span id="detail-payment" class="text-muted" style="text-transform: capitalize;">Efectivo</span></p>
                </div>
            </div>

            <!-- Tabla de Productos -->
            <div class="table-container" style="box-shadow: none; margin-bottom: var(--spacing-lg);">
                <table class="table" style="font-size: 0.9375rem;">
                    <thead>
                        <tr style="background: var(--color-bg);">
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">P. Unit.</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detail-items-body">
                        <!-- Items cargados dinámicamente -->
                    </tbody>
                </table>
            </div>

            <!-- Notas de Venta -->
            <div style="background: var(--color-bg); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-lg); font-size: 0.875rem;" id="detail-notes-container">
                <strong>Notas:</strong> <span id="detail-notes" class="text-muted">Ninguna</span>
            </div>

            <!-- Totales -->
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: var(--spacing-xs); font-size: 0.9375rem;">
                <p>Subtotal: <span id="detail-subtotal" style="font-weight: 600;">S/ 0.00</span></p>
                <p>IGV (18%): <span id="detail-tax" style="font-weight: 600;">S/ 0.00</span></p>
                <p>Descuento: <span id="detail-discount" style="font-weight: 600; color: var(--color-error);">S/ 0.00</span></p>
                <p style="font-size: 1.25rem; border-top: 2px solid var(--color-primary); padding-top: var(--spacing-xs); margin-top: var(--spacing-xs);">
                    <strong>Total: <span id="detail-total" style="color: var(--color-primary);">S/ 0.00</span></strong>
                </p>
            </div>
        </div>
        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <button onclick="window.print()" class="btn btn-secondary">
                🖨️ Imprimir Ticket
            </button>
            <button onclick="closeSaleDetailModal()" class="btn btn-primary">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    let allSales = [];

    async function loadSales() {
        try {
            const response = await fetch('actions/get_recent_sales.php');
            const result = await response.json();
            
            if (result.success) {
                allSales = result.data;
                renderSales(allSales);
            } else {
                showToast('Error al cargar ventas', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de conexión', 'error');
        }
    }

    function renderSales(sales) {
        const tbody = document.getElementById('admin-sales-body');
        if (!tbody) return;

        if (sales.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding: 2rem;">
                        No se encontraron registros de ventas
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = sales.map(s => `
            <tr>
                <td><strong>${s.sale_number}</strong></td>
                <td>${new Date(s.created_at).toLocaleDateString()} ${new Date(s.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</td>
                <td>${s.user_name || 'N/A'}</td>
                <td>${s.customer_name || 'Cliente Walk-in'}</td>
                <td><span style="text-transform: capitalize;">${s.payment_method}</span></td>
                <td>S/ ${parseFloat(s.subtotal || 0).toFixed(2)}</td>
                <td>S/ ${parseFloat(s.tax || 0).toFixed(2)}</td>
                <td><strong style="color: var(--color-primary);">S/ ${parseFloat(s.total || 0).toFixed(2)}</strong></td>
                <td>
                    <span style="padding: 4px 8px; background: ${s.payment_status === 'paid' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)'}; color: ${s.payment_status === 'paid' ? 'var(--color-success)' : 'var(--color-warning)'}; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                        ${s.payment_status === 'paid' ? 'Pagado' : 'Pendiente'}
                    </span>
                </td>
                <td>
                    <button onclick="viewSaleDetails('${s.id}')" class="btn btn-sm btn-secondary">🔍 Detalle</button>
                </td>
            </tr>
        `).join('');
    }

    function filterSales() {
        const numberQuery = document.getElementById('search-sale').value.toLowerCase();
        const method = document.getElementById('filter-payment-method').value;
        const dateVal = document.getElementById('filter-date').value;

        const filtered = allSales.filter(s => {
            const matchesNumber = s.sale_number.toLowerCase().includes(numberQuery);
            const matchesMethod = method === "" || s.payment_method === method;
            
            let matchesDate = true;
            if (dateVal) {
                const saleDateOnly = s.created_at.split('T')[0];
                matchesDate = saleDateOnly === dateVal;
            }

            return matchesNumber && matchesMethod && matchesDate;
        });

        renderSales(filtered);
    }

    async function viewSaleDetails(saleId) {
        try {
            const response = await fetch(`actions/get_sale_details.php?sale_id=${saleId}`);
            const result = await response.json();
            
            if (result.success) {
                const sale = result.sale;
                const items = result.items;

                document.getElementById('detail-tag-number').textContent = sale.sale_number;
                document.getElementById('detail-date').textContent = new Date(sale.created_at).toLocaleString();
                document.getElementById('detail-user').textContent = sale.user_name || 'Administrador';
                document.getElementById('detail-customer').textContent = sale.customer_name || 'Cliente Walk-in';
                document.getElementById('detail-payment').textContent = sale.payment_method;
                
                // Notas
                if (sale.notes) {
                    document.getElementById('detail-notes-container').style.display = 'block';
                    document.getElementById('detail-notes').textContent = sale.notes;
                } else {
                    document.getElementById('detail-notes-container').style.display = 'none';
                }

                // Totales
                document.getElementById('detail-subtotal').textContent = formatCurrency(sale.subtotal);
                document.getElementById('detail-tax').textContent = formatCurrency(sale.tax);
                document.getElementById('detail-discount').textContent = formatCurrency(sale.discount);
                document.getElementById('detail-total').textContent = formatCurrency(sale.total);

                // Render Items
                const itemsBody = document.getElementById('detail-items-body');
                itemsBody.innerHTML = items.map(item => `
                    <tr>
                        <td>
                            <strong>${item.products?.name || 'Producto Descontinuado'}</strong><br>
                            <small class="text-muted">SKU: ${item.products?.sku || 'N/A'}</small>
                        </td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-right">S/ ${parseFloat(item.price).toFixed(2)}</td>
                        <td class="text-right" style="font-weight: 600;">S/ ${parseFloat(item.subtotal).toFixed(2)}</td>
                    </tr>
                `).join('');

                const modal = document.getElementById('sale-detail-modal');
                modal.style.display = 'flex';
            } else {
                showToast(result.error || 'Error al cargar detalles de venta', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de red', 'error');
        }
    }

    function closeSaleDetailModal() {
        const modal = document.getElementById('sale-detail-modal');
        modal.style.display = 'none';
    }

    loadSales();
</script>