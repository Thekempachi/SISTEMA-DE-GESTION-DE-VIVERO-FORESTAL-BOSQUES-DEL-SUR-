<?php
require_once __DIR__ . '/UserRepository.php';

class AuthService {
    private UserRepository $users;

    public function __construct(UserRepository $users) { $this->users = $users; }

    public function login(string $username, string $password): array {
        $user = $this->users->getByUsername($username);
        if (!$user) {
            throw new InvalidArgumentException('Credenciales inválidas');
        }

        $stored = (string)($user['password_hash'] ?? '');
        $isHashLike = strlen($stored) > 0 && $stored[0] === '$';

        $verified = false;
        if ($isHashLike) {
            $verified = password_verify($password, $stored);
        } else {
            // Migration path: legacy plaintext stored in password_hash column
            // If provided password equals the stored plaintext, accept and migrate to a secure hash
            if ($stored !== '' && hash_equals($stored, $password)) {
                $verified = true;
                // Migrate to secure hash immediately
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                try { $this->users->updatePasswordHash((int)$user['id'], $newHash); } catch (Throwable $e) { /* ignore but continue */ }
                $user['password_hash'] = $newHash;
            }
        }

        if (!$verified) {
            throw new InvalidArgumentException('Credenciales inválidas');
        }
        // Regenerar ID de sesión para evitar fijación
        ensure_session_started();
        session_regenerate_id(true);
        set_current_user($user);
        return $this->safeUser($user);
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
