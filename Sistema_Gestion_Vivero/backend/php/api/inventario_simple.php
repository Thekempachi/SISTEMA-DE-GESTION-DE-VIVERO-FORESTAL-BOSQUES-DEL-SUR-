<?php
// API simple para inventario
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
        // Devolver inventario de ejemplo
        $inventario = [
            [
                'id' => 1,
                'planta_id' => 1,
                'codigo_qr' => 'PLT-001',
                'especie' => 'Pino',
                'clasificacion_calidad_id' => 1,
                'tamano_id' => 2,
                'ubicacion' => 'Invernadero A'
            ],
            [
                'id' => 2,
                'planta_id' => 2,
                'codigo_qr' => 'PLT-002',
                'especie' => 'Rosa',
                'clasificacion_calidad_id' => 2,
                'tamano_id' => 1,
                'ubicacion' => 'Invernadero B'
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $inventario]);
        
    } else {
        throw new Exception('Método no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
