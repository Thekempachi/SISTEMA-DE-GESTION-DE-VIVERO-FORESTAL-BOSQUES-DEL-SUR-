<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../repository/DespachoRepository.php';
require_once __DIR__ . '/../service/DespachoService.php';

class DespachosController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new DespachoService(new DespachoRepository($pdo));
            $method = http_method();

            if ($method === 'GET') {
                $rows = $service->list();
                send_json(['ok'=>true,'data'=>$rows]);
            }

            if ($method === 'POST') {
                require_role([1,2]);
                $action = $_GET['action'] ?? 'create_order';
                $data = json_input();
                if ($action === 'create_order') {
                    $res = $service->createOrder($data);
                    send_json(['ok'=>true] + $res);
                } elseif ($action === 'add_line') {
                    $id = $service->addLine($data);
                    send_json(['ok'=>true,'linea_id'=>$id]);
                }
                send_json(['error'=>'Acción no válida'],400);
            }

            send_json(['error'=>'Método no permitido'],405);
        } catch (Throwable $e) {
            send_json(['error'=>$e->getMessage()],500);
        }
    }
}
