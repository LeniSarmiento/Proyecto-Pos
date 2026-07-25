<?php
/**
 * Endpoint: Descargar Plantilla Excel (CSV)
 * Genera y descarga una plantilla de importación compatible con Excel
 */

// Cabeceras HTTP para forzar descarga del archivo
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="plantilla_productos_arquitec.csv"');

// Crear puntero de archivo de salida
$output = fopen('php://output', 'w');

// Escribir el BOM UTF-8 para compatibilidad total con Microsoft Excel
fwrite($output, "\xEF\xBB\xBF");

// Definir los encabezados de las columnas
$headers = [
    'sku',          // Código único (SKU)
    'nombre',       // Nombre del producto
    'categoria',    // Categoría (Casacas, Pantalones, etc.)
    'costo',        // Costo de compra (número)
    'precio',       // Precio de venta al público (número)
    'stock',        // Stock inicial (entero)
    'min_stock',    // Stock mínimo de alerta (entero, ej: 5)
    'tallas',       // Tallas separadas por comas (ej: S,M,L)
    'igv',          // Impuesto (18.00 o 10.50)
    'descripcion'   // Descripción libre del producto
];

// Escribir los encabezados en el CSV
fputcsv($output, $headers, ';');

// Filas de ejemplo con instrucciones y tipos de datos correctos
$ejemplos = [
    [
        'CASACA-CUERO-EX',
        'Casaca de Cuero Premium',
        'Casacas',
        '120.00',
        '249.99',
        '15',
        '3',
        'M,L,XL',
        '18.00',
        'Casaca de cuero vacuno legítimo con forro térmico interno'
    ],
    [
        'PANT-CHINO-EX',
        'Pantalón Chino Slim Fit',
        'Pantalones',
        '35.00',
        '89.90',
        '30',
        '5',
        '30,32,34',
        '18.00',
        'Pantalón chino stretch ultra cómodo, color beige'
    ],
    [
        'POLO-BASICO-EX',
        'Polo Básico Algodón Pima',
        'Camisetas',
        '12.50',
        '39.00',
        '100',
        '15',
        'S,M,L',
        '10.50',
        'Camiseta básica cuello redondo con algodón peruano de alta calidad'
    ]
];

// Escribir los ejemplos en el CSV
foreach ($ejemplos as $fila) {
    fputcsv($output, $fila, ';');
}

// Cerrar el puntero del archivo
fclose($output);
exit;
