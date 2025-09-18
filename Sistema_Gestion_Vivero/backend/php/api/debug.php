<?php
// Archivo de diagnóstico simple para verificar configuración
require_once __DIR__ . '/../conection.php';

header('Content-Type: application/json; charset=utf-8');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'UNKNOWN',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'UNKNOWN',
    'app_debug' => getenv('APP_DEBUG'),
    'db_config' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass_set' => !empty(DB_PASS)
    ]
];

// Intentar conexión a BD
try {
    $pdo = db();
    $debug['db_connection'] = 'SUCCESS';

    // Verificar tabla usuarios
    try {
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM usuarios LIMIT 1');
        $result = $stmt->fetch();
        $debug['usuarios_table'] = 'EXISTS - Count: ' . $result['count'];

        // Verificar usuarios de prueba
        $stmt = $pdo->query('SELECT username, password_hash FROM usuarios LIMIT 5');
        $users = $stmt->fetchAll();
        $debug['sample_users'] = array_map(function($u) {
            return [
                'username' => $u['username'],
                'has_hash' => !empty($u['password_hash']) && strlen($u['password_hash']) > 10
            ];
        }, $users);

    } catch (Exception $e) {
        $debug['usuarios_table'] = 'ERROR: ' . $e->getMessage();
    }

    // Verificar tablas de catálogo
    $catalogTables = ['tipo_especie', 'estado_salud', 'fases_produccion', 'ubicaciones'];
    $debug['catalog_tables'] = [];
    foreach ($catalogTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $debug['catalog_tables'][$table] = 'EXISTS - Count: ' . $result['count'];
        } catch (Exception $e) {
            $debug['catalog_tables'][$table] = 'MISSING: ' . $e->getMessage();
        }
    }

} catch (Exception $e) {
    $debug['db_connection'] = 'FAILED: ' . $e->getMessage();
}

// Verificar usuario actual si está autenticado
$current_user = null;
try {
    $current_user = current_user();
    if ($current_user) {
        $current_user = [
            'id' => $current_user['id'],
            'username' => $current_user['username'],
            'nombre' => $current_user['nombre'],
            'rol_id' => $current_user['rol_id'],
            'rol' => $current_user['rol']
        ];
    }
} catch (Exception $e) {
    $current_user = ['error' => 'No autenticado'];
}

$debug['current_user'] = $current_user;

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);