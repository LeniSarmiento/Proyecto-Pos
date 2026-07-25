<?php
/**
 * Endpoint: Crear Venta
 * Registra una nueva venta y actualiza el stock de productos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

try {
    // Iniciar transacción lógica (Supabase no soporta transacciones multi-tabla directas desde REST)
    
    // Generar número de venta único
    $saleNumber = 'VTA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Calcular totales
    $items = $input['items'];
    $subtotal = 0;
    
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $tax = $subtotal * 0.18; // IGV 18%
    $discount = $input['discount'] ?? 0;
    $total = $subtotal + $tax - $discount;
    
    // Obtener usuario actual
    $userId = null;
    if (isset($_SESSION['user'])) {
        $userId = $_SESSION['user']['id'] ?? null;
    }
    
    // Preparar datos de la venta
    $saleData = [
        'sale_number' => $saleNumber,
        'user_id' => $userId,
        'subtotal' => round($subtotal, 2),
        'tax' => round($tax, 2),
        'discount' => round($discount, 2),
        'total' => round($total, 2),
        'payment_method' => $input['payment_method'] ?? 'cash',
        'payment_status' => 'paid',
        'notes' => $input['notes'] ?? null
    ];
    
    // Insertar venta
    $saleResult = $supabase->insert('sales', $saleData);
    
    if ($saleResult['status'] !== 201) {
        throw new Exception('Error al registrar la venta: ' . ($saleResult['error']['message'] ?? 'Desconocido'));
    }
    
    $saleId = $saleResult['data'][0]['id'];
    
    // Insertar items de la venta y actualizar stock
    foreach ($items as $item) {
        // Insertar item
        $itemData = [
            'sale_id' => $saleId,
            'product_id' => $item['id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'subtotal' => $item['price'] * $item['quantity']
        ];
        
        $itemResult = $supabase->insert('sale_items', $itemData);
        
        if ($itemResult['status'] !== 201) {
            throw new Exception('Error al registrar items de la venta');
        }
        
        // Actualizar stock del producto
        $currentStock = $item['stock'] ?? 0;
        $newStock = max(0, $currentStock - $item['quantity']);
        
        $updateResult = $supabase->update('products', $item['id'], [
            'stock' => $newStock,
            'updated_at' => date('c')
        ]);
        
        if ($updateResult['status'] !== 200) {
            // Log error pero continuar
            error_log('Error al actualizar stock del producto ' . $item['id']);
        }
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Venta registrada exitosamente',
        'sale_id' => $saleId,
        'sale_number' => $saleNumber,
        'total' => $total,
        'items_count' => count($items)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}