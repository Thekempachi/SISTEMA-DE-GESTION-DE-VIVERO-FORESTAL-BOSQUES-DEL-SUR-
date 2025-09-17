<?php
require_once __DIR__ . '/../conection.php';

// Solo permitir si APP_DEBUG=1
if (getenv('APP_DEBUG') !== '1') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Deshabilitado. Define APP_DEBUG=1 para usar set_password.']);
    exit;
}

try {
    if (http_method() !== 'POST') {
        send_json(['error' => 'Método no permitido'], 405);
    }
    $data = json_input();
    require_fields($data, ['username','new_password']);

    $pdo = db();
    require_once __DIR__ . '/../Domain/Auth/UserRepository.php';
    $repo = new UserRepository($pdo);

    $user = $repo->getByUsername($data['username']);
    if (!$user) send_json(['error' => 'Usuario no existe'], 404);

    $newHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
    $repo->updatePasswordHash((int)$user['id'], $newHash);

    send_json(['ok' => true, 'message' => 'Password actualizado con hash seguro']);
} catch (Throwable $e) {
    send_json(['error' => $e->getMessage()], 500);
}
