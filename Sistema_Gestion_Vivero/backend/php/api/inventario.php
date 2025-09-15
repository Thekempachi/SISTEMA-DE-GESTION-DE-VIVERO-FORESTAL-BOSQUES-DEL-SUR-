<?php
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT ip.*, p.codigo_qr, es.nombre_comun AS especie
            FROM inventario_plantas ip
            JOIN plantas p ON p.id = ip.planta_id
            JOIN lotes_produccion lp ON lp.id = p.lote_produccion_id
            JOIN especies es ON es.id = lp.especie_id
            ORDER BY ip.fecha_ultima_actualizacion DESC");
        send_json(['ok'=>true,'data'=>$stmt->fetchAll()]);
    } elseif ($method === 'PUT' || $method === 'POST') {
        $data = json_input();
        require_fields($data, ['planta_id']);
        // upsert by planta_id
        $stmt = $pdo->prepare("SELECT id FROM inventario_plantas WHERE planta_id=?");
        $stmt->execute([$data['planta_id']]);
        $row = $stmt->fetch();
        if ($row) {
            $stmt = $pdo->prepare("UPDATE inventario_plantas SET clasificacion_calidad_id=?, tamano_id=? WHERE planta_id=?");
            $stmt->execute([$data['clasificacion_calidad_id'] ?? null, $data['tamano_id'] ?? null, $data['planta_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO inventario_plantas (planta_id, clasificacion_calidad_id, tamano_id) VALUES (?,?,?)");
            $stmt->execute([$data['planta_id'], $data['clasificacion_calidad_id'] ?? null, $data['tamano_id'] ?? null]);
        }
        send_json(['ok'=>true]);
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()],500);
}
?>

