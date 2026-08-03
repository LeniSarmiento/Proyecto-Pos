<?php
/**
 * Endpoint: Obtener Historial de Cierres de Caja
 * Retorna todos los turnos abiertos y cerrados de todos los cajeros
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

// Verificar login y que sea administrador
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado. Solo administradores pueden ver el historial de caja.']);
    exit;
}

try {
    // Consultar el historial completo de la tabla cash_register uniendo con profiles para saber quién operó
    $result = $supabase->from('cash_register', [
        'select' => '*,profiles(name,email)',
        'order' => 'opened_at.desc'
    ]);

    if ($result['status'] === 200 && isset($result['data'])) {
        // Formatear datos
        $history = array_map(function($session) {
            return [
                'id' => $session['id'],
                'opened_at' => $session['opened_at'],
                'closed_at' => $session['closed_at'],
                'opening_amount' => floatval($session['opening_amount'] ?? 0),
                'closing_amount' => $session['closing_amount'] !== null ? floatval($session['closing_amount']) : null,
                'status' => $session['status'],
                'notes' => $session['notes'] ?? '',
                'user_name' => !empty($session['profiles']) ? $session['profiles']['name'] : 'N/A',
                'user_email' => !empty($session['profiles']) ? $session['profiles']['email'] : 'N/A'
            ];
        }, $result['data']);

        echo json_encode([
            'success' => true,
            'data' => $history,
            'count' => count($history)
        ]);
    } else {
        throw new Exception($result['error']['message'] ?? 'Error al obtener el historial de turnos de caja');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}