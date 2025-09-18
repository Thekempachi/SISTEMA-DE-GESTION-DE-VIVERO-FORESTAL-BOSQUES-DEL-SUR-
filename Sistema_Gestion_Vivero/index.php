<?php
// Página principal simple
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vivero Bosques del Sur</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f9f4; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #184d47; text-align: center; }
        .status { padding: 15px; margin: 20px 0; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        a { color: #184d47; text-decoration: none; padding: 10px 20px; background: #e8f5e8; border-radius: 6px; display: inline-block; margin: 5px; }
        a:hover { background: #d4edda; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌱 Sistema de Gestión de Vivero "Bosques del Sur"</h1>
        
        <div class="status success">
            ✅ Servidor PHP funcionando correctamente
        </div>
        
        <div class="status info">
            📅 Fecha actual: <?php echo date('Y-m-d H:i:s'); ?><br>
            🐘 PHP Version: <?php echo phpversion(); ?><br>
            📂 Directorio: <?php echo __DIR__; ?>
        </div>
        
        <h2>🔗 Enlaces rápidos:</h2>
        <p>
            <a href="frontend/html/login.html">🔑 Login</a>
            <a href="frontend/html/index.html">📊 Dashboard</a>
            <a href="test_server.php">🔍 Diagnóstico</a>
        </p>
        
        <h2>📋 Estado del sistema:</h2>
        <?php
        // Verificar extensiones PHP
        $extensions = ['pdo', 'pdo_mysql', 'json'];
        echo "<ul>";
        foreach ($extensions as $ext) {
            $status = extension_loaded($ext) ? '✅' : '❌';
            echo "<li>$status PHP Extension: $ext</li>";
        }
        
        // Verificar archivos importantes
        $files = [
            'frontend/html/login.html' => 'Login HTML',
            'frontend/css/styles.css' => 'CSS Styles',
            'backend/php/api/auth.php' => 'Auth API',
            '.env' => 'Environment Config'
        ];
        
        foreach ($files as $file => $name) {
            $status = file_exists($file) ? '✅' : '❌';
            echo "<li>$status $name</li>";
        }
        echo "</ul>";
        
        // Verificar conexión a BD
        try {
            if (file_exists('.env')) {
                $env = file_get_contents('.env');
                $lines = explode("\n", $env);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line && !str_starts_with($line, '#')) {
                        $parts = explode('=', $line, 2);
                        if (count($parts) === 2) {
                            putenv($line);
                        }
                    }
                }
            }
            
            require_once 'backend/php/conection.php';
            $pdo = db();
            echo '<div class="status success">✅ Conexión a base de datos exitosa</div>';
        } catch (Exception $e) {
            echo '<div class="status error">❌ Error de base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <h2>🚨 Credenciales de acceso:</h2>
        <div class="status info">
            <strong>Usuarios por defecto:</strong><br>
            • admin / admin<br>
            • tecnico1 / tecnico<br>
            • logistica / logistica<br>
            <br>
            <strong>Emergencia:</strong> cualquier_usuario / emergencia123
        </div>
    </div>
</body>
</html>
