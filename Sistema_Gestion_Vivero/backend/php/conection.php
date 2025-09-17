<?php
// Simple PDO connection helper and JSON response utilities
// Hostinger shared hosting friendly: no Composer/.env required

// Basic CORS for development: reflect Origin and allow credentials
// In producción, restringe el origen explícitamente.
function setup_cors(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

setup_cors();

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'u605613151_vivero_bosques');
define('DB_USER', getenv('DB_USER') ?: 'u605613151_bosques_sur');
define('DB_PASS', getenv('DB_PASS') ?: 'C0ntrsen@102');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // Ensure utf8mb4 for the session
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    return $pdo;
}

function json_input(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function send_json($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function http_method(): string {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

function require_fields(array $data, array $fields): void {
    foreach ($fields as $f) {
        if (!isset($data[$f]) || $data[$f] === '') {
            send_json(["error" => "Campo requerido: $f"], 400);
        }
    }
}

// ---- Session & Auth helpers ----
function ensure_session_started(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Secure session cookie flags when possible
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function current_user(): ?array {
    ensure_session_started();
    return $_SESSION['user'] ?? null;
}

function set_current_user(?array $user): void {
    ensure_session_started();
    if ($user === null) {
        unset($_SESSION['user']);
    } else {
        // Store minimal safe profile
        $_SESSION['user'] = [
            'id' => $user['id'] ?? null,
            'username' => $user['username'] ?? null,
            'nombre' => $user['nombre'] ?? null,
            'rol_id' => $user['rol_id'] ?? null,
            'rol' => $user['rol'] ?? null,
        ];
    }
}

function require_auth(): array {
    $u = current_user();
    if (!$u) send_json(['error' => 'No autenticado'], 401);
    return $u;
}

function require_role($allowed): array {
    $u = require_auth();
    $allowedSet = is_array($allowed) ? $allowed : [$allowed];
    if (!in_array($u['rol_id'] ?? null, $allowedSet, true) && !in_array($u['rol'] ?? null, $allowedSet, true)) {
        send_json(['error' => 'No autorizado'], 403);
    }
    return $u;
}

?>
