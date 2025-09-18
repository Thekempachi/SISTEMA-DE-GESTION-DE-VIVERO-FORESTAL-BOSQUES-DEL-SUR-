<?php
class EspecieRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT e.*, te.nombre AS tipo_especie FROM especies e JOIN tipo_especie te ON te.id = e.tipo_especie_id WHERE e.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function list(string $q = ''): array {
        if ($q !== '') {
            $stmt = $this->pdo->prepare("SELECT e.*, te.nombre AS tipo_especie FROM especies e JOIN tipo_especie te ON te.id = e.tipo_especie_id WHERE e.nombre_comun LIKE ? OR e.nombre_cientifico LIKE ? ORDER BY e.id DESC");
            $stmt->execute(['%'.$q.'%', '%'.$q.'%']);
        } else {
            $stmt = $this->pdo->query("SELECT e.*, te.nombre AS tipo_especie FROM especies e JOIN tipo_especie te ON te.id = e.tipo_especie_id ORDER BY e.id DESC");
        }
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO especies (nombre_cientifico, nombre_comun, tipo_especie_id, categoria, notas, descripcion) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $data['nombre_cientifico'],
            $data['nombre_comun'],
            $data['tipo_especie_id'],
            $data['categoria'] ?? null,
            $data['notas'] ?? null,
            $data['descripcion'] ?? null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->pdo->prepare("UPDATE especies SET nombre_cientifico=?, nombre_comun=?, tipo_especie_id=?, categoria=?, notas=?, descripcion=? WHERE id=?");
        $stmt->execute([
            $data['nombre_cientifico'] ?? null,
            $data['nombre_comun'] ?? null,
            $data['tipo_especie_id'] ?? null,
            $data['categoria'] ?? null,
            $data['notas'] ?? null,
            $data['descripcion'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM especies WHERE id=?");
        $stmt->execute([$id]);
    }
}
