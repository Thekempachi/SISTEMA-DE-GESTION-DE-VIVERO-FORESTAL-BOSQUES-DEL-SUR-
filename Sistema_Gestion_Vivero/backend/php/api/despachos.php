<?php
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT od.*, d.nombre AS destino
            FROM ordenes_despacho od JOIN destinos d ON d.id = od.destino_id ORDER BY od.id DESC");
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $st = $pdo->prepare("SELECT l.*, p.codigo_qr, es.nombre_comun AS especie, es.id AS especie_id
                FROM ordenes_despacho_lineas l
                JOIN plantas p ON p.id = l.planta_id
                JOIN lotes_produccion lp ON lp.id = p.lote_produccion_id
                JOIN especies es ON es.id = lp.especie_id
                WHERE l.orden_despacho_id = ?");
            $st->execute([$o['id']]);
            $o['lineas'] = $st->fetchAll();
        }
        send_json(['ok'=>true,'data'=>$orders]);
    } elseif ($method === 'POST') {
        $action = $_GET['action'] ?? 'create_order';
        $data = json_input();
        if ($action === 'create_order') {
            require_fields($data, ['destino_id','fecha','responsable_despacho_id']);
            $numero = $data['numero'] ?? ('OD-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6)));
            $stmt = $pdo->prepare("INSERT INTO ordenes_despacho (numero, destino_id, fecha, responsable_despacho_id, personal_transporte, notas) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$numero, $data['destino_id'], $data['fecha'], $data['responsable_despacho_id'], $data['personal_transporte'] ?? null, $data['notas'] ?? null]);
            send_json(['ok'=>true,'orden_despacho_id'=>$pdo->lastInsertId(),'numero'=>$numero]);
        } elseif ($action === 'add_line') {
            require_fields($data, ['orden_despacho_id','planta_id','cantidad','estado_al_despacho_id']);
            $stmt = $pdo->prepare("INSERT INTO ordenes_despacho_lineas (orden_despacho_id, planta_id, cantidad, estado_al_despacho_id, observaciones) VALUES (?,?,?,?,?)");
            $stmt->execute([$data['orden_despacho_id'], $data['planta_id'], $data['cantidad'], $data['estado_al_despacho_id'], $data['observaciones'] ?? null]);
            send_json(['ok'=>true,'linea_id'=>$pdo->lastInsertId()]);
        } else {
            send_json(['error'=>'Acción no válida'],400);
        }
    } else {
        send_json(['error'=>'Método no permitido'],405);
    }
} catch (Throwable $e) {
    send_json(['error'=>$e->getMessage()],500);
}
?>

