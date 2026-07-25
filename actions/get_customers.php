<?php
/**
 * Endpoint: Obtener Clientes
 * Retorna todos los clientes registrados en Supabase
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

try {
    $result = $supabase->from('customers', [
        'select' => '*',
        'order' => 'name.asc'
    ]);
    
    if ($result['status'] === 200 && isset($result['data'])) {
        echo json_encode([
            'success' => true,
            'data' => $result['data'],
            'count' => count($result['data'])
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al obtener clientes');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}