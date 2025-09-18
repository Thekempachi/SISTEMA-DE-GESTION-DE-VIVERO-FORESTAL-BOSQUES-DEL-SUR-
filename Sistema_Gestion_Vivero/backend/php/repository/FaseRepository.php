<?php
class FaseRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function listByLote(int $lote_id): array {
        $stmt = $this->pdo->prepare("SELECT lf.*, fp.nombre AS fase_nombre, ef.nombre AS estado, u.sector, u.fila, u.posicion
                FROM lote_fase lf
                JOIN fases_produccion fp ON fp.id = lf.fase_id
                JOIN estado_fase ef ON ef.id = lf.estado_id
                LEFT JOIN ubicaciones u ON u.id = lf.ubicacion_id
                WHERE lf.lote_produccion_id = ?
                ORDER BY lf.id DESC");
        $stmt->execute([$lote_id]);
        return $stmt->fetchAll();
    }

    public function start(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO lote_fase (lote_produccion_id, fase_id, estado_id, ubicacion_id, fecha_inicio, stock_inicial, stock_disponible, responsable_id, en_progreso, notas)
                VALUES (?,?,?,?,NOW(),?,?,?,1,?)");
        $stmt->execute([
            $data['lote_produccion_id'],
            $data['fase_id'],
            2,
            $data['ubicacion_id'] ?? null,
            $data['stock_inicial'] ?? 0,
            $data['stock_inicial'] ?? 0,
            $data['responsable_id'],
            $data['notas'] ?? null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function close(array $data): int {
        // Close current fase
        $stmt = $this->pdo->prepare("UPDATE lote_fase SET estado_id=3, fecha_fin=NOW(), en_progreso=NULL, stock_disponible = stock_disponible - ?, updated_at=NOW() WHERE id=?");
        $stmt->execute([(int)$data['plantas_avanzan'], (int)$data['lote_fase_id']]);

        // Insert resultados de fase
        $stmt = $this->pdo->prepare("INSERT INTO resultados_fase (lote_fase_id, fecha, plantas_avanzan, plantas_perdidas, plantas_descartadas, observaciones, responsable_id)
                    VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_fase_id'],
            date('Y-m-d'),
            (int)$data['plantas_avanzan'],
            (int)($data['plantas_perdidas'] ?? 0),
            (int)($data['plantas_descartadas'] ?? 0),
            $data['observaciones'] ?? null,
            $data['responsable_id'],
        ]);
        $res_id = (int)$this->pdo->lastInsertId();

        // Optional details arrays
        if (!empty($data['perdidas_detalles']) && is_array($data['perdidas_detalles'])) {
            $ins = $this->pdo->prepare("INSERT INTO detalles_perdidas (resultados_fase_id, causa_id, cantidad, observaciones) VALUES (?,?,?,?)");
            foreach ($data['perdidas_detalles'] as $d) {
                if (!isset($d['causa_id'],$d['cantidad'])) continue;
                $ins->execute([$res_id, $d['causa_id'], $d['cantidad'], $d['observaciones'] ?? null]);
            }
        }
        if (!empty($data['descartes_detalles']) && is_array($data['descartes_detalles'])) {
            $ins = $this->pdo->prepare("INSERT INTO detalles_descartes (resultados_fase_id, motivo_id, cantidad, observaciones) VALUES (?,?,?,?)");
            foreach ($data['descartes_detalles'] as $d) {
                if (!isset($d['motivo_id'],$d['cantidad'])) continue;
                $ins->execute([$res_id, $d['motivo_id'], $d['cantidad'], $d['observaciones'] ?? null]);
            }
        }
        return $res_id;
    }

    public function dailyReport(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO reportes_diarios (lote_fase_id, fecha, avance_dia, perdidas_dia, descartes_dia, resumen_tratamientos, resumen_condiciones, observaciones, responsable_id)
                VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_fase_id'],
            $data['fecha'] ?? date('Y-m-d'),
            (int)($data['avance_dia'] ?? 0),
            (int)($data['perdidas_dia'] ?? 0),
            (int)($data['descartes_dia'] ?? 0),
            $data['resumen_tratamientos'] ?? null,
            $data['resumen_condiciones'] ?? null,
            $data['observaciones'] ?? null,
            $data['responsable_id'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
