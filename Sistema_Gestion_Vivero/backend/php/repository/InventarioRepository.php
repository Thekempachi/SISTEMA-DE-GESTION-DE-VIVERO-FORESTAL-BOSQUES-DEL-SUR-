<?php
class InventarioRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function list(): array {
        $stmt = $this->pdo->query("SELECT ip.*, p.codigo_qr, es.nombre_comun AS especie
            FROM inventario_plantas ip
            JOIN plantas p ON p.id = ip.planta_id
            JOIN lotes_produccion lp ON lp.id = p.lote_produccion_id
            JOIN especies es ON es.id = lp.especie_id
            ORDER BY ip.fecha_ultima_actualizacion DESC");
        return $stmt->fetchAll();
    }

    public function upsert(int $planta_id, ?int $clasificacion_calidad_id, ?int $tamano_id): void {
        $stmt = $this->pdo->prepare("SELECT id FROM inventario_plantas WHERE planta_id=?");
        $stmt->execute([$planta_id]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt = $this->pdo->prepare("UPDATE inventario_plantas SET clasificacion_calidad_id=?, tamano_id=? WHERE planta_id=?");
            $stmt->execute([$clasificacion_calidad_id, $tamano_id, $planta_id]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO inventario_plantas (planta_id, clasificacion_calidad_id, tamano_id) VALUES (?,?,?)");
            $stmt->execute([$planta_id, $clasificacion_calidad_id, $tamano_id]);
        }
    }
}
