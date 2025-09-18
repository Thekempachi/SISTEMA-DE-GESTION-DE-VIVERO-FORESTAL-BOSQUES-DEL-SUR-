<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../repository/PlantaRepository.php';
require_once __DIR__ . '/../service/PlantaService.php';

class PlantasController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new PlantaService(new PlantaRepository($pdo));
            $method = http_method();

            if ($method === 'GET') {
                if (isset($_GET['id'])) {
                    $id = (int)$_GET['id'];
                    $row = $service->getById($id);
                    if (!$row) send_json(['error'=>'No encontrado'],404);
                    send_json(['ok'=>true,'data'=>$row]);
                }
                $loteId = isset($_GET['lote_produccion_id']) ? (int)$_GET['lote_produccion_id'] : null;
                $rows = $service->list($loteId);
                send_json(['ok'=>true,'data'=>$rows]);
            }

            if ($method === 'POST') {
                require_role([1,2]);
                $data = json_input();
                require_fields($data, ['lote_produccion_id','estado_salud_id']);
                $res = $service->create($data);
                send_json(['ok'=>true,'id'=>$res['id'],'codigo_qr'=>$res['codigo_qr']]);
            }

            if ($method === 'PUT') {
                require_role([1,2]);
                $id = $_GET['id'] ?? null; if (!$id) send_json(['error'=>'Falta id'],400);
                $data = json_input();
                $service->update((int)$id, $data);
                send_json(['ok'=>true]);
            }

            send_json(['error'=>'Método no permitido'],405);
        } catch (Throwable $e) {
            send_json(['error'=>$e->getMessage()],500);
        }
    }
}
