<?php
/**
 * Configuración de Supabase
 * 
 * Este archivo inicializa el cliente de Supabase usando cURL para las llamadas API.
 * Las credenciales se cargan desde variables de entorno o archivo .env
 */

// Configurar almacenamiento de sesiones privado dentro del proyecto para evitar colisión de permisos en XAMPP
$sessionPath = __DIR__ . '/../sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
@session_save_path($sessionPath);

// Iniciar sesión si aún no está activa para propagar los tokens de autenticación de Supabase (RLS)
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Cargar variables de entorno desde .env si existe
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Saltar comentarios
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Configuración de Supabase
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: '');
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY') ?: '');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: '');
define('APP_NAME', getenv('APP_NAME') ?: 'Punto de Venta Arquitec');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/punto-venta-arquitec');

// Validar configuración
if (empty(SUPABASE_URL) || empty(SUPABASE_ANON_KEY)) {
    die('Error: Configura las variables de entorno de Supabase en el archivo .env');
}

/**
 * Clase cliente para interactuar con Supabase
 */
class SupabaseClient {
    private $url;
    private $anonKey;
    private $serviceKey;
    
    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->anonKey = SUPABASE_ANON_KEY;
        $this->serviceKey = SUPABASE_SERVICE_KEY;
    }
    
    /**
     * Realizar petición HTTP a la API de Supabase
     */
    private function request($endpoint, $method = 'GET', $data = null, $useServiceKey = false) {
        $ch = curl_init();
        
        // Si hay una sesión activa con token, usarlo como Bearer token para que Supabase reconozca al usuario autenticado (RLS)
        $bearerToken = $useServiceKey ? $this->serviceKey : (isset($_SESSION['access_token']) ? $_SESSION['access_token'] : $this->anonKey);
        
        $url = $this->url . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . ($useServiceKey ? $this->serviceKey : $this->anonKey),
            'Authorization: Bearer ' . $bearerToken,
            'Prefer: return=representation'
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return ['error' => $error, 'status' => 0];
        }
        
        $body = json_decode($response, true);
        
        return [
            'data' => $body,
            'status' => $httpCode,
            'error' => $httpCode >= 400 ? $body : null
        ];
    }
    
    // ==================== AUTENTICACIÓN ====================
    
    /**
     * Iniciar sesión con email y contraseña
     */
    public function signIn($email, $password) {
        return $this->request('/auth/v1/token?grant_type=password', 'POST', [
            'email' => $email,
            'password' => $password
        ]);
    }
    
    /**
     * Registrar nuevo usuario
     */
    public function signUp($email, $password, $name = null) {
        $data = [
            'email' => $email,
            'password' => $password
        ];
        
        if ($name) {
            $data['data'] = ['name' => $name];
        }
        
        return $this->request('/auth/v1/signup', 'POST', $data);
    }
    
    /**
     * Cerrar sesión
     */
    public function signOut($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/auth/v1/logout');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 204;
    }
    
    /**
     * Obtener usuario actual
     */
    public function getUser($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/auth/v1/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return ['data' => json_decode($response, true), 'status' => $httpCode];
        }
        
        return ['error' => 'Usuario no autenticado', 'status' => $httpCode];
    }
    
    // ==================== BASE DE DATOS ====================
    
    /**
     * Consultar datos de una tabla
     */
    public function from($table, $params = []) {
        $query = http_build_query($params);
        $endpoint = '/rest/v1/' . $table . ($query ? '?' . $query : '');
        return $this->request($endpoint);
    }
    
    /**
     * Insertar datos en una tabla
     */
    public function insert($table, $data, $useServiceKey = false) {
        return $this->request('/rest/v1/' . $table, 'POST', $data, $useServiceKey);
    }
    
    /**
     * Actualizar datos en una tabla
     */
    public function update($table, $id, $data, $useServiceKey = false) {
        return $this->request('/rest/v1/' . $table . '?id=eq.' . $id, 'PATCH', $data, $useServiceKey);
    }
    
    /**
     * Eliminar datos de una tabla
     */
    public function delete($table, $id) {
        return $this->request('/rest/v1/' . $table . '?id=eq.' . $id, 'DELETE');
    }
    
    // ==================== STORAGE ====================
    
    /**
     * Subir archivo a Storage
     */
    public function uploadFile($bucket, $path, $fileContent, $fileName) {
        $ch = curl_init();
        $url = $this->url . '/storage/v1/object/' . $bucket . '/' . $path . '/' . $fileName;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $this->serviceKey,
            'Authorization: Bearer ' . $this->serviceKey,
            'Content-Type: ' . mime_content_type($fileName)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'url' => $this->url . '/storage/v1/object/public/' . $bucket . '/' . $path . '/' . $fileName,
                'status' => $httpCode
            ];
        }
        
        return [
            'success' => false,
            'error' => json_decode($response, true),
            'status' => $httpCode
        ];
    }
    
    /**
     * Obtener URL pública de un archivo
     */
    public function getPublicUrl($bucket, $path) {
        return $this->url . '/storage/v1/object/public/' . $bucket . '/' . $path;
    }
}

// Instancia global del cliente
$supabase = new SupabaseClient();

/**
 * Helper para verificar sesión
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['access_token']);
}

/**
 * Helper para obtener usuario actual
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Helper para redirigir si no está logueado
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
}