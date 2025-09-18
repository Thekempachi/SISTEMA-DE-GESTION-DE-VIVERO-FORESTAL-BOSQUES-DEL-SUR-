<?php
require_once __DIR__ . '/../conection.php';

// Declarar InvalidArgumentException si no existe
if (!class_exists('InvalidArgumentException')) {
    class InvalidArgumentException extends Exception {}
}

class AuthController {
    public static function handle(): void {
        try {
            $method = http_method();
            $action = $_GET['action'] ?? 'me';

            if ($method === 'GET' && $action === 'me') {
                // No requiere BD
                $me = current_user();
                if (!$me) send_json(['error' => 'No autenticado'], 401);
                send_json(['ok' => true, 'user' => $me]);
            }

            if ($method === 'POST' && $action === 'login') {
                // Requiere BD
                require_once __DIR__ . '/../service/AuthService.php';
                require_once __DIR__ . '/../repository/UserRepository.php';
                $pdo = db();
                $service = new AuthService(new UserRepository($pdo));
                $data = json_input();
                require_fields($data, ['username', 'password']);
                $user = $service->login($data['username'], $data['password']);
                send_json(['ok' => true, 'user' => $user]);
            }

            if ($method === 'POST' && $action === 'logout') {
                // No requiere BD
                ensure_session_started();
                set_current_user(null);
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
                }
                session_destroy();
                send_json(['ok' => true]);
            }

            send_json(['error' => 'Método o acción no permitidos'], 405);
        } catch (Throwable $e) {
            // Log detallado del error
            error_log("AuthController Error: " . $e->getMessage());
            error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());

            // Devuelve códigos adecuados según el tipo de error
            if ($e instanceof InvalidArgumentException) {
                // Credenciales inválidas, no es error del servidor
                send_json(['error' => $e->getMessage()], 401);
            } elseif ($e instanceof PDOException) {
                // Error de base de datos: detallar solo en debug
                $debug = getenv('APP_DEBUG') === '1';
                $msg = $debug ? ('Error de base de datos: ' . $e->getMessage()) : 'Error de base de datos';
                send_json(['error' => $msg], 500);
            } else {
                // Error interno del servidor
                $debug = getenv('APP_DEBUG') === '1';
                $msg = $debug ? ('Error interno: ' . $e->getMessage()) : 'Error interno del servidor';
                send_json(['error' => $msg], 500);
            }
        }
    }
}

