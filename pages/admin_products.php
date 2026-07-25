<?php
/**
 * Vista de Administración de Productos (CRUD)
 */
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-2xl);">
    <div>
        <h1 style="font-size: 2rem; margin-bottom: var(--spacing-sm);">Gestión de Productos</h1>
        <p class="text-muted">Administra el catálogo, stock y precios de tu tienda</p>
    </div>
    <button onclick="openProductModal()" class="btn btn-primary">
        ➕ Nuevo Producto
    </button>
</div>

<!-- Filtros y Búsqueda -->
<div class="card mb-lg">
    <div class="card-body" style="display: flex; gap: var(--spacing-md); flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" id="search-product-admin" class="form-input" placeholder="🔍 Buscar por nombre, SKU o categoría..." oninput="filterAdminProducts()">
        </div>
        <div>
            <select id="filter-category" class="form-select" onchange="filterAdminProducts()">
                <option value="">Todas las Categorías</option>
                <option value="Casacas">Casacas</option>
                <option value="Pantalones">Pantalones</option>
                <option value="Camisetas">Camisetas</option>
                <option value="Sudaderas">Sudaderas</option>
                <option value="Calzado">Calzado</option>
            </select>
        </div>
        <div>
            <select id="filter-stock" class="form-select" onchange="filterAdminProducts()">
                <option value="">Todos los Stocks</option>
                <option value="low">Stock Bajo</option>
                <option value="out">Sin Stock</option>
                <option value="ok">Con Stock</option>
            </select>
        </div>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>SKU</th>
                <th>Categoría</th>
                <th>Costo</th>
                <th>Precio Venta</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="admin-products-body">
            <tr>
                <td colspan="9" class="text-center text-muted" style="padding: 2rem;">
                    Cargando productos...
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal de Producto -->
<div id="product-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: none; align-items: center; justify-content: center; padding: var(--spacing-md);">
    <div class="card" style="width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modal-title" style="margin:0;">📦 Registrar Producto</h3>
            <button onclick="closeProductModal()" class="btn btn-sm btn-secondary" style="border:none; font-size: 1.25rem;">&times;</button>
        </div>
        <form id="product-form" onsubmit="saveProduct(event)">
            <input type="hidden" id="product-id" name="id">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Nombre del Producto</label>
                    <input type="text" id="prod-name" name="name" class="form-input" required placeholder="Ej. Casaca Impermeable">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">SKU (Código Único)</label>
                        <input type="text" id="prod-sku" name="sku" class="form-input" required placeholder="Ej. CAS-IMP-01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select id="prod-category" name="category" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="Casacas">Casacas</option>
                            <option value="Pantalones">Pantalones</option>
                            <option value="Camisetas">Camisetas</option>
                            <option value="Sudaderas">Sudaderas</option>
                            <option value="Calzado">Calzado</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">Costo (Compra)</label>
                        <input type="number" id="prod-cost" name="cost" class="form-input" required step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Precio de Venta</label>
                        <input type="number" id="prod-price" name="price" class="form-input" required step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                    <div class="form-group">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" id="prod-stock" name="stock" class="form-input" required min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Mínimo (Alerta)</label>
                        <input type="number" id="prod-min-stock" name="min_stock" class="form-input" required min="1" value="5" placeholder="5">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">URL de la Imagen (Opcional)</label>
                    <input type="text" id="prod-image" name="image_url" class="form-input" placeholder="https://ejemplo.com/imagen.jpg">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea id="prod-desc" name="description" class="form-textarea" rows="2" placeholder="Detalles o especificaciones del producto..."></textarea>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: var(--spacing-sm);">
                    <input type="checkbox" id="prod-active" name="is_active" checked style="width: auto; cursor:pointer;">
                    <label for="prod-active" class="form-label" style="margin-bottom:0; cursor:pointer;">Producto Activo</label>
                </div>
            </div>
            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: var(--spacing-md);">
                <button type="button" onclick="closeProductModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btn-save-product">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    let adminProducts = [];

    async function loadAdminProducts() {
        try {
            const response = await fetch('actions/get_products.php');
            const result = await response.json();
            if (result.success) {
                adminProducts = result.data;
                renderAdminProducts(adminProducts);
            } else {
                showToast('Error al cargar productos', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de conexión', 'error');
        }
    }

    function renderAdminProducts(products) {
        const tbody = document.getElementById('admin-products-body');
        if (!tbody) return;

        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 2rem;">
                        No se encontraron productos
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = products.map(p => `
            <tr>
                <td>
                    <img src="${p.image_url || 'assets/img/placeholder.svg'}" alt="${p.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: var(--radius-sm);" onerror="this.src='assets/img/placeholder.svg'">
                </td>
                <td><strong>${p.name}</strong><br><small class="text-muted">${p.description ? p.description.substring(0, 50) + '...' : 'Sin descripción'}</small></td>
                <td><code style="background: var(--color-bg); padding: 2px 6px; border-radius: 4px;">${p.sku || 'N/A'}</code></td>
                <td>${p.category || 'General'}</td>
                <td>S/ ${parseFloat(p.cost || 0).toFixed(2)}</td>
                <td><strong style="color: var(--color-primary);">S/ ${parseFloat(p.price || 0).toFixed(2)}</strong></td>
                <td>
                    <span class="product-stock ${p.stock <= 0 ? 'out' : p.stock <= p.min_stock ? 'low' : ''}" style="margin-top:0;">
                        ${p.stock} un.
                    </span>
                </td>
                <td>
                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; background: ${p.is_active ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color: ${p.is_active ? 'var(--color-success)' : 'var(--color-error)'};">
                        ${p.is_active ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: var(--spacing-sm);">
                        <button onclick="editProduct(${JSON.stringify(p).replace(/"/g, '&quot;')})" class="btn btn-sm btn-secondary" style="padding: 4px 8px;">✏️</button>
                        <button onclick="deleteProduct('${p.id}')" class="btn btn-sm btn-outline" style="padding: 4px 8px; color: var(--color-error); border-color: var(--color-error);">🗑️</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function filterAdminProducts() {
        const query = document.getElementById('search-product-admin').value.toLowerCase();
        const category = document.getElementById('filter-category').value;
        const stockFilter = document.getElementById('filter-stock').value;

        const filtered = adminProducts.filter(p => {
            const matchesQuery = p.name.toLowerCase().includes(query) || 
                                 (p.sku && p.sku.toLowerCase().includes(query)) ||
                                 (p.category && p.category.toLowerCase().includes(query));
            
            const matchesCategory = category === "" || p.category === category;
            
            let matchesStock = true;
            if (stockFilter === "low") {
                matchesStock = p.stock > 0 && p.stock <= p.min_stock;
            } else if (stockFilter === "out") {
                matchesStock = p.stock <= 0;
            } else if (stockFilter === "ok") {
                matchesStock = p.stock > p.min_stock;
            }

            return matchesQuery && matchesCategory && matchesStock;
        });

        renderAdminProducts(filtered);
    }

    function openProductModal() {
        document.getElementById('modal-title').textContent = "📦 Registrar Producto";
        document.getElementById('product-form').reset();
        document.getElementById('product-id').value = "";
        document.getElementById('prod-sku').disabled = false;
        const modal = document.getElementById('product-modal');
        modal.style.display = 'flex';
    }

    function closeProductModal() {
        const modal = document.getElementById('product-modal');
        modal.style.display = 'none';
    }

    function editProduct(product) {
        document.getElementById('modal-title').textContent = "✏️ Editar Producto";
        document.getElementById('product-id').value = product.id;
        document.getElementById('prod-name').value = product.name;
        document.getElementById('prod-sku').value = product.sku || '';
        document.getElementById('prod-sku').disabled = true; // No permitir cambiar SKU en edición
        document.getElementById('prod-category').value = product.category || '';
        document.getElementById('prod-cost').value = product.cost || 0;
        document.getElementById('prod-price').value = product.price || 0;
        document.getElementById('prod-stock').value = product.stock || 0;
        document.getElementById('prod-min-stock').value = product.min_stock || 5;
        document.getElementById('prod-image').value = product.image_url || '';
        document.getElementById('prod-desc').value = product.description || '';
        document.getElementById('prod-active').checked = product.is_active;

        const modal = document.getElementById('product-modal');
        modal.style.display = 'flex';
    }

    async function saveProduct(event) {
        event.preventDefault();
        const btn = document.getElementById('btn-save-product');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Guardando...';

        const id = document.getElementById('product-id').value;
        const data = {
            id: id || null,
            name: document.getElementById('prod-name').value,
            sku: document.getElementById('prod-sku').value,
            category: document.getElementById('prod-category').value,
            cost: parseFloat(document.getElementById('prod-cost').value),
            price: parseFloat(document.getElementById('prod-price').value),
            stock: parseInt(document.getElementById('prod-stock').value),
            min_stock: parseInt(document.getElementById('prod-min-stock').value),
            image_url: document.getElementById('prod-image').value,
            description: document.getElementById('prod-desc').value,
            is_active: document.getElementById('prod-active').checked
        };

        try {
            const response = await fetch('actions/save_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (result.success) {
                showToast(id ? 'Producto actualizado exitosamente' : 'Producto creado exitosamente', 'success');
                closeProductModal();
                loadAdminProducts();
            } else {
                showToast(result.error || 'Error al guardar producto', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de red', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function deleteProduct(id) {
        if (!confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) return;

        try {
            const response = await fetch('actions/delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            const result = await response.json();

            if (result.success) {
                showToast('Producto eliminado exitosamente', 'success');
                loadAdminProducts();
            } else {
                showToast(result.error || 'Error al eliminar producto', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de red', 'error');
        }
    }

    // Inicializar carga de productos
    loadAdminProducts();
</script>