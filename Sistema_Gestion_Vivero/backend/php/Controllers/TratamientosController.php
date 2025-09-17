<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../Domain/Tratamientos/TratamientoRepository.php';
require_once __DIR__ . '/../Domain/Tratamientos/TratamientoService.php';

class TratamientosController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new TratamientoService(new TratamientoRepository($pdo));
            $method = http_method();

            if ($method === 'GET') {
                $lote_fase_id = isset($_GET['lote_fase_id']) ? (int)$_GET['lote_fase_id'] : 0;
                if (!$lote_fase_id) send_json(['error'=>'Falta lote_fase_id'], 400);
                $rows = $service->listByLoteFase($lote_fase_id);
                send_json(['ok'=>true,'data'=>$rows]);
            }

            if ($method === 'POST') {
                require_role([1,2]);
                $data = json_input();
                $id = $service->create($data);
                send_json(['ok'=>true,'id'=>$id]);
            }

            send_json(['error'=>'Método no permitido'],405);
        } catch (Throwable $e) {
            send_json(['error'=>$e->getMessage()],500);
        }
    }
}
