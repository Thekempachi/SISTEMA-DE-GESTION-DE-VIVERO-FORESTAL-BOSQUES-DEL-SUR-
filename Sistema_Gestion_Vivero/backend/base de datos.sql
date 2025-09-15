-- ===========================================================
-- Base de datos: Bosques del Sur - Implementación del Diagrama UML
-- Sistema de Gestión de Vivero Forestal
-- ===========================================================

DROP DATABASE IF EXISTS bosques_del_sur;
CREATE DATABASE bosques_del_sur CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bosques_del_sur;

-- =========================
-- 1) TABLAS DE CATÁLOGO
-- =========================

-- Tipos de especies forestales
CREATE TABLE tipo_especie (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Estados de salud de las plantas
CREATE TABLE estado_salud (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Tipos de destino para distribución
CREATE TABLE tipo_destino (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Estados de las fases de producción
CREATE TABLE estado_fase (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Fases del proceso de producción
CREATE TABLE fases_produccion (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  descripcion TEXT,
  orden TINYINT UNSIGNED NOT NULL UNIQUE,
  duracion_min_meses INT,
  duracion_max_meses INT,
  capacidad_lote INT,
  maceta_tamano_ml INT,
  parametros_defecto TEXT
) ENGINE=InnoDB;

-- Clasificaciones de calidad
CREATE TABLE clasificaciones_calidad (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT,
  criterios TEXT
) ENGINE=InnoDB;

-- Tamaños de plantas
CREATE TABLE tamanos_plantas (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(10) NOT NULL,
  nombre VARCHAR(50) NOT NULL,
  altura_min_cm DECIMAL(5,2),
  altura_max_cm DECIMAL(5,2),
  descripcion TEXT
) ENGINE=InnoDB;

-- Tipos de tratamiento
CREATE TABLE tipos_tratamiento (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT
) ENGINE=InnoDB;

-- Causas de pérdidas
CREATE TABLE causas_perdidas (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descripcion VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Motivos de descartes
CREATE TABLE motivos_descartes (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descripcion VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Roles de usuarios
CREATE TABLE roles (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  descripcion TEXT
) ENGINE=InnoDB;

-- =========================
-- 2) ENTIDADES PRINCIPALES
-- =========================

-- Especies forestales
CREATE TABLE especies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre_cientifico VARCHAR(100) NOT NULL,
  nombre_comun VARCHAR(100) NOT NULL,
  tipo_especie_id TINYINT UNSIGNED NOT NULL,
  categoria VARCHAR(50),
  notas TEXT,
  descripcion TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tipo_especie_id) REFERENCES tipo_especie(id)
) ENGINE=InnoDB;

-- Proveedores de semillas
CREATE TABLE proveedores_semillas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  contacto VARCHAR(100),
  telefono VARCHAR(20),
  email VARCHAR(100),
  direccion TEXT,
  identificacion_fiscal VARCHAR(50),
  certificado VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Lotes de semillas
CREATE TABLE lotes_semillas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proveedor_id INT NOT NULL,
  procedencia VARCHAR(100),
  certificado VARCHAR(100),
  tasa_germinacion DECIMAL(5,2),
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proveedor_id) REFERENCES proveedores_semillas(id)
) ENGINE=InnoDB;

-- Lotes de producción
CREATE TABLE lotes_produccion (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  especie_id INT NOT NULL,
  lote_semillas_id INT NOT NULL,
  fecha_siembra DATE NOT NULL,
  cantidad_semillas_usadas INT NOT NULL,
  notas TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (especie_id) REFERENCES especies(id),
  FOREIGN KEY (lote_semillas_id) REFERENCES lotes_semillas(id)
) ENGINE=InnoDB;

-- Ubicaciones físicas en el vivero
CREATE TABLE ubicaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sector VARCHAR(50) NOT NULL,
  fila INT NOT NULL,
  posicion INT NOT NULL,
  descripcion TEXT,
  activo BOOLEAN NOT NULL DEFAULT TRUE,
  UNIQUE KEY uq_ubicacion (sector, fila, posicion)
) ENGINE=InnoDB;

-- Usuarios del sistema
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  rol_id TINYINT UNSIGNED NOT NULL,
  email VARCHAR(100),
  mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
  last_login TIMESTAMP NULL,
  failed_attempts INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- =========================
