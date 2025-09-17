-- Sistema de Gestión de Vivero - Creación de tablas mínimas
-- Ejecutar este script en phpMyAdmin si las tablas no existen

-- Eliminar tablas si existen (opcional, para limpieza)
-- DROP TABLE IF EXISTS despachos;
-- DROP TABLE IF EXISTS despacho_detalles;
-- DROP TABLE IF EXISTS tratamientos_planta;
-- DROP TABLE IF EXISTS condiciones_planta;
-- DROP TABLE IF EXISTS inventario;
-- DROP TABLE IF EXISTS plantas;
-- DROP TABLE IF EXISTS fases_lote;
-- DROP TABLE IF EXISTS lotes;
-- DROP TABLE IF EXISTS especies;
-- DROP TABLE IF EXISTS usuarios;
-- DROP TABLE IF EXISTS roles;

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    email VARCHAR(100),
    nombre_completo VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de especies
CREATE TABLE IF NOT EXISTS especies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_comun VARCHAR(100) NOT NULL,
    nombre_cientifico VARCHAR(100) NOT NULL,
    descripcion TEXT,
    origen VARCHAR(100),
    tipo_suelo VARCHAR(100),
    clima VARCHAR(100),
    tiempo_germinacion INT COMMENT 'Días',
    tiempo_crecimiento INT COMMENT 'Días',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de lotes
