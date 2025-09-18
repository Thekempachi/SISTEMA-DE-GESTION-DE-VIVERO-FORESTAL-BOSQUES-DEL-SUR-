<?php
// API de autenticación compatible con el sistema existente
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST' && $action === 'login') {
        // Login
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
                'id' => 1,
                'username' => $username,
                'nombre' => ucfirst($username),
                'rol_id' => 1,
                'rol' => 'Admin'
            ];
            
            echo json_encode([
                'ok' => true,
                'user' => [
                    'id' => 1,
                    'username' => $username,
                    'nombre' => ucfirst($username),
                    'rol_id' => 1,
                    'rol' => 'Admin'
                ]
            ]);
        } else {
            throw new Exception('Credenciales inválidas');
        }
        
    } elseif ($method === 'GET' && $action === 'me') {
        // Verificar sesión actual
        session_start();
        
        if (isset($_SESSION['user'])) {
            echo json_encode([
                'ok' => true,
                'user' => $_SESSION['user']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
        }
        
    } elseif ($method === 'POST' && $action === 'logout') {
        // Cerrar sesión
        session_start();
        $_SESSION = [];
        session_destroy();
        
        echo json_encode(['ok' => true]);
        
    } else {
        throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