-- 3) CONTROL DE FASES POR LOTE
-- =========================

-- Control de fases por lote
CREATE TABLE lote_fase (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lote_produccion_id INT NOT NULL,
  fase_id TINYINT UNSIGNED NOT NULL,
  estado_id TINYINT UNSIGNED NOT NULL,
  ubicacion_id INT,
  fecha_inicio DATETIME NULL,
  fecha_fin DATETIME NULL,
  stock_inicial INT NOT NULL DEFAULT 0,
  stock_disponible INT NOT NULL DEFAULT 0,
  responsable_id INT NOT NULL,
  en_progreso TINYINT NULL,
  notas TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lote_produccion_id) REFERENCES lotes_produccion(id),
  FOREIGN KEY (fase_id) REFERENCES fases_produccion(id),
  FOREIGN KEY (estado_id) REFERENCES estado_fase(id),
  FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id),
  FOREIGN KEY (responsable_id) REFERENCES usuarios(id),
  UNIQUE KEY uq_lote_enprogreso (lote_produccion_id, en_progreso),
  UNIQUE KEY uq_ubi_fase_enprogreso (ubicacion_id, fase_id, en_progreso)
) ENGINE=InnoDB;

-- Reportes diarios del lote/fase
CREATE TABLE reportes_diarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lote_fase_id INT NOT NULL,
  fecha DATE NOT NULL,
  avance_dia INT NOT NULL DEFAULT 0,
  perdidas_dia INT NOT NULL DEFAULT 0,
  descartes_dia INT NOT NULL DEFAULT 0,
  resumen_tratamientos TEXT,
  resumen_condiciones TEXT,
  observaciones TEXT,
  responsable_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lote_fase_id) REFERENCES lote_fase(id),
  FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Resultados de cada fase
CREATE TABLE resultados_fase (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lote_fase_id INT NOT NULL,
  fecha DATE NOT NULL,
  plantas_avanzan INT NOT NULL,
  plantas_perdidas INT NOT NULL DEFAULT 0,
  plantas_descartadas INT NOT NULL DEFAULT 0,
  observaciones TEXT,
  responsable_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lote_fase_id) REFERENCES lote_fase(id),
  FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Detalles de pérdidas
CREATE TABLE detalles_perdidas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  resultados_fase_id INT NOT NULL,
  causa_id TINYINT UNSIGNED NOT NULL,
  cantidad INT NOT NULL,
  observaciones TEXT,
  FOREIGN KEY (resultados_fase_id) REFERENCES resultados_fase(id),
  FOREIGN KEY (causa_id) REFERENCES causas_perdidas(id)
) ENGINE=InnoDB;

-- Detalles de descartes
CREATE TABLE detalles_descartes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  resultados_fase_id INT NOT NULL,
  motivo_id TINYINT UNSIGNED NOT NULL,
  cantidad INT NOT NULL,
  observaciones TEXT,
  FOREIGN KEY (resultados_fase_id) REFERENCES resultados_fase(id),
  FOREIGN KEY (motivo_id) REFERENCES motivos_descartes(id)
) ENGINE=InnoDB;

-- =========================
-- 4) OPERACIONES Y REGISTROS
-- =========================

-- Tratamientos aplicados
CREATE TABLE tratamientos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lote_fase_id INT NOT NULL,
  tipo_tratamiento_id TINYINT UNSIGNED NOT NULL,
  usuario_id INT NOT NULL,
  fecha DATETIME NOT NULL,
  producto VARCHAR(100),
  dosis VARCHAR(50),
  motivo VARCHAR(100),
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lote_fase_id) REFERENCES lote_fase(id),
  FOREIGN KEY (tipo_tratamiento_id) REFERENCES tipos_tratamiento(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Condiciones ambientales
CREATE TABLE condiciones_ambientales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lote_fase_id INT NOT NULL,
  ubicacion_id INT,
  fecha DATETIME NOT NULL,
  temperatura DECIMAL(5,2),
  humedad DECIMAL(5,2),
  precipitaciones DECIMAL(5,2),
  fuente VARCHAR(20),
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (lote_fase_id) REFERENCES lote_fase(id),
  FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id)
) ENGINE=InnoDB;

