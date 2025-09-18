<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../repository/FaseRepository.php';
require_once __DIR__ . '/../service/FaseService.php';

class FasesController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new FaseService(new FaseRepository($pdo), $pdo);
            $method = http_method();

            if ($method === 'GET') {
                $action = $_GET['action'] ?? 'by_lote';
                if ($action === 'by_lote') {
                    $lote_id = isset($_GET['lote_id']) ? (int)$_GET['lote_id'] : 0;
                    if (!$lote_id) send_json(['error'=>'Falta lote_id'], 400);
                    $rows = $service->listByLote($lote_id);
                    send_json(['ok'=>true,'data'=>$rows]);
                }
                send_json(['error'=>'Acción no válida'], 400);
            }

            if ($method === 'POST') {
                require_role([1,2]);
                $action = $_GET['action'] ?? '';
                $data = json_input();
                if ($action === 'start') {
                    $id = $service->start($data);
                    send_json(['ok'=>true,'lote_fase_id'=>$id]);
                } elseif ($action === 'close') {
                    $res_id = $service->close($data);
                    send_json(['ok'=>true,'resultado_id'=>$res_id]);
                } elseif ($action === 'daily_report') {
                    $rep_id = $service->dailyReport($data);
                    send_json(['ok'=>true,'reporte_id'=>$rep_id]);
                }
                send_json(['error'=>'Acción no válida'], 400);
            }

            send_json(['error'=>'Método no permitido'],405);
        } catch (Throwable $e) {
            send_json(['error'=>$e->getMessage()], 500);
        }
    }
}
