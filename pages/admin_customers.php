<?php
/**
 * Vista de Gestión de Clientes
 */
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
    <div>
        <h1 style="font-size: 2rem; margin-bottom: var(--spacing-sm);">Directorio de Clientes</h1>
        <p class="text-muted">Administra los datos de contacto, RUC o DNI de tus clientes corporativos y recurrentes</p>
    </div>
    <button onclick="openCustomerModal()" class="btn btn-primary">
        👥 Registrar Cliente
    </button>
</div>

<!-- Filtros de Clientes -->
<div class="card mb-lg">
    <div class="card-body">
        <input type="text" id="search-customer" class="form-input" placeholder="🔍 Buscar por nombre, DNI/RUC, teléfono o correo..." oninput="filterCustomers()">
    </div>
</div>

<!-- Tabla de Clientes -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>DNI / RUC</th>
                <th>Teléfono</th>
                <th>Correo Electrónico</th>
                <th>Dirección</th>
                <th>Fecha de Registro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="admin-customers-body">
            <tr>
                <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                    Cargando clientes...
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal Cliente -->
<div id="customer-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: none; align-items: center; justify-content: center; padding: var(--spacing-md);">
    <div class="card" style="width: 100%; max-width: 550px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 id="customer-modal-title" style="margin:0;">👥 Registrar Cliente</h3>
            <button onclick="closeCustomerModal()" class="btn btn-sm btn-secondary" style="border:none; font-size: 1.25rem;">&times;</button>
        </div>
        <form id="customer-form" onsubmit="saveCustomer(event)">
            <input type="hidden" id="customer-id" name="id">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Nombre Completo o Razón Social</label>
                    <input type="text" id="cust-name" name="name" class="form-input" required placeholder="Ej. Juan Pérez / Inversiones SAC">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">DNI o RUC</label>
                        <input type="text" id="cust-doc" name="ruc_dni" class="form-input" required placeholder="Ej. 1044399201">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" id="cust-phone" name="phone" class="form-input" placeholder="Ej. 987654321">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" id="cust-email" name="email" class="form-input" placeholder="cliente@correo.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección Fiscal / Domicilio</label>
                    <textarea id="cust-address" name="address" class="form-textarea" rows="2" placeholder="Ej. Av. Larco 123, Miraflores, Lima"></textarea>
                </div>
            </div>
            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: var(--spacing-md);">
                <button type="button" onclick="closeCustomerModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-save-customer">Guardar Cliente</button>
            </div>
        </form>
    </div>
</div>

<script>
    let allCustomers = [];

    async function loadCustomers() {
        try {
            const response = await fetch('actions/get_customers.php');
            const result = await response.json();
            
            if (result.success) {
                allCustomers = result.data;
                renderCustomers(allCustomers);
            } else {
                showToast('Error al cargar clientes', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de conexión', 'error');
        }
    }

    function renderCustomers(customers) {
        const tbody = document.getElementById('admin-customers-body');
        if (!tbody) return;

        if (customers.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                        No se encontraron clientes registrados
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = customers.map(c => `
            <tr>
                <td><strong>${c.name}</strong></td>
                <td><code style="background: var(--color-bg); padding: 2px 6px; border-radius: 4px;">${c.ruc_dni || 'Walk-in'}</code></td>
                <td>${c.phone || '<span class="text-muted">N/A</span>'}</td>
                <td>${c.email || '<span class="text-muted">N/A</span>'}</td>
                <td><small class="text-muted">${c.address || 'Sin dirección'}</small></td>
                <td>${new Date(c.created_at).toLocaleDateString()}</td>
                <td>
                    <div style="display: flex; gap: var(--spacing-sm);">
                        <button onclick="editCustomer(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="btn btn-sm btn-secondary" style="padding: 4px 8px;">✏️</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function filterCustomers() {
        const query = document.getElementById('search-customer').value.toLowerCase();
        const filtered = allCustomers.filter(c =>
            c.name.toLowerCase().includes(query) ||
            (c.ruc_dni && c.ruc_dni.toLowerCase().includes(query)) ||
            (c.phone && c.phone.toLowerCase().includes(query)) ||
            (c.email && c.email.toLowerCase().includes(query))
        );
        renderCustomers(filtered);
    }

    function openCustomerModal() {
        document.getElementById('customer-modal-title').textContent = "👥 Registrar Cliente";
        document.getElementById('customer-form').reset();
        document.getElementById('customer-id').value = "";
        const modal = document.getElementById('customer-modal');
        modal.style.display = 'flex';
    }

    function closeCustomerModal() {
        const modal = document.getElementById('customer-modal');
        modal.style.display = 'none';
    }

    function editCustomer(customer) {
        document.getElementById('customer-modal-title').textContent = "✏️ Editar Cliente";
        document.getElementById('customer-id').value = customer.id;
        document.getElementById('cust-name').value = customer.name;
        document.getElementById('cust-doc').value = customer.ruc_dni || '';
        document.getElementById('cust-phone').value = customer.phone || '';
        document.getElementById('cust-email').value = customer.email || '';
        document.getElementById('cust-address').value = customer.address || '';

        const modal = document.getElementById('customer-modal');
        modal.style.display = 'flex';
    }

    async function saveCustomer(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-save-customer');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Guardando...';

        const id = document.getElementById('customer-id').value;
        const data = {
            id: id || null,
            name: document.getElementById('cust-name').value,
            ruc_dni: document.getElementById('cust-doc').value,
            phone: document.getElementById('cust-phone').value,
            email: document.getElementById('cust-email').value,
            address: document.getElementById('cust-address').value
        };

        try {
            const response = await fetch('actions/save_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                showToast(id ? 'Cliente actualizado exitosamente' : 'Cliente registrado exitosamente', 'success');
                closeCustomerModal();
                loadCustomers();
            } else {
                showToast(result.error || 'Error al guardar cliente', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de red', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    loadCustomers();
</script>