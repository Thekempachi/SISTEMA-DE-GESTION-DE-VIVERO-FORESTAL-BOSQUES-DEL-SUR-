<?php
// API simple para catálogos básicos
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
        // Devolver catálogos básicos
        $catalogs = [
            'tipo_especie' => [
                ['id' => 1, 'nombre' => 'Árbol'],
                ['id' => 2, 'nombre' => 'Arbusto'],
                ['id' => 3, 'nombre' => 'Hierba']
            ],
            'tipos_especie' => [
                ['id' => 1, 'nombre' => 'Árbol'],
                ['id' => 2, 'nombre' => 'Arbusto'],
                ['id' => 3, 'nombre' => 'Hierba']
            ],
            'fases' => [
                ['id' => 1, 'nombre' => 'Germinación'],
                ['id' => 2, 'nombre' => 'Crecimiento'],
                ['id' => 3, 'nombre' => 'Desarrollo'],
                ['id' => 4, 'nombre' => 'Maduración']
            ],
            'fases_produccion' => [
                ['id' => 1, 'nombre' => 'Germinación'],
                ['id' => 2, 'nombre' => 'Crecimiento'],
                ['id' => 3, 'nombre' => 'Desarrollo'],
                ['id' => 4, 'nombre' => 'Maduración']
            ],
            'ubicaciones' => [
                ['id' => 1, 'nombre' => 'Invernadero A'],
                ['id' => 2, 'nombre' => 'Invernadero B'],
                ['id' => 3, 'nombre' => 'Área Externa']
            ],
            'tipos_tratamiento' => [
                ['id' => 1, 'nombre' => 'Riego'],
                ['id' => 2, 'nombre' => 'Fertilización'],
                ['id' => 3, 'nombre' => 'Poda']
            ],
            'clasificaciones_calidad' => [
                ['id' => 1, 'nombre' => 'Excelente'],
                ['id' => 2, 'nombre' => 'Buena'],
                ['id' => 3, 'nombre' => 'Regular']
            ],
            'tamanos_plantas' => [
                ['id' => 1, 'codigo' => 'S', 'nombre' => 'Pequeño'],
                ['id' => 2, 'codigo' => 'M', 'nombre' => 'Mediano'],
                ['id' => 3, 'codigo' => 'L', 'nombre' => 'Grande']
            ],
            'estado_salud' => [
                ['id' => 1, 'nombre' => 'Saludable'],
                ['id' => 2, 'nombre' => 'Enfermo'],
                ['id' => 3, 'nombre' => 'Recuperándose']
            ]
        ];
        
        echo json_encode(['ok' => true, 'data' => $catalogs, 'catalogs' => $catalogs]);
        
    } elseif ($method === 'POST' && isset($_GET['seed']) && $_GET['seed'] === '1') {
        // Inicializar catálogos (simulado)
        echo json_encode([
            'ok' => true, 
            'message' => 'Catálogos inicializados correctamente',
            'created' => [
                'tipos_especie' => 3,
                'fases' => 4,
                'ubicaciones' => 3,
                'tipos_tratamiento' => 3
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
