<?php
/**
 * Endpoint: Login de Usuarios
 * Autentica usuario contra Supabase Auth
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

// Obtener credenciales
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email y contraseña son requeridos']);
    exit;
}

try {
    // Autenticar con Supabase
    $result = $supabase->signIn($email, $password);
    
    if ($result['status'] === 200 && isset($result['data']['access_token'])) {
        $accessToken = $result['data']['access_token'];
        $user = $result['data']['user'];
        
        // Obtener información completa del usuario desde Supabase Auth
        $userResult = $supabase->getUser($accessToken);
        
        if ($userResult['status'] === 200) {
            $userData = $userResult['data'];
            
            // Buscar perfil del usuario en la tabla profiles
            $profileResult = $supabase->from('profiles', [
                'select' => '*',
                'id' => 'eq.' . $userData['id']
            ]);
            
            $profile = null;
            if ($profileResult['status'] === 200 && !empty($profileResult['data'])) {
                $profile = $profileResult['data'][0];
            }
            
            // Guardar sesión
            $_SESSION['user'] = [
                'id' => $userData['id'],
                'email' => $userData['email'],
                'name' => $profile['name'] ?? ($userData['user_metadata']['name'] ?? 'Usuario'),
                'role' => $profile['role'] ?? 'vendedor',
                'avatar_url' => $profile['avatar_url'] ?? null
            ];
            
            $_SESSION['access_token'] = $accessToken;
            
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'access_token' => $accessToken,
                'user' => $_SESSION['user']
            ]);
        } else {
            throw new Exception('Error al obtener información del usuario');
        }
    } else {
        throw new Exception($result['error']['message'] ?? 'Credenciales inválidas');
    }
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}