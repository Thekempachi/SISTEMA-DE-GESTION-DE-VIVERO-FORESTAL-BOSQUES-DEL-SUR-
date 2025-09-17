<?php
class TratamientoRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listByLoteFase(int $lote_fase_id): array {
        $stmt = $this->pdo->prepare("SELECT t.*, tt.nombre AS tipo_tratamiento
            FROM tratamientos t JOIN tipos_tratamiento tt ON tt.id = t.tipo_tratamiento_id
            WHERE t.lote_fase_id = ? ORDER BY t.fecha DESC, t.id DESC");
        $stmt->execute([$lote_fase_id]);
        return $stmt->fetchAll();
    }

    public function insert(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO tratamientos (lote_fase_id, tipo_tratamiento_id, usuario_id, fecha, producto, dosis, motivo, observaciones) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_fase_id'], $data['tipo_tratamiento_id'], $data['usuario_id'], $data['fecha'],
            $data['producto'] ?? null, $data['dosis'] ?? null, $data['motivo'] ?? null, $data['observaciones'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
