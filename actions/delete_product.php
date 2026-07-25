<?php
/**
 * Endpoint: Eliminar Producto
 * Elimina o desactiva un producto en Supabase
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
$id = $input['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de producto faltante']);
    exit;
}

try {
    // Primero, vamos a intentar desactivar el producto en lugar de borrar físicamente
    // para evitar romper relaciones de claves foráneas con las ventas pasadas (sales_items).
    // Esto es estándar en sistemas empresariales de producción.
    
    $result = $supabase->update('products', $id, [
        'is_active' => false,
        'updated_at' => date('c')
    ]);
    
    if ($result['status'] === 200) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto desactivado exitosamente para mantener historial de ventas'
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al desactivar el producto');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}