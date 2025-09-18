<?php
// API simple para fases
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    if ($method === 'GET' && $action === 'by_lote') {
        $lote_id = $_GET['lote_id'] ?? '';
        
        // Devolver fases de ejemplo para un lote
        $fases = [
            [
                'id' => 1,
                'lote_id' => $lote_id,
                'fase_nombre' => 'Germinación',
                'estado' => 'Completada',
                'fecha_inicio' => '2024-01-15',
                'fecha_fin' => '2024-02-01',
                'stock_inicial' => 100,
                'stock_disponible' => 85,
                'en_progreso' => 0
            ],
            [
                'id' => 2,
                'lote_id' => $lote_id,
                'fase_nombre' => 'Crecimiento',
                'estado' => 'En progreso',
                'fecha_inicio' => '2024-02-01',
                'fecha_fin' => null,
                'stock_inicial' => 85,
                'stock_disponible' => 80,
                'en_progreso' => 1
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $fases]);
        
    } else {
        throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
