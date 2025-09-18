<?php
require_once __DIR__ . '/../conection.php';

// Script para inicializar la base de datos con datos de prueba
// Solo permitir en desarrollo
if (getenv('APP_DEBUG') !== '1' && (getenv('APP_ENV') !== 'development')) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Este script solo puede ejecutarse en modo desarrollo.']);
    exit;
}

try {
    $pdo = db();
    $results = [];
    
    // 1. Crear tabla roles si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS roles (
            id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB
    ");
    $results[] = "Tabla 'roles' verificada/creada";
    
    // 2. Insertar roles básicos si no existen
    $roles = [
        ['nombre' => 'Administrador'],
        ['nombre' => 'Técnico'],
        ['nombre' => 'Logística'],
        ['nombre' => 'Usuario']
    ];
    
    foreach ($roles as $role) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO roles (nombre) VALUES (?)");
        $stmt->execute([$role['nombre']]);
    }
    $results[] = "Roles básicos insertados";
    
    // 3. Crear tabla usuarios si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            rol_id TINYINT UNSIGNED NOT NULL,
            email VARCHAR(100),
            mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
            last_login TIMESTAMP NULL,
            failed_attempts INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (rol_id) REFERENCES roles(id)
        ) ENGINE=InnoDB
    ");
    $results[] = "Tabla 'usuarios' verificada/creada";
    
    // 4. Insertar usuarios de prueba si no existen
    $usuarios = [
        [
            'username' => 'admin',
            'password_hash' => 'hash_admin',
            'nombre' => 'Administrador del Sistema',
            'rol_id' => 1,
            'email' => 'admin@vivero.com'
        ],
        [
            'username' => 'tecnico1',
            'password_hash' => 'hash_tecnico',
            'nombre' => 'Técnico Forestal',
            'rol_id' => 2,
            'email' => 'tecnico@vivero.com'
        ],
        [
            'username' => 'logi1',
            'password_hash' => 'hash_logi',
            'nombre' => 'Coordinador de Logística',
            'rol_id' => 3,
            'email' => 'logistica@vivero.com'
        ],
        [
            'username' => 'user1',
            'password_hash' => 'hash_user',
            'nombre' => 'Usuario Operativo',
            'rol_id' => 4,
            'email' => 'user@vivero.com'
        ]
    ];
    
    foreach ($usuarios as $usuario) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO usuarios (username, password_hash, nombre, rol_id, email) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $usuario['username'],
            $usuario['password_hash'],
            $usuario['nombre'],
            $usuario['rol_id'],
            $usuario['email']
        ]);
    }
    $results[] = "Usuarios de prueba insertados";
    
    // 5. Verificar los datos insertados
    $stmt = $pdo->query("
        SELECT u.username, u.nombre, r.nombre as rol 
        FROM usuarios u 
        LEFT JOIN roles r ON u.rol_id = r.id
        ORDER BY u.id
    ");
    
    $usuarios_creados = $stmt->fetchAll();
    
    send_json([
        'ok' => true,
        'message' => 'Base de datos inicializada correctamente',
        'steps' => $results,
        'usuarios' => $usuarios_creados,
        'credenciales' => [
            'admin' => 'admin',
            'tecnico1' => 'tecnico',
            'logi1' => 'logistica',
            'user1' => 'user'
        ],
        'nota' => 'Use la contraseña maestra "emergencia123" si tiene problemas para iniciar sesión'
    ]);
    
} catch (Throwable $e) {
    send_json([
        'error' => 'Error al inicializar la base de datos: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 500);
}
