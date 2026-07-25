<?php
/**
 * Endpoint: Logout
 * Cierra la sesión del usuario actual (admite GET y POST)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/supabase.php';

$isAjax = ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'));

try {
    // Obtener token de la sesión
    $accessToken = $_SESSION['access_token'] ?? null;
    
    // Si hay token, hacer logout en Supabase
    if ($accessToken) {
        $supabase->signOut($accessToken);
    }
    
    // Destruir sesión local
    session_destroy();
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    } else {
        // Redirección visual amigable para clics en enlaces estándar
        header('Location: ../index.php?page=login');
    }
    exit;
    
} catch (Exception $e) {
    // Incluso si hay error, destruir sesión local
    session_destroy();
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Sesión cerrada (con errores)'
        ]);
    } else {
        header('Location: ../index.php?page=login');
    }
    exit;
}