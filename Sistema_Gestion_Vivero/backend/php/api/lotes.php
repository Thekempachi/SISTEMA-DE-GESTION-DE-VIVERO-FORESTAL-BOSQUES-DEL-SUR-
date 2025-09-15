<?php
require_once __DIR__ . '/../conection.php';

function gen_codigo_lote(PDO $pdo): string {
    $base = 'LP-'.date('Ymd').'-';
    do {
        $code = $base . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $stmt = $pdo->prepare("SELECT id FROM lotes_produccion WHERE codigo = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';
        if ($action === 'list') {
            $sql = "SELECT lp.*, e.nombre_comun AS especie, e.nombre_cientifico,
                        ls.proveedor_id, ps.nombre AS proveedor, ls.procedencia, ls.certificado
                    FROM lotes_produccion lp
                    JOIN especies e ON e.id = lp.especie_id
                    JOIN lotes_semillas ls ON ls.id = lp.lote_semillas_id
                    JOIN proveedores_semillas ps ON ps.id = ls.proveedor_id
                    ORDER BY lp.id DESC";
            $rows = $pdo->query($sql)->fetchAll();
            send_json(['ok'=>true,'data'=>$rows]);
        } elseif ($action === 'detail') {
            $id = $_GET['id'] ?? null;
            if (!$id) send_json(['error' => 'Falta id'], 400);
            $stmt = $pdo->prepare("SELECT lp.*, e.nombre_comun AS especie, e.nombre_cientifico,
                        ls.proveedor_id, ps.nombre AS proveedor, ls.procedencia, ls.certificado
                    FROM lotes_produccion lp
                    JOIN especies e ON e.id = lp.especie_id
                    JOIN lotes_semillas ls ON ls.id = lp.lote_semillas_id
                    JOIN proveedores_semillas ps ON ps.id = ls.proveedor_id
                    WHERE lp.id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) send_json(['error'=>'No encontrado'],404);
            // current active fase if any
            $fase = $pdo->prepare("SELECT lf.*, fp.nombre AS fase_nombre, ef.nombre AS estado
                FROM lote_fase lf
                JOIN fases_produccion fp ON fp.id = lf.fase_id
                JOIN estado_fase ef ON ef.id = lf.estado_id
                WHERE lf.lote_produccion_id=? AND lf.en_progreso=1");
            $fase->execute([$id]);
            $row['fase_actual'] = $fase->fetch();
            send_json(['ok'=>true,'data'=>$row]);
        } else {
            send_json(['error' => 'Acción no válida'], 400);
        }
    } elseif ($method === 'POST') {
        $data = json_input();
        $action = $_GET['action'] ?? 'create_all';
        if ($action === 'create_all') {
            // Create provider (optional), seed lot, and production lot in one transaction
            $pdo->beginTransaction();
            try {
                $proveedor_id = $data['proveedor_id'] ?? null;
                if (!$proveedor_id) {
                    require_fields($data['proveedor'] ?? [], ['nombre']);
                    $p = $data['proveedor'];
                    $stmt = $pdo->prepare("INSERT INTO proveedores_semillas (nombre, contacto, telefono, email, direccion, identificacion_fiscal, certificado) VALUES (?,?,?,?,?,?,?)");
                    $stmt->execute([
                        $p['nombre'], $p['contacto'] ?? null, $p['telefono'] ?? null, $p['email'] ?? null,
                        $p['direccion'] ?? null, $p['identificacion_fiscal'] ?? null, $p['certificado'] ?? null
                    ]);
                    $proveedor_id = (int)$pdo->lastInsertId();
                }

                require_fields($data['lote_semillas'] ?? [], []);
                $ls = $data['lote_semillas'] ?? [];
                $stmt = $pdo->prepare("INSERT INTO lotes_semillas (proveedor_id, procedencia, certificado, tasa_germinacion, observaciones) VALUES (?,?,?,?,?)");
                $stmt->execute([$proveedor_id, $ls['procedencia'] ?? null, $ls['certificado'] ?? null, $ls['tasa_germinacion'] ?? null, $ls['observaciones'] ?? null]);
                $lote_semillas_id = (int)$pdo->lastInsertId();

                require_fields($data['lote_produccion'] ?? [], ['especie_id','fecha_siembra','cantidad_semillas_usadas']);
                $lp = $data['lote_produccion'];
                $codigo = $lp['codigo'] ?? gen_codigo_lote($pdo);
                $stmt = $pdo->prepare("INSERT INTO lotes_produccion (codigo, especie_id, lote_semillas_id, fecha_siembra, cantidad_semillas_usadas, notas) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$codigo, $lp['especie_id'], $lote_semillas_id, $lp['fecha_siembra'], $lp['cantidad_semillas_usadas'], $lp['notas'] ?? null]);
                $lote_produccion_id = (int)$pdo->lastInsertId();

                $pdo->commit();
                send_json(['ok'=>true,'lote_produccion_id'=>$lote_produccion_id,'codigo'=>$codigo]);
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
        } else {
            send_json(['error'=>'Acción no válida'],400);
        }
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()], 500);
}
?>
