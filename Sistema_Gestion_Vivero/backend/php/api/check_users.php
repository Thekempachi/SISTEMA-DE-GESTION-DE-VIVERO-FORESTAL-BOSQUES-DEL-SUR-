<?php
require_once __DIR__ . '/../conection.php';

// Solo permitir si APP_DEBUG=1
if (getenv('APP_DEBUG') !== '1') {
    http_response_code(403);
    send_json(['error' => 'Acceso denegado. Habilita APP_DEBUG=1']);
}

try {
    $pdo = db();
    
    // Obtener todos los usuarios
    $stmt = $pdo->query('SELECT id, username, nombre, rol_id, LENGTH(password_hash) as hash_length, 
                         SUBSTRING(password_hash, 1, 10) as hash_preview 
                         FROM usuarios ORDER BY id');
    $users = $stmt->fetchAll();
    
    // Obtener roles
    $stmt = $pdo->query('SELECT id, nombre FROM roles ORDER BY id');
    $roles = $stmt->fetchAll();
    
    send_json([
        'ok' => true,
        'users' => $users,
        'roles' => $roles,
        'total_users' => count($users),
        'message' => 'Usuarios encontrados. Usa credenciales: admin/admin, tecnico1/tecnico, etc.'
    ]);
    
} catch (Exception $e) {
    send_json(['error' => 'Error consultando usuarios: ' . $e->getMessage()], 500);
}