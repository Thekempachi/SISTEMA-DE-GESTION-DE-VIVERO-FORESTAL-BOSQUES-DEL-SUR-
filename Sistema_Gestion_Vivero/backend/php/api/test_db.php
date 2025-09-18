<?php
// Script para probar la conexión a la base de datos
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../conection.php';

try {
    // Probar conexión
    $pdo = db();
    
    $result = [
        'ok' => true,
        'message' => 'Conexión a base de datos exitosa',
        'database_info' => [
            'host' => DB_HOST,
            'database' => DB_NAME,
            'user' => DB_USER
        ]
    ];
    
    // Verificar si existen las tablas principales
    $tables = ['usuarios', 'especies', 'lotes_produccion', 'plantas'];
    $existing_tables = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $existing_tables[] = $table;
            }
        } catch (Exception $e) {
            // Tabla no existe
        }
    }
    
    $result['tables'] = [
        'required' => $tables,
        'existing' => $existing_tables,
        'missing' => array_diff($tables, $existing_tables)
    ];
    
    // Si faltan tablas, sugerir crear
    if (count($result['tables']['missing']) > 0) {
        $result['suggestion'] = 'Algunas tablas faltan. Ejecuta el script de inicialización.';
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error de conexión a base de datos',
        'details' => $e->getMessage(),
        'suggestion' => 'Verifica las credenciales de la base de datos en conection.php'
    ], JSON_PRETTY_PRINT);
}
?>
