<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../Domain/Inventario/InventarioRepository.php';
require_once __DIR__ . '/../Domain/Inventario/InventarioService.php';

class InventarioController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new InventarioService(new InventarioRepository($pdo));
            $method = http_method();

            if ($method === 'GET') {
                $rows = $service->list();
                send_json(['ok'=>true,'data'=>$rows]);
            }

            if ($method === 'PUT' || $method === 'POST') {
                require_role([1,2]);
                $data = json_input();
                require_fields($data, ['planta_id']);
                $service->upsert($data);
                send_json(['ok'=>true]);
            }

            send_json(['error'=>'Método no permitido'],405);
        } catch (Throwable $e) {
            send_json(['error'=>$e->getMessage()],500);
        }
    }
}
