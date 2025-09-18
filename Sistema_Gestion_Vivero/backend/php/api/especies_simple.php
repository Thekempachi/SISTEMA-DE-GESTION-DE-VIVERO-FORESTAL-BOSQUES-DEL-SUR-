<?php
// API simple para especies
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Devolver especies de ejemplo
        $especies = [
            [
                'id' => 1,
                'nombre_comun' => 'Pino',
                'nombre_cientifico' => 'Pinus sylvestris',
                'tipo_especie_id' => 1,
                'tipo_especie' => 'Árbol',
                'categoria' => 'Conífera',
                'descripcion' => 'Árbol de hoja perenne'
            ],
            [
                'id' => 2,
                'nombre_comun' => 'Rosa',
                'nombre_cientifico' => 'Rosa rubiginosa',
                'tipo_especie_id' => 2,
                'tipo_especie' => 'Arbusto',
                'categoria' => 'Ornamental',
                'descripcion' => 'Arbusto con flores aromáticas'
            ],
            [
                'id' => 3,
                'nombre_comun' => 'Eucalipto',
                'nombre_cientifico' => 'Eucalyptus globulus',
                'tipo_especie_id' => 1,
                'tipo_especie' => 'Árbol',
                'categoria' => 'Medicinal',
                'descripcion' => 'Árbol de crecimiento rápido'
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $especies]);
        
    } elseif ($method === 'POST') {
        // Simular creación de especie
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            throw new Exception('Datos JSON inválidos');
        }
        
        // Validar campos requeridos
        $required = ['nombre_comun', 'nombre_cientifico', 'tipo_especie_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Campo requerido: $field");
            }
        }
        
        $newId = rand(4, 100);
        
        // Determinar el tipo de especie
        $tipoNombre = 'Desconocido';
        switch($data['tipo_especie_id']) {
            case 1: $tipoNombre = 'Árbol'; break;
            case 2: $tipoNombre = 'Arbusto'; break;
            case 3: $tipoNombre = 'Hierba'; break;
        }
        
        echo json_encode([
            'ok' => true, 
            'id' => $newId,
            'message' => 'Especie creada correctamente',
            'data' => [
                'id' => $newId,
                'nombre_comun' => $data['nombre_comun'],
                'nombre_cientifico' => $data['nombre_cientifico'],
                'tipo_especie_id' => $data['tipo_especie_id'],
                'tipo_especie' => $tipoNombre,
                'categoria' => $data['categoria'] ?? '',
                'descripcion' => $data['descripcion'] ?? ''
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
