<?php
// Script para corregir las contraseñas de usuarios existentes
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Definir las contraseñas correctas
    $users = [
        'admin' => 'admin',
        'tecnico1' => 'tecnico',
        'logistica' => 'logistica',
        'user1' => 'user'
    ];
    
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE username = ?");
    
    foreach ($users as $username => $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$hash, $username]);
        
        if ($stmt->rowCount() > 0) {
            echo "✅ Contraseña actualizada para usuario: $username\n";
        } else {
            echo "⚠️  Usuario no encontrado o ya actualizado: $username\n";
        }
    }
    
    echo "\n✅ Proceso de corrección de contraseñas completado\n";
    echo "📋 Credenciales actualizadas:\n";
    echo "   - admin / admin (Administrador)\n";
    echo "   - tecnico1 / tecnico (Técnico)\n";
    echo "   - logistica / logistica (Logística)\n";
    echo "   - user1 / user (Usuario)\n";
    
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📋 Stack trace: " . $e->getTraceAsString() . "\n";
}
