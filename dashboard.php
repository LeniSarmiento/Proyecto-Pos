<?php
require_once __DIR__ . '/config/supabase.php';

// Verificar autenticación
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$user = getCurrentUser();
$page = $_GET['page'] ?? 'home';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - <?= APP_NAME ?></title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-2xl);
        }
        
        .stat-card {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border-left: 4px solid var(--color-primary);
        }
        
        .stat-card.success { border-left-color: var(--color-success); }
        .stat-card.warning { border-left-color: var(--color-warning); }
        .stat-card.info { border-left-color: var(--color-info); }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-text);
        }
        
        .stat-label {
            color: var(--color-text-light);
            font-size: 0.875rem;
            margin-top: var(--spacing-xs);
        }
        
        .recent-sales {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-lg);
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
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div style="margin-bottom: var(--spacing-2xl);">
                <a href="dashboard.php" class="brand">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="8" fill="var(--color-primary)"/>
                        <path d="M8 16L14 22L24 10" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span><?= APP_NAME ?></span>
                </a>
            </div>
            
            <nav class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-link <?= $page === 'home' ? 'active' : '' ?>">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="index.php?page=pos" class="sidebar-link">
                    <span>🛒</span>
                    <span>Punto de Venta</span>
                </a>
                <a href="dashboard.php?page=products" class="sidebar-link <?= $page === 'products' ? 'active' : '' ?>">
                    <span>📦</span>
                    <span>Productos</span>
                </a>
                <a href="dashboard.php?page=sales" class="sidebar-link <?= $page === 'sales' ? 'active' : '' ?>">
                    <span>📋</span>
                    <span>Ventas</span>
                </a>
                <a href="dashboard.php?page=customers" class="sidebar-link <?= $page === 'customers' ? 'active' : '' ?>">
                    <span>👥</span>
                    <span>Clientes</span>
                </a>
                <a href="dashboard.php?page=reports" class="sidebar-link <?= $page === 'reports' ? 'active' : '' ?>">
                    <span>📈</span>
                    <span>Reportes</span>
                </a>
            </nav>
            
            <div style="margin-top: auto; padding-top: var(--spacing-lg); border-top: 1px solid var(--color-border);">
                <div style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-md);">
                    <div style="width: 40px; height: 40px; background: var(--color-primary); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($user['name']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--color-text-light);"><?= htmlspecialchars($user['role']) ?></div>
                    </div>
                </div>
                <a href="actions/logout.php" class="btn btn-outline btn-full btn-sm">
                    🚪 Cerrar Sesión
                </a>
            </div>
        </aside>
        
        <!-- Contenido Principal -->
        <main class="dashboard-main">
            <div style="display: flex; justify-content: flex-end; margin-bottom: var(--spacing-md);">
                <button onclick="toggleDarkMode()" class="btn btn-sm btn-secondary" style="border-radius: var(--radius-full);">🌙 Oscuro</button>
            </div>
            <?php
            switch ($page) {
                case 'products':
                    include 'pages/admin_products.php';
                    break;
                case 'sales':
                    include 'pages/admin_sales.php';
                    break;
                case 'customers':
                    include 'pages/admin_customers.php';
                    break;
                case 'reports':
                    include 'pages/admin_reports.php';
                    break;
                case 'home':
                default:
                    include 'pages/admin_home.php';
                    break;
            }
            ?>
        </main>
    </div>
    
    <script src="assets/js/main.js?v=1.0.1"></script>
</body>
</html>