<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../repository/EspecieRepository.php';
require_once __DIR__ . '/../service/EspecieService.php';

class EspeciesController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new EspecieService(new EspecieRepository($pdo));
            $method = http_method();

            if ($method === 'GET') {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $row = $service->getById($id);
                    if (!$row) send_json(['error' => 'No encontrado'], 404);
                    send_json(['ok' => true, 'data' => $row]);
                }
                $q = trim($_GET['q'] ?? '');
                $rows = $service->list($q);
                send_json(['ok' => true, 'data' => $rows]);
            }

            if ($method === 'POST') {
                require_role([1,2]);
                $data = json_input();
                require_fields($data, ['nombre_cientifico','nombre_comun','tipo_especie_id']);
                $id = $service->create($data);
                send_json(['ok' => true, 'id' => $id]);
            }

            if ($method === 'PUT') {
                require_role([1,2]);
                $id = $_GET['id'] ?? null;
                if (!$id) send_json(['error' => 'Falta id'], 400);
                $data = json_input();
                $service->update((int)$id, $data);
                send_json(['ok' => true]);
            }

            if ($method === 'DELETE') {
                require_role(1);
                $id = $_GET['id'] ?? null;
                if (!$id) send_json(['error' => 'Falta id'], 400);
                $service->delete((int)$id);
                send_json(['ok' => true]);
            }

            send_json(['error' => 'Método no permitido'], 405);
        } catch (Throwable $e) {
            send_json(['error' => $e->getMessage()], 500);
        }
    }
}
