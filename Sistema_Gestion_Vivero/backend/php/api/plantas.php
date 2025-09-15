<?php
require_once __DIR__ . '/../conection.php';

function gen_qr_code(PDO $pdo): string {
    do {
        $code = 'QR-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        $stmt = $pdo->prepare("SELECT id FROM plantas WHERE codigo_qr = ?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());
    return $code;
}

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT p.*, es.nombre_comun AS especie, u.sector, u.fila, u.posicion
                FROM plantas p
                JOIN lotes_produccion lp ON lp.id = p.lote_produccion_id
                JOIN especies es ON es.id = lp.especie_id
                LEFT JOIN ubicaciones u ON u.id = p.ubicacion_id
                WHERE p.id = ?");
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch();
            if (!$row) send_json(['error'=>'No encontrado'],404);
            send_json(['ok'=>true,'data'=>$row]);
        }
        $where = '';
        $params = [];
        if (!empty($_GET['lote_produccion_id'])) { $where = 'WHERE p.lote_produccion_id = ?'; $params[] = $_GET['lote_produccion_id']; }
        $stmt = $pdo->prepare("SELECT p.*, es.nombre_comun AS especie FROM plantas p JOIN lotes_produccion lp ON lp.id=p.lote_produccion_id JOIN especies es ON es.id=lp.especie_id $where ORDER BY p.id DESC");
        $stmt->execute($params);
        send_json(['ok'=>true,'data'=>$stmt->fetchAll()]);
    } elseif ($method === 'POST') {
        $data = json_input();
        require_fields($data, ['lote_produccion_id','estado_salud_id']);
        $codigo = $data['codigo_qr'] ?? gen_qr_code($pdo);
        $stmt = $pdo->prepare("INSERT INTO plantas (lote_produccion_id, codigo_qr, fecha_etiquetado, altura_actual_cm, estado_salud_id, ubicacion_id, vigencia_meses, observaciones) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_produccion_id'], $codigo, $data['fecha_etiquetado'] ?? date('Y-m-d'),
            $data['altura_actual_cm'] ?? null, $data['estado_salud_id'], $data['ubicacion_id'] ?? null,
            $data['vigencia_meses'] ?? 24, $data['observaciones'] ?? null
        ]);
        $id = (int)$pdo->lastInsertId();
        // Ensure an inventory record exists
        $pdo->prepare("INSERT INTO inventario_plantas (planta_id) VALUES (?)")->execute([$id]);
        send_json(['ok'=>true,'id'=>$id,'codigo_qr'=>$codigo]);
    } elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null; if (!$id) send_json(['error'=>'Falta id'],400);
        $data = json_input();
        $stmt = $pdo->prepare("UPDATE plantas SET altura_actual_cm=?, estado_salud_id=?, ubicacion_id=?, observaciones=? WHERE id=?");
        $stmt->execute([$data['altura_actual_cm'] ?? null, $data['estado_salud_id'] ?? null, $data['ubicacion_id'] ?? null, $data['observaciones'] ?? null, $id]);
        send_json(['ok'=>true]);
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()],500);
}
?>

