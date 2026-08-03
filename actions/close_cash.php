<?php
/**
 * Endpoint: Cerrar Caja Chica
 * Actualiza la sesión de caja a cerrada e ingresa el monto final en Supabase
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

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$closingAmount = isset($input['closing_amount']) ? floatval($input['closing_amount']) : null;

if (!$id || $closingAmount === null || $closingAmount < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID de sesión o monto de cierre inválidos']);
    exit;
}

try {
    // Actualizar registro de caja a cerrado
    $result = $supabase->update('cash_register', $id, [
        'closing_amount' => $closingAmount,
        'status' => 'closed',
        'closed_at' => date('c'),
        'notes' => trim($input['notes'] ?? '')
    ]);

    if ($result['status'] === 200) {
        echo json_encode([
            'success' => true,
            'message' => 'Caja cerrada exitosamente'
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al cerrar caja en Supabase');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}