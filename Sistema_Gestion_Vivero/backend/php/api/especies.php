<?php
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    $method = http_method();
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT e.*, te.nombre AS tipo_especie FROM especies e JOIN tipo_especie te ON te.id = e.tipo_especie_id WHERE e.id = ?");
            $stmt->execute([$_GET['id']]);
            $row = $stmt->fetch();
            if (!$row) send_json(['error' => 'No encontrado'], 404);
            send_json(['ok' => true, 'data' => $row]);
        } else {
            $q = trim($_GET['q'] ?? '');
            if ($q !== '') {
                $stmt = $pdo->prepare("SELECT e.*, te.nombre AS tipo_especie FROM especies e JOIN tipo_especie te ON te.id = e.tipo_especie_id WHERE e.nombre_comun LIKE ? OR e.nombre_cientifico LIKE ? ORDER BY e.id DESC");
                $stmt->execute(['%'.$q.'%', '%'.$q.'%']);
            } else {
                $stmt = $pdo->query("SELECT e.*, te.nombre AS tipo_especie FROM especies e JOIN tipo_especie te ON te.id = e.tipo_especie_id ORDER BY e.id DESC");
            }
            send_json(['ok' => true, 'data' => $stmt->fetchAll()]);
        }
    } elseif ($method === 'POST') {
        $data = json_input();
        require_fields($data, ['nombre_cientifico','nombre_comun','tipo_especie_id']);
        $stmt = $pdo->prepare("INSERT INTO especies (nombre_cientifico, nombre_comun, tipo_especie_id, categoria, notas, descripcion) VALUES (?,?,?,?,?,?)");
        $stmt->execute([
            $data['nombre_cientifico'],
            $data['nombre_comun'],
            $data['tipo_especie_id'],
            $data['categoria'] ?? null,
            $data['notas'] ?? null,
            $data['descripcion'] ?? null,
        ]);
        send_json(['ok' => true, 'id' => $pdo->lastInsertId()]);
    } elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;
        if (!$id) send_json(['error' => 'Falta id'], 400);
        $data = json_input();
        $stmt = $pdo->prepare("UPDATE especies SET nombre_cientifico=?, nombre_comun=?, tipo_especie_id=?, categoria=?, notas=?, descripcion=? WHERE id=?");
        $stmt->execute([
            $data['nombre_cientifico'] ?? null,
            $data['nombre_comun'] ?? null,
            $data['tipo_especie_id'] ?? null,
            $data['categoria'] ?? null,
            $data['notas'] ?? null,
            $data['descripcion'] ?? null,
            $id
        ]);
        send_json(['ok' => true]);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) send_json(['error' => 'Falta id'], 400);
        $stmt = $pdo->prepare("DELETE FROM especies WHERE id=?");
        $stmt->execute([$id]);
        send_json(['ok' => true]);
    } else {
        send_json(['error' => 'Método no permitido'], 405);
    }
} catch (Throwable $e) {
    send_json(['error' => $e->getMessage()], 500);
}
?>
