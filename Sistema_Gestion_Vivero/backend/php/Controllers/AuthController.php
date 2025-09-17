<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../Domain/Auth/AuthService.php';
require_once __DIR__ . '/../Domain/Auth/UserRepository.php';

class AuthController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new AuthService(new UserRepository($pdo));
            $method = http_method();
            $action = $_GET['action'] ?? 'me';

            if ($method === 'GET' && $action === 'me') {
                $me = $service->me();
                if (!$me) send_json(['error' => 'No autenticado'], 401);
                send_json(['ok' => true, 'user' => $me]);
            }

            if ($method === 'POST' && $action === 'login') {
                $data = json_input();
                require_fields($data, ['username', 'password']);
                $user = $service->login($data['username'], $data['password']);
                send_json(['ok' => true, 'user' => $user]);
            }

            if ($method === 'POST' && $action === 'logout') {
                $service->logout();
                send_json(['ok' => true]);
            }

            send_json(['error' => 'Método o acción no permitidos'], 405);
        } catch (Throwable $e) {
            send_json(['error' => $e->getMessage()], 500);
        }
    }
}