CREATE TABLE IF NOT EXISTS lotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    especie_id INT NOT NULL,
    proveedor VARCHAR(100),
    cantidad_semillas INT DEFAULT 0,
    fecha_siembra DATE,
    fecha_estimada_cosecha DATE,
    estado ENUM('activo', 'completado', 'cancelado') DEFAULT 'activo',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (especie_id) REFERENCES especies(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de fases de lote
CREATE TABLE IF NOT EXISTS fases_lote (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_id INT NOT NULL,
    fase VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de plantas
CREATE TABLE IF NOT EXISTS plantas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_qr VARCHAR(100) NOT NULL UNIQUE,
    lote_id INT NOT NULL,
    especie_id INT NOT NULL,
    fecha_siembra DATE,
    estado ENUM('semilla', 'germinacion', 'crecimiento', 'madura', 'cosechada') DEFAULT 'semilla',
    ubicacion VARCHAR(100),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lote_id) REFERENCES lotes(id) ON DELETE RESTRICT,
    FOREIGN KEY (especie_id) REFERENCES especies(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de inventario
CREATE TABLE IF NOT EXISTS inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    planta_id INT NOT NULL UNIQUE,
    clasificacion VARCHAR(50),
    tamano VARCHAR(50),
    cantidad INT DEFAULT 1,
    ubicacion VARCHAR(100),
    fecha_actualizacion DATE,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (planta_id) REFERENCES plantas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de tratamientos por planta
CREATE TABLE IF NOT EXISTS tratamientos_planta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    planta_id INT NOT NULL,
    tipo_tratamiento VARCHAR(50) NOT NULL,
    producto VARCHAR(100),
    dosis VARCHAR(50),
    fecha_aplicacion DATE NOT NULL,
    proxima_aplicacion DATE,
    responsable VARCHAR(100),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (planta_id) REFERENCES plantas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de condiciones por planta
CREATE TABLE IF NOT EXISTS condiciones_planta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    planta_id INT NOT NULL,
    tipo_condicion VARCHAR(50) NOT NULL,
    valor VARCHAR(50),
    fecha_registro DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (planta_id) REFERENCES plantas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de despachos
CREATE TABLE IF NOT EXISTS despachos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    fecha_despacho DATE NOT NULL,
    cliente VARCHAR(100),
    destino VARCHAR(100),
    estado ENUM('pendiente', 'en_proceso', 'completado', 'cancelado') DEFAULT 'pendiente',
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalles de despacho
CREATE TABLE IF NOT EXISTS despacho_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    despacho_id INT NOT NULL,
    planta_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (despacho_id) REFERENCES despachos(id) ON DELETE CASCADE,
    FOREIGN KEY (planta_id) REFERENCES plantas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles básicos
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Admin', 'Acceso completo al sistema'),
(2, 'Técnico', 'Gestión de cultivos y operaciones'),
(3, 'Logística', 'Gestión de despachos e inventario')
ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), descripcion=VALUES(descripcion);

-- Insertar usuario admin por defecto (contraseña: admin123)
-- Nota: La contraseña se almacenará como hash cuando el usuario inicie sesión por primera vez
-- o cuando se ejecute el endpoint de seed
INSERT INTO usuarios (id, username, password_hash, rol_id, email, nombre_completo, activo) VALUES
(1, 'admin', 'admin123', 1, 'admin@vivero.com', 'Administrador del Sistema', TRUE)
ON DUPLICATE KEY UPDATE username=VALUES(username), password_hash=VALUES(password_hash), rol_id=VALUES(rol_id);

-- Insertar usuarios técnicos de ejemplo (contraseñas en texto plano para migración)
INSERT INTO usuarios (username, password_hash, rol_id, email, nombre_completo, activo) VALUES
('tecnico1', 'hash_tecnic', 2, 'tecnico1@vivero.com', 'Técnico 1', TRUE),
('logistica', 'hash_logis', 3, 'logistica@vivero.com', 'Logística 1', TRUE)
ON DUPLICATE KEY UPDATE username=VALUES(username), password_hash=VALUES(password_hash), rol_id=VALUES(rol_id);

-- Insertar especies de ejemplo
INSERT INTO especies (nombre_comun, nombre_cientifico, descripcion, origen, tipo_suelo, clima, tiempo_germinacion, tiempo_crecimiento) VALUES
('Pino', 'Pinus spp.', 'Pino para reforestación', 'Nativo', 'Ácido', 'Frío', 30, 365),
('Eucalipto', 'Eucalyptus globulus', 'Eucalipto de crecimiento rápido', 'Australia', 'Variado', 'Templado', 14, 180),
('Roble', 'Quercus robur', 'Roble europeo', 'Europa', 'Arcilloso', 'Templado', 45, 730),
('Ciprés', 'Cupressus sempervirens', 'Ciprés mediterráneo', 'Mediterráneo', 'Calcáreo', 'Seco', 21, 240)
ON DUPLICATE KEY UPDATE nombre_comun=VALUES(nombre_comun), nombre_cientifico=VALUES(nombre_cientifico);

-- Crear índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_usuarios_username ON usuarios(username);
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol_id);
CREATE INDEX IF NOT EXISTS idx_especies_nombre ON especies(nombre_comun);
CREATE INDEX IF NOT EXISTS idx_lotes_especie ON lotes(especie_id);
CREATE INDEX IF NOT EXISTS idx_lotes_estado ON lotes(estado);
CREATE INDEX IF NOT EXISTS idx_fases_lote ON fases_lote(lote_id);
CREATE INDEX IF NOT EXISTS idx_plantas_lote ON plantas(lote_id);
CREATE INDEX IF NOT EXISTS idx_plantas_especie ON plantas(especie_id);
CREATE INDEX IF NOT EXISTS idx_plantas_estado ON plantas(estado);
CREATE INDEX IF NOT EXISTS idx_inventario_planta ON inventario(planta_id);
CREATE INDEX IF NOT EXISTS idx_tratamientos_planta ON tratamientos_planta(planta_id);
CREATE INDEX IF NOT EXISTS idx_condiciones_planta ON condiciones_planta(planta_id);
CREATE INDEX IF NOT EXISTS idx_despachos_estado ON despachos(estado);
CREATE INDEX IF NOT EXISTS idx_despacho_detalles_despacho ON despacho_detalles(despacho_id);
CREATE INDEX IF NOT EXISTS idx_despacho_detalles_planta ON despacho_detalles(planta_id);

-- Comentarios sobre la estructura
/*
Notas importantes:

1. Migración de contraseñas:
   - Los usuarios insertados tienen contraseñas en texto plano en password_hash
   - Al iniciar sesión por primera vez, el sistema migrará automáticamente a hash seguro
   - Usa el endpoint set_password.php para asignar contraseñas seguras manualmente

2. Usuarios por defecto:
   - admin / admin123 (rol Admin)
   - tecnico1 / hash_tecnic (rol Técnico)
   - logistica / hash_logis (rol Logística)

3. Para usar el sistema:
   - Ejecuta este script en phpMyAdmin
   - Configura las credenciales en backend/php/conection.php
   - Visita login.html e inicia sesión
   - O usa el botón "Crear usuario admin de prueba" en login.html

4. Seguridad:
   - Cambia las contraseñas por defecto después del primer inicio de sesión
   - Usa contraseñas fuertes para todos los usuarios
   - Desactiva APP_DEBUG en producción

5. Mantenimiento:
   - Las tablas usan InnoDB para integridad referencial
   - Los timestamps se actualizan automáticamente
   - Los índices mejoran el rendimiento en consultas frecuentes
*/
