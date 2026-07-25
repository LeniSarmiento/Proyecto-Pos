<?php
/**
 * Endpoint: Guardar/Editar Producto
 * Inserta o actualiza un producto en Supabase con soporte para archivos
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

// Recolectar de $_POST (ya que se envía como FormData / multipart)
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$sku = $_POST['sku'] ?? '';
$price = $_POST['price'] ?? '';

if (empty($name) || empty($sku) || $price === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos o faltantes']);
    exit;
}

try {
    // Procesar la subida física del archivo si existe
    $imageUrl = $_POST['image_url'] ?? ''; // Conservar imagen previa por defecto
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['image_file']['tmp_name'];
        $fileName = $_FILES['image_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            
            // Nombre de archivo seguro e irrepetible
            $newFileName = 'prod_' . date('YmdHis') . '_' . uniqid() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($tmpPath, $destPath)) {
                $imageUrl = 'uploads/' . $newFileName;
            } else {
                throw new Exception('No se pudo guardar la imagen en el servidor');
            }
        } else {
            throw new Exception('Formato de imagen no permitido (solo JPG, PNG, WEBP, SVG)');
        }
    }
    
    $productData = [
        'name' => trim($name),
        'sku' => strtoupper(trim($sku)),
        'category' => trim($_POST['category'] ?? 'General'),
        'cost' => floatval($_POST['cost'] ?? 0),
        'price' => floatval($price),
        'stock' => intval($_POST['stock'] ?? 0),
        'min_stock' => intval($_POST['min_stock'] ?? 5),
        'image_url' => $imageUrl,
        'sizes' => trim($_POST['sizes'] ?? ''),
        'igv' => floatval($_POST['igv'] ?? 18.00),
        'description' => trim($_POST['description'] ?? ''),
        'is_active' => (bool)($_POST['is_active'] ?? true),
        'updated_at' => date('c')
    ];

    if ($id) {
        // ACTUALIZACIÓN
        $result = $supabase->update('products', $id, $productData);
        if ($result['status'] === 200) {
            echo json_encode(['success' => true, 'message' => 'Producto actualizado']);
        } else {
            throw new Exception($result['error']['message'] ?? 'Error al actualizar producto');
        }
    } else {
        // INSERCIÓN
        // Primero verificar si el SKU ya existe
        $checkResult = $supabase->from('products', [
            'select' => 'id',
            'sku' => 'eq.' . $productData['sku']
        ]);
        
        if ($checkResult['status'] === 200 && !empty($checkResult['data'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'El SKU ingresado ya está registrado por otro producto']);
            exit;
        }

        // Remover id para que Supabase lo auto-genere
        unset($productData['id']);
        $productData['created_at'] = date('c');

        $result = $supabase->insert('products', $productData);
        if ($result['status'] === 201) {
            echo json_encode(['success' => true, 'message' => 'Producto creado', 'data' => $result['data'][0]]);
        } else {
            throw new Exception($result['error']['message'] ?? 'Error al crear producto');
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}