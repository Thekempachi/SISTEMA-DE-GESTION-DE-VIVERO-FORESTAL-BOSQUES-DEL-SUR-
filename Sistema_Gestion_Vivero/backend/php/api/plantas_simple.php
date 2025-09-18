<?php
// API simple para plantas
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
    
    if ($method === 'GET') {
        // Devolver plantas de ejemplo
        $plantas = [
            [
                'id' => 1,
                'codigo' => 'PLT-001',
                'lote_id' => 1,
                'lote_codigo' => 'LOT-001',
                'especie' => 'Pino',
                'fase_actual' => 'Crecimiento',
                'ubicacion' => 'Invernadero A',
                'estado' => 'Saludable'
            ],
            [
                'id' => 2,
                'codigo' => 'PLT-002',
                'lote_id' => 1,
                'lote_codigo' => 'LOT-001',
                'especie' => 'Pino',
                'fase_actual' => 'Desarrollo',
                'ubicacion' => 'Invernadero A',
                'estado' => 'Saludable'
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $plantas]);
        
    } else {
        throw new Exception('Método no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
