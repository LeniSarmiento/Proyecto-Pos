<?php
require_once __DIR__ . '/config/supabase.php';

// Verificar autenticación
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$user = getCurrentUser();
$page = $_GET['page'] ?? 'pos';

// Si es administrador, redirigir al panel gerencial
if ($user['role'] === 'admin' && $page === 'pos_dashboard_default') {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Ventas - <?= APP_NAME ?></title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <style>
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .dashboard-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--color-border);
            padding: var(--spacing-lg);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .dashboard-main {
            flex: 1;
            margin-left: 280px;
            padding: var(--spacing-xl);
            background: var(--color-bg);
        }
        
        @media (max-width: 768px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .dashboard-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header para POS (si aplica o se mantiene dentro del layout) -->
    <header class="site-header" style="position: fixed; top: 0; left: 0; right: 0; height: var(--header-height); background: white; border-bottom: 1px solid var(--color-border); z-index: 1000;">
        <div class="header-container">
            <a href="pos_dashboard.php" class="brand">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <rect width="32" height="32" rx="8" fill="var(--color-primary)"/>
                    <path d="M8 16L14 22L24 10" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span><?= APP_NAME ?> — POS</span>
            </a>
            
            <div class="flex items-center gap-md">
                <button onclick="toggleDarkMode()" class="btn btn-sm btn-secondary" style="border-radius: 50%; width: 36px; height: 36px; padding:0; display:flex; align-items:center; justify-content:center;">
                    🌙
                </button>
                <?php if ($page === 'pos'): ?>
                    <button onclick="toggleCart()" class="btn btn-primary btn-sm" style="position: relative;">
                        🛒 Carrito
                        <span id="cart-count" style="position: absolute; top: -8px; right: -8px; background: var(--color-accent); color: white; border-radius: 50%; padding: 2px 6px; font-size: 0.75rem; font-weight: 700; display: none;">0</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="dashboard-layout" style="padding-top: var(--header-height);">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar" style="top: var(--header-height); height: calc(100vh - var(--header-height));">
            <nav class="sidebar-menu">
                <a href="pos_dashboard.php?page=pos" class="sidebar-link <?= $page === 'pos' ? 'active' : '' ?>">
                    <span>🛒</span>
                    <span>Punto de Venta</span>
                </a>
                <a href="pos_dashboard.php?page=cash" class="sidebar-link <?= $page === 'cash' ? 'active' : '' ?>">
                    <span>💵</span>
                    <span>Control de Caja</span>
                </a>
                <a href="pos_dashboard.php?page=sales" class="sidebar-link <?= $page === 'sales' ? 'active' : '' ?>">
                    <span>📋</span>
                    <span>Mis Ventas del Día</span>
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="dashboard.php" class="sidebar-link" style="background: rgba(37,99,235,0.05); color: var(--color-primary); margin-top: var(--spacing-md);">
                        <span>👑</span>
                        <span>Ir al Panel Admin</span>
                    </a>
                <?php endif; ?>
            </nav>
            
            <div style="margin-top: auto; padding-top: var(--spacing-lg); border-top: 1px solid var(--color-border); display: flex; flex-direction: column; gap: var(--spacing-md);">
                <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                    <div style="width: 40px; height: 40px; background: var(--color-primary); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($user['name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-text-light); text-transform: capitalize;"><?= htmlspecialchars($user['role']) ?></div>
                    </div>
                </div>
                <a href="actions/logout.php" class="btn btn-outline btn-full btn-sm">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </aside>
        
        <!-- Contenido Principal -->
        <main class="dashboard-main" style="margin-left: 280px; padding: var(--spacing-xl); background: var(--color-bg); min-height: calc(100vh - var(--header-height));">
            <?php
            switch ($page) {
                case 'cash':
                    include 'pages/cash.php';
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
    </div>

    <!-- Panel del Carrito (solo si estamos en la vista de POS) -->
    <?php if ($page === 'pos'): ?>
        <div class="cart-panel" style="top: var(--header-height); height: calc(100vh - var(--header-height));">
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