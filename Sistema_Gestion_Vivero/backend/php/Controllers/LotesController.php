<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../Domain/Lotes/LoteRepository.php';
require_once __DIR__ . '/../Domain/Lotes/LoteService.php';

class LotesController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new LoteService(new LoteRepository($pdo), $pdo);
            $method = http_method();

            if ($method === 'GET') {
                $action = $_GET['action'] ?? 'list';
                if ($action === 'list') {
                    $rows = $service->list();
                    send_json(['ok'=>true,'data'=>$rows]);
                } elseif ($action === 'detail') {
                    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                    if (!$id) send_json(['error' => 'Falta id'], 400);
                    $row = $service->detail($id);
                    if (!$row) send_json(['error' => 'No encontrado'], 404);
                    send_json(['ok'=>true,'data'=>$row]);
                } else {
                    send_json(['error'=>'Acción no válida'], 400);
                }
            }

            if ($method === 'POST') {
                require_role([1,2]);
                $action = $_GET['action'] ?? 'create_all';
                if ($action === 'create_all') {
                    $data = json_input();
                    // Validaciones mínimas se realizan en el servicio
                    $res = $service->createAll($data);
                    send_json(['ok'=>true] + $res);
                }
                send_json(['error'=>'Acción no válida'],400);
            }

            send_json(['error'=>'Método no permitido'],405);
        } catch (Throwable $e) {
            send_json(['error'=>$e->getMessage()], 500);
        }
    }
}
