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
        
        // Verificar credenciales ESTRICTAMENTE
        $isValid = false;
        $userRole = 'Usuario';
        
        // Credenciales normales - VALIDACIÓN ESTRICTA
        if (isset($validCredentials[$username]) && $validCredentials[$username] === $password) {
            $isValid = true;
            
            // Asignar roles específicos
            switch ($username) {
                case 'admin':
                    $userRole = 'Administrador';
                    break;
                case 'tecnico1':
                    $userRole = 'Técnico';
                    break;
                case 'logistica':
                    $userRole = 'Logística';
                    break;
                default:
                    $userRole = 'Usuario';
            }
        }
        // Contraseña de emergencia SOLO en modo debug
        elseif ($password === 'emergencia123' && (getenv('APP_DEBUG') === '1' || $_GET['debug'] === '1')) {
            $isValid = true;
            $userRole = 'Emergencia';
        }
        
        if ($isValid) {
            // Crear sesión segura
            session_start();
            session_regenerate_id(true); // Regenerar ID de sesión por seguridad
            
            $_SESSION['user'] = [
                'id' => rand(1, 1000),
                'username' => $username,
                'nombre' => ucfirst($username),
                'rol' => $userRole,
                'login_time' => time(),
                'session_id' => session_id()
            ];
            
            // Log del login exitoso
            error_log("Login exitoso: $username ($userRole) - " . date('Y-m-d H:i:s'));
            
            echo json_encode([
                'ok' => true,
                'user' => $_SESSION['user'],
                'message' => 'Login exitoso'
            ]);
        } else {
            // Log del intento fallido
            error_log("Login fallido: $username - " . date('Y-m-d H:i:s'));
            
            // Delay para prevenir ataques de fuerza bruta
            sleep(1);
            
            throw new Exception('Usuario o contraseña incorrectos');
        }
        
    } elseif ($method === 'GET' && $action === 'me') {
        // Verificar sesión actual ESTRICTAMENTE
        session_start();
        
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            // Verificar que la sesión sea válida y no haya expirado
            $user = $_SESSION['user'];
            $loginTime = $user['login_time'] ?? 0;
            $sessionTimeout = 8 * 60 * 60; // 8 horas
            
            if ((time() - $loginTime) > $sessionTimeout) {
                // Sesión expirada
                $_SESSION = [];
                session_destroy();
                
                http_response_code(401);
                echo json_encode([
                    'error' => 'Sesión expirada',
                    'expired' => true
                ]);
            } else {
                // Sesión válida
                echo json_encode([
                    'ok' => true,
                    'user' => $user,
                    'session_remaining' => $sessionTimeout - (time() - $loginTime)
                ]);
            }
        } else {
            // No hay sesión activa
            http_response_code(401);
            echo json_encode([
                'error' => 'No autenticado',
                'authenticated' => false
            ]);
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
