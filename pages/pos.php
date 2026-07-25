<?php
/**
 * Página de Punto de Venta (POS)
 * Interfaz principal para realizar ventas
 */
?>

<!-- Hero Section con Buscador -->
<section class="hero" style="padding: var(--spacing-xl) 0;">
    <div class="container">
        <div style="text-align: center; max-width: 800px; margin: 0 auto;">
            <h1 style="font-size: 2.5rem; margin-bottom: var(--spacing-md);">Realizar Venta</h1>
            <p style="font-size: 1.125rem; opacity: 0.9; margin-bottom: var(--spacing-xl);">
                Selecciona productos y agrégalos al carrito para procesar la venta
            </p>
            
            <!-- Buscador de Productos -->
            <div style="position: relative; max-width: 500px; margin: 0 auto;">
                <input 
                    type="text" 
                    id="product-search"
                    class="form-input" 
                    placeholder="🔍 Buscar producto por nombre o SKU..."
                    style="padding: 1rem; font-size: 1rem; border-radius: var(--radius-full);"
                >
            </div>
        </div>
    </div>
</section>

<!-- Grid de Productos -->
<section style="padding: var(--spacing-2xl) 0;">
    <div class="container">
        <div id="products-grid" class="products-grid">
            <!-- Los productos se cargan dinámicamente aquí -->
            <div class="text-center" style="grid-column: 1/-1; padding: 3rem;">
                <div class="skeleton skeleton-title" style="margin: 0 auto 1rem;"></div>
                <p class="text-muted">Cargando productos...</p>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas Rápidas -->
<section style="padding: 0 0 var(--spacing-2xl); background-color: var(--color-bg);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg);">
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📦</div>
                    <h3 style="font-size: 1.5rem; color: var(--color-primary);" id="stats-products">0</h3>
                    <p class="text-muted">Productos Disponibles</p>
                </div>
            </div>
            
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">🛒</div>
                    <h3 style="font-size: 1.5rem; color: var(--color-accent);" id="stats-cart">0</h3>
                    <p class="text-muted">Items en Carrito</p>
                </div>
            </div>
            
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">💰</div>
                    <h3 style="font-size: 1.5rem; color: var(--color-success);" id="stats-total">S/ 0.00</h3>
                    <p class="text-muted">Total a Pagar</p>
                </div>
            </div>
            
            <div class="card" style="text-align: center;">
                <div class="card-body">
                    <div style="font-size: 2rem; margin-bottom: var(--spacing-sm);">📊</div>
                    <h3 style="font-size: 1.5rem; color: var(--color-secondary);">Hoy</h3>
                    <p class="text-muted">Ventas del Día</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Actualizar estadísticas en tiempo real
setInterval(() => {
    const statsProducts = document.getElementById('stats-products');
    const statsCart = document.getElementById('stats-cart');
    const statsTotal = document.getElementById('stats-total');
    
    if (statsProducts) {
        statsProducts.textContent = AppState.products.length;
    }
    
    if (statsCart) {
        statsCart.textContent = CartManager.getItemCount();
    }
    
    if (statsTotal) {
        statsTotal.textContent = formatCurrency(CartManager.getTotal());
    }
}, 1000);
</script>