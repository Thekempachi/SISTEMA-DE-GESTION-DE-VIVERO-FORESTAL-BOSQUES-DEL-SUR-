<?php
// Endpoint para probar permisos de roles
require_once __DIR__ . '/../conection.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar autenticación
    $user = require_auth();

    $action = $_GET['action'] ?? 'info';

    switch ($action) {
        case 'admin_only':
            require_role(1); // Solo Admin
            send_json(['success' => true, 'message' => 'Acceso concedido a función de Admin', 'user' => $user]);

        case 'tecnico_plus':
            require_role([1, 2]); // Admin o Técnico
            send_json(['success' => true, 'message' => 'Acceso concedido a función de Técnico+', 'user' => $user]);

        case 'logistica_plus':
            require_role([1, 2, 3]); // Admin, Técnico o Logística
            send_json(['success' => true, 'message' => 'Acceso concedido a función de Logística+', 'user' => $user]);

        case 'info':
        default:
            send_json([
                'success' => true,
                'message' => 'Información de usuario autenticado',
                'user' => $user,
                'permissions' => [
                    'can_admin' => in_array($user['rol_id'], [1]),
                    'can_tecnico' => in_array($user['rol_id'], [1, 2]),
                    'can_logistica' => in_array($user['rol_id'], [1, 2, 3]),
                    'can_basic' => in_array($user['rol_id'], [1, 2, 3, 4])
                ]
            ]);
    }

} catch (Throwable $e) {
    send_json(['error' => $e->getMessage()], 403);
}