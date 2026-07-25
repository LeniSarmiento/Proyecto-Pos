<?php
/**
 * Endpoint: Importar Productos desde Excel (CSV)
 * Procesa el archivo subido y realiza un Upsert masivo en Supabase
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

// Validar que se haya subido un archivo
if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No se subió ningún archivo o el archivo está corrupto']);
    exit;
}

$tmpPath = $_FILES['import_file']['tmp_name'];
$fileName = $_FILES['import_file']['name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($fileExtension !== 'csv') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Formato de archivo inválido. Por favor, sube un archivo con extensión .csv (Excel delimitado por punto y coma)']);
    exit;
}

try {
    $handle = fopen($tmpPath, 'r');
    if (!$handle) {
        throw new Exception('No se pudo abrir el archivo para su lectura');
    }

    // Saltar el BOM UTF-8 si existe
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") {
        rewind($handle); // Regresar al inicio si no tiene BOM
    }

    // Leer la cabecera para determinar el delimitador (; o ,)
    $firstLine = fgets($handle);
    $delimiter = ';';
    if (strpos($firstLine, ';') === false && strpos($firstLine, ',') !== false) {
        $delimiter = ',';
    }
    
    // Regresar al inicio y omitir la cabecera
    rewind($handle);
    if ($bom === "\xEF\xBB\xBF") {
        fread($handle, 3); // Volver a saltar el BOM
    }
    fgetcsv($handle, 2000, $delimiter); // Saltar fila de cabecera

    $importedCount = 0;
    $errors = [];
    $rowNumber = 1;

    while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
        $rowNumber++;
        
        // Validar que tenga el número mínimo de columnas
        if (count($row) < 5) {
            $errors[] = "Fila $rowNumber: Columnas incompletas (mínimo SKU, Nombre, Costo, Precio, Stock).";
            continue;
        }

        // Mapear columnas según el orden de la plantilla
        $sku = strtoupper(trim($row[0] ?? ''));
        $name = trim($row[1] ?? '');
        $category = trim($row[2] ?? 'General');
        $cost = floatval($row[3] ?? 0);
        $price = floatval($row[4] ?? 0);
        $stock = intval($row[5] ?? 0);
        $minStock = intval($row[6] ?? 5);
        $sizes = trim($row[7] ?? '');
        $igv = floatval($row[8] ?? 18.00);
        $description = trim($row[9] ?? '');

        // Validaciones básicas de campos obligatorios
        if (empty($sku)) {
            $errors[] = "Fila $rowNumber: El SKU es obligatorio.";
            continue;
        }
        if (empty($name)) {
            $errors[] = "Fila $rowNumber: El Nombre es obligatorio.";
            continue;
        }
        if ($price <= 0) {
            $errors[] = "Fila $rowNumber (SKU $sku): El precio debe ser mayor a 0.";
            continue;
        }

        // Validar porcentaje de IGV
        if ($igv != 18.00 && $igv != 10.50) {
            $igv = 18.00; // Por defecto
        }

        // Estructurar datos del producto
        $productData = [
            'name' => $name,
            'sku' => $sku,
            'category' => $category ?: 'General',
            'cost' => $cost,
            'price' => $price,
            'stock' => $stock,
            'min_stock' => $minStock,
            'sizes' => $sizes,
            'igv' => $igv,
            'description' => $description,
            'is_active' => true,
            'updated_at' => date('c')
        ];

        // Verificar si el SKU ya existe en la base de datos para hacer UPDATE (Upsert)
        $checkResult = $supabase->from('products', [
            'select' => 'id',
            'sku' => 'eq.' . $sku
        ]);

        if ($checkResult['status'] === 200 && !empty($checkResult['data'])) {
            // Existe -> Actualizar
            $existingId = $checkResult['data'][0]['id'];
            $updateResult = $supabase->update('products', $existingId, $productData);
            
            if ($updateResult['status'] === 200) {
                $importedCount++;
            } else {
                $errors[] = "Fila $rowNumber (SKU $sku): Error al actualizar: " . ($updateResult['error']['message'] ?? 'Desconocido');
            }
        } else {
            // No existe -> Insertar nuevo
            $productData['created_at'] = date('c');
            $insertResult = $supabase->insert('products', $productData);
            
            if ($insertResult['status'] === 201) {
                $importedCount++;
            } else {
                $errors[] = "Fila $rowNumber (SKU $sku): Error al insertar: " . ($insertResult['error']['message'] ?? 'Desconocido');
            }
        }
    }

    fclose($handle);

    // Responder
    if (empty($errors)) {
        echo json_encode([
            'success' => true,
            'imported' => $importedCount,
            'message' => "Se importaron $importedCount productos correctamente."
        ]);
    } else if ($importedCount > 0) {
        echo json_encode([
            'success' => true,
            'imported' => $importedCount,
            'error' => "Se importaron $importedCount productos, pero hubo algunos errores: " . implode(" | ", $errors)
        ]);
    } else {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => "No se pudo importar ningún producto. Errores detectados: " . implode(" | ", $errors)
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}