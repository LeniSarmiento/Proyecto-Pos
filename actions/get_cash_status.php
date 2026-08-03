<?php
/**
 * Endpoint: Obtener Estado de Caja
 * Retorna la caja activa abierta del cajero logueado y calcula ventas en efectivo
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

// Verificar login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

$user = getCurrentUser();

try {
    // 1. Consultar si hay una caja abierta (status = 'open') para este cajero
    $result = $supabase->from('cash_register', [
        'select' => '*',
        'user_id' => 'eq.' . $user['id'],
        'status' => 'eq.open',
        'limit' => '1'
    ]);

    if ($result['status'] === 200 && !empty($result['data'])) {
        $activeSession = $result['data'][0];
        $openedAt = $activeSession['opened_at'];
        
        // 2. Obtener todas las ventas pagadas de este cajero desde la apertura
        $salesResult = $supabase->from('sales', [
            'select' => 'total, payment_method',
            'user_id' => 'eq.' . $user['id'],
            'payment_status' => 'eq.paid',
            'created_at' => 'gte.' . $openedAt
        ]);

        $cashSalesTotal = 0;
        $cardSalesTotal = 0;
        $yapeSalesTotal = 0;

        if ($salesResult['status'] === 200 && !empty($salesResult['data'])) {
            foreach ($salesResult['data'] as $sale) {
                $method = $sale['payment_method'] ?? 'cash';
                $total = floatval($sale['total'] ?? 0);
                
                if ($method === 'cash') {
                    $cashSalesTotal += $total;
                } elseif ($method === 'card') {
                    $cardSalesTotal += $total;
                } elseif ($method === 'yape') {
                    $yapeSalesTotal += $total;
                }
            }
        }

        echo json_encode([
            'success' => true,
            'session' => $activeSession,
            'cash_sales_total' => $cashSalesTotal,
            'card_sales_total' => $cardSalesTotal,
            'yape_sales_total' => $yapeSalesTotal
        ]);
    } else {
        // No hay caja activa
        echo json_encode([
            'success' => true,
            'session' => null,
            'cash_sales_total' => 0,
            'card_sales_total' => 0,
            'yape_sales_total' => 0
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}