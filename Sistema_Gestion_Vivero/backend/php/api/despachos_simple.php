<?php
// API simple para despachos
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
        // Devolver despachos de ejemplo
        $despachos = [
            [
                'id' => 1,
                'codigo' => 'DESP-001',
                'cliente' => 'Municipalidad Central',
                'fecha' => '2024-03-01',
                'estado' => 'Pendiente',
                'total_plantas' => 25
            ],
            [
                'id' => 2,
                'codigo' => 'DESP-002',
                'cliente' => 'Parque Nacional',
                'fecha' => '2024-03-05',
                'estado' => 'Completado',
                'total_plantas' => 50
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $despachos]);
        
    } else {
        throw new Exception('Método no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
