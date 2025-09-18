<?php
// API simple de login
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Leer datos JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        throw new Exception('Usuario y contraseña son requeridos');
    }
    
    // Credenciales válidas
    $validCredentials = [
        'admin' => 'admin',
        'tecnico1' => 'tecnico',
        'logistica' => 'logistica',
        'user1' => 'user'
    ];
    
    // Verificar credenciales
    $isValid = false;
    
    // Contraseña de emergencia
    if ($password === 'emergencia123') {
        $isValid = true;
    }
    // Credenciales normales
    elseif (isset($validCredentials[$username]) && $validCredentials[$username] === $password) {
        $isValid = true;
    }
    
    if ($isValid) {
        // Iniciar sesión
        session_start();
        $_SESSION['user'] = [
            'username' => $username,
            'logged_in' => true,
            'login_time' => time()
        ];
        
        echo json_encode([
            'ok' => true,
            'user' => [
                'username' => $username,
                'message' => 'Login exitoso'
            ]
        ]);
    } else {
        throw new Exception('Credenciales inválidas');
    }
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
