<?php
/**
 * Endpoint: Obtener Ventas Recientes
 * Retorna las últimas ventas registradas
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

try {
    // Consultar últimas 10 ventas ordenadas por fecha descendente
    $result = $supabase->from('sales', [
        'select' => '*, profiles(name)',
        'order' => 'created_at.desc',
        'limit' => '10'
    ]);
    
    if ($result['status'] === 200 && isset($result['data'])) {
        // Formatear datos para incluir nombre del vendedor
        $sales = array_map(function($sale) {
            return [
                ...$sale,
                'customer_name' => null, // Se podría hacer join con customers
                'user_name' => !empty($sale['profiles']) ? $sale['profiles']['name'] : 'N/A'
            ];
        }, $result['data']);
        
        echo json_encode([
            'success' => true,
            'data' => $sales,
            'count' => count($sales)
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al obtener ventas');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}