-- Umbrales para alertas
CREATE TABLE umbrales_alertas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  especie_id INT NOT NULL,
  fase_id TINYINT UNSIGNED NOT NULL,
  variable VARCHAR(50) NOT NULL,
  valor_min DECIMAL(5,2),
  valor_max DECIMAL(5,2),
  descripcion TEXT,
  FOREIGN KEY (especie_id) REFERENCES especies(id),
  FOREIGN KEY (fase_id) REFERENCES fases_produccion(id)
) ENGINE=InnoDB;

-- Alertas del sistema
CREATE TABLE alertas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  umbral_id INT NOT NULL,
  lote_fase_id INT NOT NULL,
  fecha DATETIME NOT NULL,
  descripcion TEXT,
  estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  usuario_notificado_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (umbral_id) REFERENCES umbrales_alertas(id),
  FOREIGN KEY (lote_fase_id) REFERENCES lote_fase(id),
  FOREIGN KEY (usuario_notificado_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- =========================
-- 5) PLANTAS E INVENTARIO
-- =========================

-- Plantas individuales
CREATE TABLE plantas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lote_produccion_id INT NOT NULL,
  codigo_qr VARCHAR(50) NOT NULL UNIQUE,
  fecha_etiquetado DATE NOT NULL,
  altura_actual_cm DECIMAL(5,2),
  estado_salud_id TINYINT UNSIGNED NOT NULL,
  ubicacion_id INT,
  vigencia_meses INT DEFAULT 24,
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lote_produccion_id) REFERENCES lotes_produccion(id),
  FOREIGN KEY (estado_salud_id) REFERENCES estado_salud(id),
  FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id)
) ENGINE=InnoDB;

-- Movimientos de inventario
CREATE TABLE movimientos_inventario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  planta_id INT NOT NULL,
  tipo_movimiento VARCHAR(20) NOT NULL,
  ubicacion_origen_id INT,
  ubicacion_destino_id INT,
  fecha DATETIME NOT NULL,
  responsable_id INT NOT NULL,
  observaciones TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (planta_id) REFERENCES plantas(id),
  FOREIGN KEY (ubicacion_origen_id) REFERENCES ubicaciones(id),
  FOREIGN KEY (ubicacion_destino_id) REFERENCES ubicaciones(id),
  FOREIGN KEY (responsable_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Inventario de plantas
CREATE TABLE inventario_plantas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  planta_id INT NOT NULL,
  clasificacion_calidad_id TINYINT UNSIGNED,
  tamano_id TINYINT UNSIGNED,
  fecha_ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (planta_id) REFERENCES plantas(id),
  FOREIGN KEY (clasificacion_calidad_id) REFERENCES clasificaciones_calidad(id),
  FOREIGN KEY (tamano_id) REFERENCES tamanos_plantas(id)
) ENGINE=InnoDB;

-- =========================
-- 6) DESTINOS Y SALIDAS
-- =========================

-- Destinos finales
CREATE TABLE destinos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  tipo_destino_id TINYINT UNSIGNED NOT NULL,
  contacto TEXT,
  FOREIGN KEY (tipo_destino_id) REFERENCES tipo_destino(id)
) ENGINE=InnoDB;

-- Órdenes de despacho
CREATE TABLE ordenes_despacho (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(20) NOT NULL UNIQUE,
  destino_id INT NOT NULL,
  fecha DATETIME NOT NULL,
  responsable_despacho_id INT NOT NULL,
  personal_transporte VARCHAR(100),
  notas TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (destino_id) REFERENCES destinos(id),
  FOREIGN KEY (responsable_despacho_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Líneas de órdenes de despacho
CREATE TABLE ordenes_despacho_lineas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  orden_despacho_id INT NOT NULL,
  planta_id INT NOT NULL,
  cantidad INT NOT NULL,
  estado_al_despacho_id TINYINT UNSIGNED NOT NULL,
  observaciones TEXT,
  FOREIGN KEY (orden_despacho_id) REFERENCES ordenes_despacho(id),
  FOREIGN KEY (planta_id) REFERENCES plantas(id),
  FOREIGN KEY (estado_al_despacho_id) REFERENCES estado_salud(id)
) ENGINE=InnoDB;

-- =========================
-- 7) AUDITORÍA Y SEGURIDAD
-- =========================

-- Logs de auditoría
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entidad VARCHAR(50) NOT NULL,
  entidad_id INT NOT NULL,
  accion VARCHAR(20) NOT NULL,
  usuario_id INT NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  cambios JSON,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;