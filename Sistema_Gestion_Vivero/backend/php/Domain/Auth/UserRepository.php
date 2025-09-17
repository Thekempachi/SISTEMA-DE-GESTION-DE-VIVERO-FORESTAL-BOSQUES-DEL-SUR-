<?php
class UserRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getByUsername(string $username): ?array {
        $stmt = $this->pdo->prepare("SELECT u.*, r.nombre AS rol
            FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE u.username = ? LIMIT 1");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT u.*, r.nombre AS rol
            FROM usuarios u LEFT JOIN roles r ON r.id = u.rol_id WHERE u.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updatePasswordHash(int $id, string $newHash): void {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $id]);
    }
}
