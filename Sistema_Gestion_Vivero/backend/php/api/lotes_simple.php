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
                'cantidad_semillas_usadas' => 100,
                'proveedor' => 'Semillas del Norte',
                'notas' => 'Lote de prueba'
            ],
            [
                'id' => 2,
                'codigo' => 'LOT-002',
                'especie_id' => 2,
                'especie' => 'Rosa',
                'fecha_siembra' => '2024-02-01',
                'cantidad_semillas_usadas' => 50,
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
        
        $newId = rand(3, 100);
        $newCodigo = 'LOT-' . str_pad(rand(3, 999), 3, '0', STR_PAD_LEFT);
        
        // Especies disponibles
        $especies = [
            1 => 'Pino',
            2 => 'Rosa', 
            3 => 'Eucalipto'
        ];
        
        $especieId = $data['lote_produccion']['especie_id'] ?? 1;
        $especieNombre = $especies[$especieId] ?? 'Especie Desconocida';
        
        echo json_encode([
            'ok' => true, 
            'id' => $newId,
            'codigo' => $newCodigo,
            'message' => 'Lote creado correctamente',
            'data' => [
                'id' => $newId,
                'codigo' => $newCodigo,
                'especie_id' => $especieId,
                'especie' => $especieNombre,
                'fecha_siembra' => $data['lote_produccion']['fecha_siembra'] ?? date('Y-m-d'),
                'cantidad_semillas_usadas' => $data['lote_produccion']['cantidad_semillas_usadas'] ?? 0,
                'proveedor' => $data['proveedor']['nombre'] ?? 'Proveedor',
                'notas' => $data['lote_produccion']['notas'] ?? ''
            ]
        ]);
        
    } else {
        throw new Exception('Método no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
