<?php
require_once __DIR__ . '/config/supabase.php';

// Redirección inteligente si ya está logueado
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    if ($currentUser['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: pos_dashboard.php');
    }
    exit;
}

// Determinar página actual
$page = $_GET['page'] ?? 'login'; // Forzar a login por defecto si no está logueado
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= APP_NAME ?> - Sistema de Punto de Venta</title>
    <meta name="description" content="Sistema moderno de punto de venta para tu negocio">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-container">
            <a href="index.php" class="brand">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="8" fill="var(--color-primary)"/>
                    <path d="M8 16L14 22L24 10" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span><?= APP_NAME ?></span>
            </a>
            
            <nav class="main-nav">
                <?php if (isLoggedIn()): ?>
                    <a href="index.php?page=pos" class="nav-link">Ventas</a>
                    <a href="index.php?page=products" class="nav-link">Productos</a>
                    <a href="index.php?page=sales" class="nav-link">Historial</a>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="actions/logout.php" class="nav-link">Salir</a>
                <?php else: ?>
                    <a href="index.php?page=login" class="nav-link">Ingresar</a>
                <?php endif; ?>
            </nav>
            
            <div class="flex items-center gap-md">
                <!-- Modo Oscuro -->
                <button onclick="toggleDarkMode()" class="btn btn-sm btn-secondary" style="border-radius: 50%; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;" title="Modo Oscuro (Beta)">
                    🌙
                </button>

                <!-- Botón del Carrito -->
                <?php if ($page === 'pos'): ?>
                    <button onclick="toggleCart()" class="btn btn-primary btn-sm" style="position: relative;">
                        🛒 Carrito
                        <span id="cart-count" style="position: absolute; top: -8px; right: -8px; background: var(--color-accent); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.75rem; font-weight: 700; display: none;">0</span>
                    </button>
                <?php endif; ?>
                
                <!-- Menú Móvil -->
                <button class="menu-toggle" onclick="toggleMenu()" aria-label="Menú">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main style="padding-top: var(--header-height); min-height: 100vh;">
        <?php
        switch ($page) {
            case 'login':
                include 'pages/login.php';
                break;
            case 'products':
                include 'pages/products.php';
                break;
            case 'sales':
                include 'pages/sales.php';
                break;
            case 'pos':
            default:
                include 'pages/pos.php';
                break;
        }
        ?>
    </main>

    <!-- Panel del Carrito -->
    <?php if ($page === 'pos'): ?>
        <div class="cart-panel">
            <div class="cart-header">
                <h3 style="margin: 0;">🛒 Carrito de Compras</h3>
                <button onclick="closeCart()" class="btn btn-sm btn-secondary" style="border: none;">&times;</button>
            </div>
            
            <div id="cart-items" class="cart-items">
                <!-- Items del carrito se renderizan aquí -->
            </div>
            
            <div class="cart-footer">
                <div class="cart-total">
                    <span>Total:</span>
                    <span id="cart-total">S/ 0.00</span>
                </div>
                
                <form id="checkout-form">
                    <div class="form-group">
                        <label class="form-label">Método de Pago</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="transfer">Transferencia</option>
                            <option value="yape">Yape/Plin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notas (opcional)</label>
                        <textarea name="notes" class="form-textarea" rows="2" placeholder="Instrucciones adicionales..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full btn-lg" id="checkout-btn">
                        ✅ Procesar Venta
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenedor de Notificaciones -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Scripts -->
    <script src="assets/js/main.js?v=1.0.1"></script>
</body>
</html>