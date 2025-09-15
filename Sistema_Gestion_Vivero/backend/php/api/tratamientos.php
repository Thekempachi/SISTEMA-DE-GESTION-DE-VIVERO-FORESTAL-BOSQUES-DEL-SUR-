<?php
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        $lote_fase_id = $_GET['lote_fase_id'] ?? null;
        if (!$lote_fase_id) send_json(['error'=>'Falta lote_fase_id'], 400);
        $stmt = $pdo->prepare("SELECT t.*, tt.nombre AS tipo_tratamiento
            FROM tratamientos t JOIN tipos_tratamiento tt ON tt.id = t.tipo_tratamiento_id
            WHERE t.lote_fase_id = ? ORDER BY t.fecha DESC, t.id DESC");
        $stmt->execute([$lote_fase_id]);
        send_json(['ok'=>true,'data'=>$stmt->fetchAll()]);
    } elseif ($method === 'POST') {
        $data = json_input();
        require_fields($data, ['lote_fase_id','tipo_tratamiento_id','usuario_id','fecha']);
        $stmt = $pdo->prepare("INSERT INTO tratamientos (lote_fase_id, tipo_tratamiento_id, usuario_id, fecha, producto, dosis, motivo, observaciones) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['lote_fase_id'], $data['tipo_tratamiento_id'], $data['usuario_id'], $data['fecha'],
            $data['producto'] ?? null, $data['dosis'] ?? null, $data['motivo'] ?? null, $data['observaciones'] ?? null
        ]);
        send_json(['ok'=>true,'id'=>$pdo->lastInsertId()]);
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()],500);
}
?>

