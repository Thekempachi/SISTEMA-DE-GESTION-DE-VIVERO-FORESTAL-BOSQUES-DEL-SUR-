<?php
// Configuración local para desarrollo
// Este archivo debe ser incluido antes de conection.php para sobrescribir las configuraciones

// Configuración de base de datos local
putenv('DB_HOST=localhost');
putenv('DB_NAME=vivero_bosques');
putenv('DB_USER=root');
putenv('DB_PASS=');

// Habilitar modo debug
putenv('APP_DEBUG=1');
putenv('APP_ENV=development');

// Configuración CORS para desarrollo local
putenv('CORS_ALLOW_ORIGIN=*');
?>
