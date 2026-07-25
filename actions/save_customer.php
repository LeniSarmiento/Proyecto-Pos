<?php
/**
 * Endpoint: Guardar/Editar Cliente
 * Inserta o actualiza un cliente en Supabase
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

if (!$input || empty($input['name']) || empty($input['ruc_dni'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o faltantes']);
    exit;
}

try {
    $id = $input['id'] ?? null;
    
    $customerData = [
        'name' => trim($input['name']),
        'ruc_dni' => trim($input['ruc_dni']),
        'phone' => trim($input['phone'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'address' => trim($input['address'] ?? ''),
        'updated_at' => date('c')
    ];

    if ($id) {
        // ACTUALIZACIÓN
        $result = $supabase->update('customers', $id, $customerData);
        if ($result['status'] === 200) {
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado']);
        } else {
            throw new Exception($result['error']['message'] ?? 'Error al actualizar cliente');
        }
    } else {
        // INSERCIÓN
        // Primero verificar si el RUC/DNI ya existe
        $checkResult = $supabase->from('customers', [
            'select' => 'id',
            'ruc_dni' => 'eq.' . $customerData['ruc_dni']
        ]);
        
        if ($checkResult['status'] === 200 && !empty($checkResult['data'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'El DNI o RUC ingresado ya está registrado']);
            exit;
        }

        $customerData['created_at'] = date('c');

        $result = $supabase->insert('customers', $customerData);
        if ($result['status'] === 201) {
            echo json_encode(['success' => true, 'message' => 'Cliente registrado', 'data' => $result['data'][0]]);
        } else {
            throw new Exception($result['error']['message'] ?? 'Error al registrar cliente');
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}