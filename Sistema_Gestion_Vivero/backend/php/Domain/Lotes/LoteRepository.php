<?php
class LoteRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function list(): array {
        $sql = "SELECT lp.*, e.nombre_comun AS especie, e.nombre_cientifico,
                        ls.proveedor_id, ps.nombre AS proveedor, ls.procedencia, ls.certificado
                    FROM lotes_produccion lp
                    JOIN especies e ON e.id = lp.especie_id
                    JOIN lotes_semillas ls ON ls.id = lp.lote_semillas_id
                    JOIN proveedores_semillas ps ON ps.id = ls.proveedor_id
                    ORDER BY lp.id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function detail(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT lp.*, e.nombre_comun AS especie, e.nombre_cientifico,
                        ls.proveedor_id, ps.nombre AS proveedor, ls.procedencia, ls.certificado
                    FROM lotes_produccion lp
                    JOIN especies e ON e.id = lp.especie_id
                    JOIN lotes_semillas ls ON ls.id = lp.lote_semillas_id
                    JOIN proveedores_semillas ps ON ps.id = ls.proveedor_id
                    WHERE lp.id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        // fase actual si existe
        $fase = $this->pdo->prepare("SELECT lf.*, fp.nombre AS fase_nombre, ef.nombre AS estado
                FROM lote_fase lf
                JOIN fases_produccion fp ON fp.id = lf.fase_id
                JOIN estado_fase ef ON ef.id = lf.estado_id
                WHERE lf.lote_produccion_id=? AND lf.en_progreso=1");
        $fase->execute([$id]);
        $row['fase_actual'] = $fase->fetch();
        return $row;
    }

    public function isCodigoUsed(string $codigo): bool {
        $stmt = $this->pdo->prepare("SELECT id FROM lotes_produccion WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return (bool)$stmt->fetch();
    }

    public function insertProveedor(array $p): int {
        $stmt = $this->pdo->prepare("INSERT INTO proveedores_semillas (nombre, contacto, telefono, email, direccion, identificacion_fiscal, certificado) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $p['nombre'], $p['contacto'] ?? null, $p['telefono'] ?? null, $p['email'] ?? null,
            $p['direccion'] ?? null, $p['identificacion_fiscal'] ?? null, $p['certificado'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function insertLoteSemillas(int $proveedor_id, array $ls): int {
        $stmt = $this->pdo->prepare("INSERT INTO lotes_semillas (proveedor_id, procedencia, certificado, tasa_germinacion, observaciones) VALUES (?,?,?,?,?)");
        $stmt->execute([$proveedor_id, $ls['procedencia'] ?? null, $ls['certificado'] ?? null, $ls['tasa_germinacion'] ?? null, $ls['observaciones'] ?? null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function insertLoteProduccion(array $lp, int $lote_semillas_id, string $codigo): int {
        $stmt = $this->pdo->prepare("INSERT INTO lotes_produccion (codigo, especie_id, lote_semillas_id, fecha_siembra, cantidad_semillas_usadas, notas) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$codigo, $lp['especie_id'], $lote_semillas_id, $lp['fecha_siembra'], $lp['cantidad_semillas_usadas'], $lp['notas'] ?? null]);
        return (int)$this->pdo->lastInsertId();
    }
}
