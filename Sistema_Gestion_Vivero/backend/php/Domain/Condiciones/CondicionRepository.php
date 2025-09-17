<?php
class CondicionRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listByLoteFase(int $lote_fase_id): array {
        $stmt = $this->pdo->prepare("SELECT c.*, u.sector, u.fila, u.posicion
            FROM condiciones_ambientales c LEFT JOIN ubicaciones u ON u.id = c.ubicacion_id
            WHERE c.lote_fase_id = ? ORDER BY c.fecha DESC, c.id DESC");
        $stmt->execute([$lote_fase_id]);
        return $stmt->fetchAll();
    }

    public function insert(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO condiciones_ambientales (lote_fase_id, ubicacion_id, fecha, temperatura, humedad, precipitaciones, fuente, observaciones) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_fase_id'], $data['ubicacion_id'] ?? null, $data['fecha'],
            $data['temperatura'] ?? null, $data['humedad'] ?? null, $data['precipitaciones'] ?? null,
            $data['fuente'] ?? 'manual', $data['observaciones'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
