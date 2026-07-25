<?php
/**
 * Endpoint: Guardar/Editar Producto
 * Inserta o actualiza un producto en Supabase
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

if (!$input || empty($input['name']) || empty($input['sku']) || !isset($input['price'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o faltantes']);
    exit;
}

try {
    $id = $input['id'] ?? null;
    
    $productData = [
        'name' => trim($input['name']),
        'sku' => strtoupper(trim($input['sku'])),
        'category' => trim($input['category'] ?? 'General'),
        'cost' => floatval($input['cost'] ?? 0),
        'price' => floatval($input['price']),
        'stock' => intval($input['stock'] ?? 0),
        'min_stock' => intval($input['min_stock'] ?? 5),
        'image_url' => trim($input['image_url'] ?? ''),
        'description' => trim($input['description'] ?? ''),
        'is_active' => (bool)($input['is_active'] ?? true),
        'updated_at' => date('c')
    ];

    if ($id) {
        // ACTUALIZACIÓN
        $result = $supabase->update('products', $id, $productData);
        if ($result['status'] === 200) {
            echo json_encode(['success' => true, 'message' => 'Producto actualizado']);
        } else {
            throw new Exception($result['error']['message'] ?? 'Error al actualizar producto');
        }
    } else {
        // INSERCIÓN
        // Primero verificar si el SKU ya existe
        $checkResult = $supabase->from('products', [
            'select' => 'id',
            'sku' => 'eq.' . $productData['sku']
        ]);
        
        if ($checkResult['status'] === 200 && !empty($checkResult['data'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'El SKU ingresado ya está registrado por otro producto']);
            exit;
        }

        // Remover id para que Supabase lo auto-genere
        unset($productData['id']);
        $productData['created_at'] = date('c');

        $result = $supabase->insert('products', $productData);
        if ($result['status'] === 201) {
            echo json_encode(['success' => true, 'message' => 'Producto creado', 'data' => $result['data'][0]]);
        } else {
            throw new Exception($result['error']['message'] ?? 'Error al crear producto');
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}