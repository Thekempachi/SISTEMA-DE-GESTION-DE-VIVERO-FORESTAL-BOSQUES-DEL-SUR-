<?php
require_once __DIR__ . '/../conection.php';

// --- Seguridad: Solo permitir en modo de depuración ---
// Este script es una herramienta de desarrollo y no debe ser accesible en producción.
// La variable de entorno APP_DEBUG debe estar en '1' en tu archivo .env o configuración del servidor.
if (getenv('APP_DEBUG') !== '1') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Acción no permitida. Este script es solo para desarrollo.']);
    exit;
}

try {
    // --- Validación del método HTTP ---
    // Solo se permite el método POST para esta operación.
    if (http_method() !== 'POST') {
        send_json(['error' => 'Método no permitido. Se esperaba POST.'], 405);
    }

    // --- Obtención y validación de datos de entrada ---
    $data = json_input();
    require_fields($data, ['username', 'new_password', 'new_password_confirmation']);

    // --- Política de contraseñas ---
    $newPassword = $data['new_password'];
    $newPasswordConfirmation = $data['new_password_confirmation'];

    // 1. Verificar que las contraseñas coincidan
    if ($newPassword !== $newPasswordConfirmation) {
        send_json(['error' => 'Las contraseñas no coinciden.'], 400);
    }

    // 2. Verificar la longitud mínima de la contraseña
    if (strlen($newPassword) < 8) {
        send_json(['error' => 'La contraseña debe tener al menos 8 caracteres.'], 400);
    }

    // --- Conexión a la base de datos y repositorio ---
    $pdo = db();
    require_once __DIR__ . '/../repository/UserRepository.php';
    $userRepository = new UserRepository($pdo);

    // --- Verificación de existencia del usuario ---
    $user = $userRepository->getByUsername($data['username']);
    if (!$user) {
        send_json(['error' => 'El usuario especificado no existe.'], 404);
    }

    // --- Actualización de la contraseña ---
    // Se genera un hash seguro de la nueva contraseña. NUNCA guardes contraseñas en texto plano.
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $userRepository->updatePasswordHash((int)$user['id'], $newHash);

    // --- Respuesta de éxito ---
    send_json(['ok' => true, 'message' => 'La contraseña ha sido actualizada correctamente.']);

} catch (Throwable $e) {
    // --- Manejo de errores inesperados ---
    // Si algo sale mal, se captura la excepción y se devuelve un error 500.
    send_json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
}