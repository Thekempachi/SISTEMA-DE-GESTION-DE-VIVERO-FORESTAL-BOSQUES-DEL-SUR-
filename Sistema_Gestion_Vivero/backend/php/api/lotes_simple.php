<?php
// API simple para lotes
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
        // Devolver lotes de ejemplo
        $lotes = [
            [
                'id' => 1,
                'codigo' => 'LOT-001',
                'especie_id' => 1,
                'especie' => 'Pino',
                'fecha_siembra' => '2024-01-15',
                'cantidad_semillas' => 100,
                'proveedor' => 'Semillas del Norte',
                'notas' => 'Lote de prueba'
            ],
            [
                'id' => 2,
                'codigo' => 'LOT-002',
                'especie_id' => 2,
                'especie' => 'Rosa',
                'fecha_siembra' => '2024-02-01',
                'cantidad_semillas' => 50,
                'proveedor' => 'Vivero Central',
                'notas' => 'Variedades mixtas'
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $lotes]);
        
    } elseif ($method === 'POST') {
        // Simular creación de lote
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            throw new Exception('Datos JSON inválidos');
        }
        
        echo json_encode([
            'ok' => true, 
            'id' => rand(3, 100),
            'codigo' => 'LOT-' . str_pad(rand(3, 999), 3, '0', STR_PAD_LEFT),
            'message' => 'Lote creado correctamente'
        ]);
        
    } else {
        throw new Exception('Método no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
