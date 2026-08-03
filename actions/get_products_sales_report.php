<?php
/**
 * Endpoint: Reporte Detallado de Productos Vendidos por Medio de Pago
 * Retorna las cantidades vendidas de cada producto agrupadas por Efectivo, Tarjeta, Yape y Transferencia
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

// Verificar login y privilegios de administrador
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

try {
    // Consultar todos los detalles de venta relacionalmente
    $result = $supabase->from('sale_items', [
        'select' => 'quantity,price,subtotal,sales(payment_method,payment_status),products(name,sku,category)'
    ]);

    if ($result['status'] === 200 && isset($result['data'])) {
        $reportData = [];

        foreach ($result['data'] as $item) {
            // Ignorar items de ventas anuladas o no pagadas
            $paymentStatus = !empty($item['sales']) ? ($item['sales']['payment_status'] ?? '') : '';
            if ($paymentStatus !== 'paid') {
                continue;
            }

            $productName = !empty($item['products']) ? ($item['products']['name'] ?? 'Producto Descontinuado') : 'Producto Descontinuado';
            $sku = !empty($item['products']) ? ($item['products']['sku'] ?? 'N/A') : 'N/A';
            $category = !empty($item['products']) ? ($item['products']['category'] ?? 'General') : 'General';
            
            $method = !empty($item['sales']) ? ($item['sales']['payment_method'] ?? 'cash') : 'cash';
            $qty = intval($item['quantity'] ?? 0);
            $subtotal = floatval($item['subtotal'] ?? 0);

            // Inicializar fila de producto si no existe en el reporte
            if (!isset($reportData[$sku])) {
                $reportData[$sku] = [
                    'name' => $productName,
                    'sku' => $sku,
                    'category' => $category,
                    'cash_qty' => 0,
                    'card_qty' => 0,
                    'yape_qty' => 0,
                    'transfer_qty' => 0,
                    'total_qty' => 0,
                    'total_revenue' => 0
                ];
            }

            // Acumular cantidades por método de pago
            if ($method === 'cash') {
                $reportData[$sku]['cash_qty'] += $qty;
            } elseif ($method === 'card') {
                $reportData[$sku]['card_qty'] += $qty;
            } elseif ($method === 'yape') {
                $reportData[$sku]['yape_qty'] += $qty;
            } elseif ($method === 'transfer') {
                $reportData[$sku]['transfer_qty'] += $qty;
            }

            $reportData[$sku]['total_qty'] += $qty;
            $reportData[$sku]['total_revenue'] += $subtotal;
        }

        echo json_encode([
            'success' => true,
            'data' => array_values($reportData),
            'count' => count($reportData)
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al obtener reporte de productos vendidos');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}