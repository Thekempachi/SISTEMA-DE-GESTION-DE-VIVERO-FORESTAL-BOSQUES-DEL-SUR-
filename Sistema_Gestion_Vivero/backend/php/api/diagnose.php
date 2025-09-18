<?php
// Script de diagnóstico para verificar el estado del sistema
require_once __DIR__ . '/../conection.php';

echo "🔍 DIAGNÓSTICO DEL SISTEMA DE VIVERO\n";
echo "=====================================\n\n";

try {
    // 1. Verificar conexión a base de datos
    echo "1. 🔌 CONEXIÓN A BASE DE DATOS\n";
    $pdo = db();
    echo "   ✅ Conexión exitosa\n";
    echo "   📊 Base de datos: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "\n\n";
    
    // 2. Verificar tablas principales
    echo "2. 📋 VERIFICACIÓN DE TABLAS\n";
    $tables = ['usuarios', 'roles'];
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "   ✅ Tabla '$table': $count registros\n";
        } catch (Exception $e) {
            echo "   ❌ Tabla '$table': Error - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // 3. Verificar usuarios
    echo "3. 👥 USUARIOS REGISTRADOS\n";
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.nombre, r.nombre as rol, 
               CASE 
                   WHEN u.password_hash LIKE '$%' THEN 'Hash seguro'
                   WHEN u.password_hash = '' THEN 'Sin contraseña'
                   ELSE 'Hash inseguro/temporal'
               END as estado_password
        FROM usuarios u 
        LEFT JOIN roles r ON u.rol_id = r.id 
        ORDER BY u.id
    ");
    
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $user['estado_password'] === 'Hash seguro' ? '✅' : '⚠️';
        echo "   $status ID: {$user['id']} | Usuario: {$user['username']} | Nombre: {$user['nombre']} | Rol: {$user['rol']} | Password: {$user['estado_password']}\n";
    }
    echo "\n";
    
    // 4. Verificar variables de entorno
    echo "4. ⚙️  CONFIGURACIÓN\n";
    echo "   📍 APP_DEBUG: " . (getenv('APP_DEBUG') === '1' ? 'Activado ✅' : 'Desactivado') . "\n";
    echo "   🗄️  DB_HOST: " . (getenv('DB_HOST') ?: 'No configurado') . "\n";
    echo "   📊 DB_NAME: " . (getenv('DB_NAME') ?: 'No configurado') . "\n";
    echo "   👤 DB_USER: " . (getenv('DB_USER') ?: 'No configurado') . "\n";
    echo "\n";
    
    // 5. Probar autenticación
    echo "5. 🔐 PRUEBA DE AUTENTICACIÓN\n";
    require_once __DIR__ . '/../service/AuthService.php';
    require_once __DIR__ . '/../repository/UserRepository.php';
    
    $service = new AuthService(new UserRepository($pdo));
    $testCredentials = [
        'admin' => 'admin',
        'tecnico1' => 'tecnico',
        'logistica' => 'logistica'
    ];
    
    foreach ($testCredentials as $username => $password) {
        try {
            $user = $service->login($username, $password);
            echo "   ✅ Login exitoso: $username\n";
            // Logout inmediato para no interferir
            $service->logout();
        } catch (Exception $e) {
            echo "   ❌ Login fallido: $username - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    echo "🎯 RECOMENDACIONES\n";
    echo "==================\n";
    echo "Si hay problemas de login:\n";
    echo "1. Ejecutar: php fix_passwords.php\n";
    echo "2. Verificar que APP_DEBUG=1 en .env\n";
    echo "3. Usar contraseña de emergencia: 'emergencia123'\n";
    echo "4. Revisar logs del servidor web\n\n";
    
} catch (Throwable $e) {
    echo "❌ Error crítico: " . $e->getMessage() . "\n";
    echo "📋 Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "\n";
}
