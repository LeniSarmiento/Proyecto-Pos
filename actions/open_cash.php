<?php
/**
 * Endpoint: Abrir Caja Chica
 * Crea una sesión activa de caja en Supabase
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

$user = getCurrentUser();

// Obtener datos del request
$input = json_decode(file_get_contents('php://input'), true);
$openingAmount = isset($input['opening_amount']) ? floatval($input['opening_amount']) : null;

if ($openingAmount === null || $openingAmount < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Monto inicial de apertura inválido']);
    exit;
}

try {
    // 1. Verificar si ya existe una caja abierta para este usuario
    $checkResult = $supabase->from('cash_register', [
        'select' => 'id',
        'user_id' => 'eq.' . $user['id'],
        'status' => 'eq.open',
        'limit' => '1'
    ]);

    if ($checkResult['status'] === 200 && !empty($checkResult['data'])) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Ya cuentas con un turno de caja activo abierto']);
        exit;
    }

    // 2. Insertar sesión de apertura
    $cashData = [
        'user_id' => $user['id'],
        'opening_amount' => $openingAmount,
        'status' => 'open',
        'opened_at' => date('c'),
        'closed_at' => null, // No cerrado aún
        'notes' => trim($input['notes'] ?? '')
    ];

    $result = $supabase->insert('cash_register', $cashData);

    if ($result['status'] === 201) {
        echo json_encode([
            'success' => true,
            'message' => 'Caja abierta con éxito',
            'session' => $result['data'][0]
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al registrar apertura en base de datos');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}