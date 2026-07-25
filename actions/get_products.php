<?php
/**
 * Endpoint: Obtener Productos
 * Retorna todos los productos activos desde Supabase
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

try {
    // Consultar productos activos ordenados por nombre
    $result = $supabase->from('products', [
        'select' => '*',
        'is_active' => 'eq.true',
        'order' => 'name.asc'
    ]);
    
    if ($result['status'] === 200 && isset($result['data'])) {
        echo json_encode([
            'success' => true,
            'data' => $result['data'],
            'count' => count($result['data'])
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al obtener productos');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}