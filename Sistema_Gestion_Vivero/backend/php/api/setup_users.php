<?php
require_once __DIR__ . '/../conection.php';

// Solo permitir si APP_DEBUG=1
if (getenv('APP_DEBUG') !== '1') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Deshabilitado. Define APP_DEBUG=1 para usar setup_users.']);
    exit;
}

try {
    $pdo = db();
    
    // Verificar si la tabla roles existe y tiene datos
    $roles_count = 0;
    try {
        $stmt = $pdo->query('SELECT COUNT(*) AS c FROM roles');
        $roles_count = (int)($stmt->fetch()['c'] ?? 0);
    } catch (Throwable $e) {
        // La tabla roles no existe, crearla
        $pdo->exec("
            CREATE TABLE roles (
                id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(50) NOT NULL
            ) ENGINE=InnoDB
        ");
    }
    
    // Insertar roles si no existen
    if ($roles_count === 0) {
        $roles = [
            ['nombre' => 'Administrador'],
            ['nombre' => 'Técnico'],
            ['nombre' => 'Logística'],
            ['nombre' => 'Usuario']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO roles (nombre) VALUES (?)");
        foreach ($roles as $role) {
            $stmt->execute([$role['nombre']]);
        }
    }
    
    // Verificar si la tabla usuarios existe
    $usuarios_count = 0;
    try {
        $stmt = $pdo->query('SELECT COUNT(*) AS c FROM usuarios');
        $usuarios_count = (int)($stmt->fetch()['c'] ?? 0);
    } catch (Throwable $e) {
        // La tabla usuarios no existe, crearla
        $pdo->exec("
            CREATE TABLE usuarios (
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
    }
    
    // Insertar usuarios si no existen
    if ($usuarios_count === 0) {
        $usuarios = [
            [
                'username' => 'admin',
                'password_hash' => 'hash_admin', // Será verificado como texto plano
                'nombre' => 'Administrador del Sistema',
                'rol_id' => 1
            ],
            [
                'username' => 'tecnico1',
                'password_hash' => 'hash_tecnico',
                'nombre' => 'Técnico Forestal',
                'rol_id' => 2
            ],
            [
                'username' => 'logi1',
                'password_hash' => 'hash_logi',
                'nombre' => 'Coordinador de Logística',
                'rol_id' => 3
            ],
            [
                'username' => 'user1',
                'password_hash' => 'hash_user',
                'nombre' => 'Usuario Operativo',
                'rol_id' => 4
            ]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, password_hash, nombre, rol_id) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($usuarios as $usuario) {
            $stmt->execute([
                $usuario['username'],
                $usuario['password_hash'],
                $usuario['nombre'],
                $usuario['rol_id']
            ]);
        }
    }
    
    // Verificar los usuarios creados
    $stmt = $pdo->query("
        SELECT u.username, u.nombre, r.nombre as rol 
        FROM usuarios u 
        LEFT JOIN roles r ON u.rol_id = r.id
    ");
    
    $usuarios_creados = $stmt->fetchAll();
    
    send_json([
        'ok' => true,
        'message' => 'Base de datos configurada correctamente',
        'roles_count' => $roles_count,
        'usuarios_count' => $usuarios_count,
        'usuarios' => $usuarios_creados,
        'credenciales' => [
            'admin' => 'admin',
            'tecnico1' => 'tecnico',
            'logi1' => 'logistica',
            'user1' => 'user'
        ]
    ]);
    
} catch (Throwable $e) {
    send_json([
        'error' => 'Error al configurar la base de datos: ' . $e->getMessage()
    ], 500);
}
