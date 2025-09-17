<?php
class PlantaRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function generateUniqueQr(): string {
        do {
            $code = 'QR-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
            $stmt = $this->pdo->prepare("SELECT id FROM plantas WHERE codigo_qr = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());
        return $code;
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT p.*, es.nombre_comun AS especie, u.sector, u.fila, u.posicion
            FROM plantas p
            JOIN lotes_produccion lp ON lp.id = p.lote_produccion_id
            JOIN especies es ON es.id = lp.especie_id
            LEFT JOIN ubicaciones u ON u.id = p.ubicacion_id
            WHERE p.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function list(?int $lote_produccion_id = null): array {
        $where = '';
        $params = [];
        if ($lote_produccion_id) { $where = 'WHERE p.lote_produccion_id = ?'; $params[] = $lote_produccion_id; }
        $stmt = $this->pdo->prepare("SELECT p.*, es.nombre_comun AS especie FROM plantas p JOIN lotes_produccion lp ON lp.id=p.lote_produccion_id JOIN especies es ON es.id=lp.especie_id $where ORDER BY p.id DESC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function create(array $data, ?string $codigo_qr = null): array {
        $codigo = $codigo_qr ?? $this->generateUniqueQr();
        $stmt = $this->pdo->prepare("INSERT INTO plantas (lote_produccion_id, codigo_qr, fecha_etiquetado, altura_actual_cm, estado_salud_id, ubicacion_id, vigencia_meses, observaciones) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_produccion_id'], $codigo, $data['fecha_etiquetado'] ?? date('Y-m-d'),
            $data['altura_actual_cm'] ?? null, $data['estado_salud_id'], $data['ubicacion_id'] ?? null,
            $data['vigencia_meses'] ?? 24, $data['observaciones'] ?? null
        ]);
        $id = (int)$this->pdo->lastInsertId();
        // Ensure an inventory record exists
        $this->pdo->prepare("INSERT INTO inventario_plantas (planta_id) VALUES (?)")->execute([$id]);
        return ['id' => $id, 'codigo_qr' => $codigo];
    }

    public function update(int $id, array $data): void {
        $stmt = $this->pdo->prepare("UPDATE plantas SET altura_actual_cm=?, estado_salud_id=?, ubicacion_id=?, observaciones=? WHERE id=?");
        $stmt->execute([$data['altura_actual_cm'] ?? null, $data['estado_salud_id'] ?? null, $data['ubicacion_id'] ?? null, $data['observaciones'] ?? null, $id]);
    }
}
