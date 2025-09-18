<?php
// Script de diagnóstico de conexión y tablas
require_once __DIR__ . '/../conection.php';

try {
    // Probar conexión
    $pdo = db();
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Listar tablas existentes
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✅ Tablas encontradas: " . implode(', ', $tables) . "\n";
    
    // Verificar tablas necesarias
    $requiredTables = [
        'usuarios', 'roles', 'tipo_especie', 'estado_salud', 'tipo_destino', 
        'estado_fase', 'fases_produccion', 'clasificaciones_calidad', 
        'tamanos_plantas', 'tipos_tratamiento', 'causas_perdidas', 
        'motivos_descartes', 'ubicaciones'
    ];
    
    $missingTables = array_diff($requiredTables, $tables);
    if (empty($missingTables)) {
        echo "✅ Todas las tablas necesarias existen\n";
    } else {
        echo "❌ Faltan las siguientes tablas: " . implode(', ', $missingTables) . "\n";
    }
    
    // Verificar usuarios existentes
    if (in_array('usuarios', $tables)) {
        $stmt = $pdo->query("SELECT username, password_hash FROM usuarios");
        $users = $stmt->fetchAll();
        echo "✅ Usuarios encontrados: " . count($users) . "\n";
        foreach ($users as $user) {
            echo "   - {$user['username']}: {$user['password_hash']}\n";
        }
    }
    
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
