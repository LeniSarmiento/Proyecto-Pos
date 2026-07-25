<?php
/**
 * Endpoint: Obtener Detalles de Venta
 * Retorna la cabecera de la venta y la lista de productos comprados
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

$saleId = $_GET['sale_id'] ?? null;

if (!$saleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de venta es requerido']);
    exit;
}

try {
    // 1. Obtener cabecera de la venta
    $saleResult = $supabase->from('sales', [
        'select' => '*,profiles(name)',
        'id' => 'eq.' . $saleId
    ]);

    if ($saleResult['status'] !== 200 || empty($saleResult['data'])) {
        throw new Exception('Venta no encontrada');
    }

    $saleData = $saleResult['data'][0];
    
    // Normalizar datos de usuario
    $saleData['user_name'] = !empty($saleData['profiles']) ? $saleData['profiles']['name'] : 'N/A';
    $saleData['customer_name'] = 'Cliente Walk-in'; // En el futuro se puede join con customers

    // 2. Obtener items de la venta con join a productos
    $itemsResult = $supabase->from('sale_items', [
        'select' => '*,products(name,sku,image_url)',
        'sale_id' => 'eq.' . $saleId
    ]);

    if ($itemsResult['status'] !== 200) {
        throw new Exception('Error al obtener los detalles de productos de la venta');
    }

    echo json_encode([
        'success' => true,
        'sale' => $saleData,
        'items' => $itemsResult['data']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}