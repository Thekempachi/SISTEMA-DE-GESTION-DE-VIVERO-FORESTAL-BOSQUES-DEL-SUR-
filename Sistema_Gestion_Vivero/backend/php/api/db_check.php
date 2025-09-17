<?php
require_once __DIR__ . '/../conection.php';

// Solo permitir si APP_DEBUG=1
if (getenv('APP_DEBUG') !== '1') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Deshabilitado. Define APP_DEBUG=1 para usar db_check.']);
    exit;
}

$info = [
    'php_version' => PHP_VERSION,
    'pdo_available' => class_exists('PDO'),
    'pdo_drivers' => class_exists('PDO') ? PDO::getAvailableDrivers() : [],
    'env' => [
        'DB_HOST' => getenv('DB_HOST') ?: null,
        'DB_NAME' => getenv('DB_NAME') ?: null,
        'DB_USER' => getenv('DB_USER') ?: null,
        // Nunca devolvemos DB_PASS
    ],
];

try {
    $pdo = db();
    // Conexión OK
    $info['db_connection'] = 'OK';
    // Intentar leer conteo de usuarios y rol
    try {
        $stmt = $pdo->query('SELECT COUNT(*) AS c FROM usuarios');
        $info['usuarios_count'] = (int)($stmt->fetch()['c'] ?? 0);
    } catch (Throwable $e) {
        $info['usuarios_count'] = 'tabla usuarios no disponible: ' . $e->getMessage();
    }
    // Verificar si existe usuario admin
    try {
        $stmt = $pdo->prepare('SELECT id, username, LENGTH(password_hash) AS hash_len FROM usuarios WHERE username = ? LIMIT 1');
        $stmt->execute(['admin']);
        $info['admin_user'] = $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        $info['admin_user'] = 'no accesible: ' . $e->getMessage();
    }

    send_json(['ok' => true, 'diag' => $info]);
} catch (Throwable $e) {
    // Reportar causa exacta de fallo de conexión
    send_json(['error' => 'DB_FAIL: ' . $e->getMessage(), 'diag' => $info], 500);
}
