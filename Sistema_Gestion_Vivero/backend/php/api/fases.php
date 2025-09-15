<?php
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'by_lote';
        if ($action === 'by_lote') {
            $lote_id = $_GET['lote_id'] ?? null;
            if (!$lote_id) send_json(['error'=>'Falta lote_id'], 400);
            $stmt = $pdo->prepare("SELECT lf.*, fp.nombre AS fase_nombre, ef.nombre AS estado, u.sector, u.fila, u.posicion
                FROM lote_fase lf
                JOIN fases_produccion fp ON fp.id = lf.fase_id
                JOIN estado_fase ef ON ef.id = lf.estado_id
                LEFT JOIN ubicaciones u ON u.id = lf.ubicacion_id
                WHERE lf.lote_produccion_id = ?
                ORDER BY lf.id DESC");
            $stmt->execute([$lote_id]);
            send_json(['ok'=>true,'data'=>$stmt->fetchAll()]);
        } else {
            send_json(['error'=>'Acción no válida'], 400);
        }
    } elseif ($method === 'POST') {
        $action = $_GET['action'] ?? '';
        $data = json_input();
        if ($action === 'start') {
            require_fields($data, ['lote_produccion_id','fase_id','responsable_id']);
            // default estado: En progreso (id 2)
            $stmt = $pdo->prepare("INSERT INTO lote_fase (lote_produccion_id, fase_id, estado_id, ubicacion_id, fecha_inicio, stock_inicial, stock_disponible, responsable_id, en_progreso, notas)
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
            send_json(['ok'=>true,'lote_fase_id'=>$pdo->lastInsertId()]);
        } elseif ($action === 'close') {
            require_fields($data, ['lote_fase_id','responsable_id','plantas_avanzan']);
            $pdo->beginTransaction();
            try {
                // Close current fase
                $stmt = $pdo->prepare("UPDATE lote_fase SET estado_id=3, fecha_fin=NOW(), en_progreso=NULL, stock_disponible = stock_disponible - ?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$data['plantas_avanzan'], $data['lote_fase_id']]);

                // Insert resultados
                // Insert resultados de fase
                $stmt = $pdo->prepare("INSERT INTO resultados_fase (lote_fase_id, fecha, plantas_avanzan, plantas_perdidas, plantas_descartadas, observaciones, responsable_id)
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
                // Optional details arrays
                $res_id = (int)$pdo->lastInsertId();
                if (!empty($data['perdidas_detalles']) && is_array($data['perdidas_detalles'])) {
                    $ins = $pdo->prepare("INSERT INTO detalles_perdidas (resultados_fase_id, causa_id, cantidad, observaciones) VALUES (?,?,?,?)");
                    foreach ($data['perdidas_detalles'] as $d) {
                        if (!isset($d['causa_id'],$d['cantidad'])) continue;
                        $ins->execute([$res_id, $d['causa_id'], $d['cantidad'], $d['observaciones'] ?? null]);
                    }
                }
                if (!empty($data['descartes_detalles']) && is_array($data['descartes_detalles'])) {
                    $ins = $pdo->prepare("INSERT INTO detalles_descartes (resultados_fase_id, motivo_id, cantidad, observaciones) VALUES (?,?,?,?)");
                    foreach ($data['descartes_detalles'] as $d) {
                        if (!isset($d['motivo_id'],$d['cantidad'])) continue;
                        $ins->execute([$res_id, $d['motivo_id'], $d['cantidad'], $d['observaciones'] ?? null]);
                    }
                }
                $pdo->commit();
                send_json(['ok'=>true,'resultado_id'=>$res_id]);
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        } elseif ($action === 'daily_report') {
            require_fields($data, ['lote_fase_id','responsable_id']);
            $stmt = $pdo->prepare("INSERT INTO reportes_diarios (lote_fase_id, fecha, avance_dia, perdidas_dia, descartes_dia, resumen_tratamientos, resumen_condiciones, observaciones, responsable_id)
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
            send_json(['ok'=>true,'reporte_id'=>$pdo->lastInsertId()]);
        } else {
            send_json(['error'=>'Acción no válida'], 400);
        }
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()], 500);
}
?>
