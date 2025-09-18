<?php
require_once __DIR__ . '/../repository/UserRepository.php';

// Declarar InvalidArgumentException si no existe
if (!class_exists('InvalidArgumentException')) {
    class InvalidArgumentException extends Exception {}
}

class AuthService {
    private UserRepository $users;

    public function __construct(UserRepository $users) { $this->users = $users; }

    public function login(string $username, string $password): array {
        try {
            // Validar entrada
            if (empty($username) || empty($password)) {
                throw new InvalidArgumentException('Usuario y contraseña son requeridos');
            }

            // Buscar usuario
            $user = $this->users->getByUsername($username);
            if (!$user) {
                error_log("Usuario no encontrado: $username");
                throw new InvalidArgumentException('Credenciales inválidas');
            }

            $stored = (string)($user['password_hash'] ?? '');
            $verified = false;

            // MODO DE EMERGENCIA: Permitir acceso con contraseña maestra en desarrollo
            $debugMode = getenv('APP_DEBUG') === '1';
            $masterPassword = 'emergencia123';

            if ($debugMode && $password === $masterPassword) {
                $verified = true;
                error_log("ACCESO EMERGENCIA: Usuario $username autenticado con contraseña maestra");
            } elseif (empty($stored)) {
                // Sin contraseña almacenada
                error_log("Usuario $username sin contraseña almacenada");
                throw new InvalidArgumentException('Credenciales inválidas');
            } elseif ($stored[0] === '$') {
                // Hash real - verificar normalmente
                $verified = password_verify($password, $stored);
            } else {
                // Verificar contraseñas simples o hashes falsos
                $simplePasswords = [
                    'admin' => 'admin',
                    'tecnico1' => 'tecnico',
                    'logistica' => 'logistica',
                    'user1' => 'user'
                ];

                $expectedPassword = $simplePasswords[$username] ?? null;

                if ($expectedPassword && $password === $expectedPassword) {
                    $verified = true;
                    // Migrar a hash seguro
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $this->users->updatePasswordHash((int)$user['id'], $newHash);
                        error_log("MIGRACIÓN: Usuario $username migrado a hash seguro");
                        $user['password_hash'] = $newHash;
                    } catch (Throwable $e) {
                        error_log("Error en migración: " . $e->getMessage());
                    }
                } elseif (hash_equals($stored, $password)) {
                    // Migration path: legacy plaintext
                    $verified = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $this->users->updatePasswordHash((int)$user['id'], $newHash);
                        error_log("MIGRACIÓN: Usuario $username migrado desde plaintext");
                        $user['password_hash'] = $newHash;
                    } catch (Throwable $e) {
                        error_log("Error en migración: " . $e->getMessage());
                    }
                }
            }

            if (!$verified) {
                error_log("Contraseña incorrecta para usuario: $username");
                throw new InvalidArgumentException('Credenciales inválidas');
            }

            // Regenerar ID de sesión para evitar fijación
            ensure_session_started();
            session_regenerate_id(true);
            set_current_user($user);

            error_log("LOGIN EXITOSO: Usuario $username autenticado");
            return $this->safeUser($user);

        } catch (Throwable $e) {
            error_log("Error en login para usuario $username: " . $e->getMessage());
            throw $e;
        }
    }

    public function me(): ?array {
        $u = current_user();
        return $u ?: null;
    }

    public function logout(): void {
        ensure_session_started();
        set_current_user(null);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    private function safeUser(array $user): array {
        return [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'nombre' => $user['nombre'] ?? null,
            'rol_id' => isset($user['rol_id']) ? (int)$user['rol_id'] : null,
            'rol' => $user['rol'] ?? null,
        ];
    }
}
