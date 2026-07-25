<?php
/**
 * Vista de Consulta de Catálogo (Products) para el Vendedor
 */
?>
<section class="hero" style="padding: var(--spacing-xl) 0;">
    <div class="container">
        <div style="text-align: center; max-width: 800px; margin: 0 auto;">
            <h1 style="font-size: 2.5rem; margin-bottom: var(--spacing-sm);">Catálogo de Productos</h1>
            <p style="font-size: 1.125rem; opacity: 0.9; margin-bottom: var(--spacing-xl);">
                Consulta precios, stock actual y códigos de barra/SKU de todos los productos disponibles en tienda
            </p>
            <div style="position: relative; max-width: 500px; margin: 0 auto;">
                <input 
                    type="text" 
                    id="product-search-catalog"
                    class="form-input" 
                    placeholder="🔍 Buscar en el catálogo..."
                    style="padding: 1rem; font-size: 1rem; border-radius: var(--radius-full);"
                    oninput="searchCatalog(event)"
                >
            </div>
        </div>
    </div>
</section>

<section style="padding: var(--spacing-2xl) 0;">
    <div class="container">
        <div id="catalog-grid" class="products-grid">
            <!-- Cargando -->
            <div class="text-center" style="grid-column: 1/-1; padding: 3rem;">
                <p class="text-muted">Cargando catálogo...</p>
            </div>
        </div>
    </div>
</section>

<script>
    let catalogProducts = [];

    async function loadCatalog() {
        try {
            const response = await fetch('actions/get_products.php');
            const result = await response.json();
            if (result.success) {
                catalogProducts = result.data;
                renderCatalog(catalogProducts);
            }
        } catch (error) {
            console.error(error);
        }
    }

    function renderCatalog(products) {
        const grid = document.getElementById('catalog-grid');
        if (!grid) return;

        if (products.length === 0) {
            grid.innerHTML = '<div class="text-center text-muted" style="grid-column: 1/-1;">No se encontraron productos</div>';
            return;
        }

        grid.innerHTML = products.map(p => `
            <div class="product-card" style="cursor: default; transform: none;">
                <img src="${p.image_url || 'assets/img/placeholder.svg'}" alt="${p.name}" class="product-image" onerror="this.onerror=null; this.src='assets/img/placeholder.svg'">
                <div class="product-body">
                    <span class="text-muted" style="font-size: 0.75rem; text-transform: uppercase; font-weight:600; letter-spacing: 0.05em;">${p.category}</span>
                    <h3 class="product-name" style="margin-top: 0.25rem;">${p.name}</h3>
                    <code style="background: var(--color-bg); padding: 2px 6px; border-radius: 4px; font-size: 0.8125rem; display: inline-block; margin-bottom: var(--spacing-sm);">${p.sku}</code>
                    <p class="product-price">${formatCurrency(p.price)}</p>
                    <span class="product-stock ${p.stock <= 0 ? 'out' : p.stock <= p.min_stock ? 'low' : ''}">
                        ${p.stock <= 0 ? 'Agotado' : p.stock <= p.min_stock ? 'Stock bajo' : `Stock: ${p.stock}`}
                    </span>
                </div>
            </div>
        `).join('');
    }

    function searchCatalog(event) {
        const query = event.target.value.toLowerCase();
        const filtered = catalogProducts.filter(p =>
            p.name.toLowerCase().includes(query) ||
            p.sku.toLowerCase().includes(query) ||
            (p.category && p.category.toLowerCase().includes(query))
        );
        renderCatalog(filtered);
    }

    loadCatalog();
</script>