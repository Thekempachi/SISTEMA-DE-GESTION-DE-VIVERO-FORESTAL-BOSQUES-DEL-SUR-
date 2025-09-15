<?php
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        $lote_fase_id = $_GET['lote_fase_id'] ?? null;
        if (!$lote_fase_id) send_json(['error'=>'Falta lote_fase_id'], 400);
        $stmt = $pdo->prepare("SELECT c.*, u.sector, u.fila, u.posicion
            FROM condiciones_ambientales c LEFT JOIN ubicaciones u ON u.id = c.ubicacion_id
            WHERE c.lote_fase_id = ? ORDER BY c.fecha DESC, c.id DESC");
        $stmt->execute([$lote_fase_id]);
        send_json(['ok'=>true,'data'=>$stmt->fetchAll()]);
    } elseif ($method === 'POST') {
        $data = json_input();
        require_fields($data, ['lote_fase_id','fecha']);
        $stmt = $pdo->prepare("INSERT INTO condiciones_ambientales (lote_fase_id, ubicacion_id, fecha, temperatura, humedad, precipitaciones, fuente, observaciones) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_fase_id'], $data['ubicacion_id'] ?? null, $data['fecha'],
            $data['temperatura'] ?? null, $data['humedad'] ?? null, $data['precipitaciones'] ?? null,
            $data['fuente'] ?? 'manual', $data['observaciones'] ?? null
        ]);
        send_json(['ok'=>true,'id'=>$pdo->lastInsertId()]);
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()],500);
}
?>

