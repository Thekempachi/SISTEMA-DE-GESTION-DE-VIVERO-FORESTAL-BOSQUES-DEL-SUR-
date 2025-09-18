<?php
// Script para instalar todas las tablas necesarias
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Crear tablas si no existen
    $sql = [];
    
    // Tabla roles
    $sql[] = "CREATE TABLE IF NOT EXISTS roles (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    // Tabla usuarios
    $sql[] = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        rol_id INT,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rol_id) REFERENCES roles(id)
    )";
    
    // Tablas de catálogo
    $sql[] = "CREATE TABLE IF NOT EXISTS tipo_especie (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS estado_salud (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS tipo_destino (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS estado_fase (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS fases_produccion (
        id INT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT,
        orden INT,
        duracion_min_meses INT,
        duracion_max_meses INT,
        capacidad_lote INT,
        maceta_tamano_ml INT,
        parametros_defecto JSON
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS clasificaciones_calidad (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS tamanos_plantas (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS tipos_tratamiento (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS causas_perdidas (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS motivos_descartes (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    $sql[] = "CREATE TABLE IF NOT EXISTS ubicaciones (
        id INT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL,
        descripcion TEXT
    )";
    
    // Ejecutar todas las consultas
    foreach ($sql as $query) {
        $pdo->exec($query);
        echo "✅ Tabla creada/verificada\n";
    }
    
    echo "✅ Todas las tablas han sido creadas exitosamente\n";
    
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
