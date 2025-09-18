<?php
// Script para poblar las tablas con datos iniciales
require_once __DIR__ . '/../conection.php';

try {
    $pdo = db();
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Insertar roles
    $pdo->exec("INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
        (1, 'Admin', 'Administrador del sistema'),
        (2, 'Técnico', 'Técnico de vivero'),
        (3, 'Logística', 'Encargado de logística'),
        (4, 'Usuario', 'Usuario básico')");
    echo "✅ Roles insertados\n";
    
    // Insertar usuarios con contraseñas en texto plano para migración
    $pdo->exec("INSERT IGNORE INTO usuarios (id, username, password_hash, nombre, rol_id, email) VALUES
        (1, 'admin', 'hash_admin', 'Administrador', 1, 'admin@vivero.com'),
        (2, 'tecnico1', 'hash_tecnico', 'Técnico 1', 2, 'tecnico1@vivero.com'),
        (3, 'logi1', 'hash_logi', 'Logística 1', 3, 'logi1@vivero.com'),
        (4, 'user1', 'hash_user', 'Usuario 1', 4, 'user1@vivero.com')");
    echo "✅ Usuarios insertados\n";
    
    // Insertar catálogos
    $pdo->exec("INSERT IGNORE INTO tipo_especie (id, nombre) VALUES
        (1, 'Coníferas'), (2, 'Latifoliadas'), (3, 'Frutales nativos')");
    
    $pdo->exec("INSERT IGNORE INTO estado_salud (id, nombre) VALUES
        (1, 'Excelente'), (2, 'Bueno'), (3, 'Regular'), (4, 'Malo')");
    
    $pdo->exec("INSERT IGNORE INTO tipo_destino (id, nombre) VALUES
        (1, 'Proyecto'), (2, 'Cliente'), (3, 'Municipalidad')");
    
    $pdo->exec("INSERT IGNORE INTO estado_fase (id, nombre) VALUES
        (1, 'Planeada'), (2, 'En progreso'), (3, 'Completada')");
    
    $pdo->exec("INSERT IGNORE INTO fases_produccion (id, nombre, descripcion, orden, duracion_min_meses, duracion_max_meses, capacidad_lote, maceta_tamano_ml, parametros_defecto) VALUES
        (1, 'Germinación', '0-2 meses, almácigos controlados', 1, 0, 2, 10000, NULL, '{\"riego\":\"diario\"}'),
        (2, 'Desarrollo inicial', '2-6 meses, macetas 200ml, invernadero sombra parcial', 2, 2, 6, NULL, 200, '{\"fertilización\":\"quincenal\"}'),
        (3, 'Crecimiento juvenil', '6-12 meses, macetas 500ml, semi-sombra', 3, 6, 12, NULL, 500, '{\"adaptación\":\"gradual\"}'),
        (4, 'Maduración', '12-18 meses, macetas 1L, exterior', 4, 12, 18, NULL, 1000, '{\"preparación\":\"comercialización\"}')");
    
    $pdo->exec("INSERT IGNORE INTO clasificaciones_calidad (id, nombre, descripcion) VALUES
        (1, 'Premium', 'Plantas perfectas, sin defectos'),
        (2, 'Comercial', 'Características comerciales aceptables'),
        (3, 'Estándar', 'Calidad básica para proyectos')");
    
    $pdo->exec("INSERT IGNORE INTO tamanos_plantas (id, nombre, descripcion) VALUES
        (1, 'Pequeña', 'Altura < 30cm'),
        (2, 'Mediana', 'Altura 30-60cm'),
        (3, 'Grande', 'Altura > 60cm')");
    
    $pdo->exec("INSERT IGNORE INTO tipos_tratamiento (id, nombre, descripcion) VALUES
        (1, 'Fertilización', 'Aplicación de nutrientes'),
        (2, 'Control de plagas', 'Tratamientos fitosanitarios'),
        (3, 'Poda', 'Podas de formación y mantenimiento')");
    
    $pdo->exec("INSERT IGNORE INTO causas_perdidas (id, nombre, descripcion) VALUES
        (1, 'Enfermedad', 'Muerte por enfermedades'),
        (2, 'Plagas', 'Daño por plagas'),
        (3, 'Condiciones ambientales', 'Muerte por factores ambientales')");
    
    $pdo->exec("INSERT IGNORE INTO motivos_descartes (id, nombre, descripcion) VALUES
        (1, 'Calidad insuficiente', 'No cumple estándares de calidad'),
        (2, 'Daño físico', 'Daños durante el manejo'),
        (3, 'Sobreproducción', 'Exceso de inventario')");
    
    $pdo->exec("INSERT IGNORE INTO ubicaciones (id, nombre, descripcion) VALUES
        (1, 'Invernadero 1', 'Invernadero principal'),
        (2, 'Invernadero 2', 'Invernadero secundario'),
        (3, 'Área exterior', 'Zona de aclimatación exterior')");
    
    echo "✅ Todos los datos iniciales han sido insertados\n";
    echo "✅ Instalación completada. Ahora puedes usar el sistema.\n";
    
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
