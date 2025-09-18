<?php
class CatalogRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    // Minimal seed: solo roles y usuario admin, sin datos de catálogos
    public function seedMinimal(): void {
        // roles
        $this->pdo->exec("INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
            (1,'Admin','Administrador del sistema'),(2,'Técnico','Técnico de vivero')");
        // usuario admin si no existe
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute(['admin']);
        if (!$stmt->fetch()) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (username, password_hash, nombre, rol_id, email) VALUES (?,?,?,?,?)");
            $stmt->execute(['admin', $hash, 'Administrador', 1, 'admin@example.com']);
        }
    }

    public function seed(): void {
        // tipo_especie
        $this->pdo->exec("INSERT IGNORE INTO tipo_especie (id, nombre) VALUES
            (1,'Coníferas'),(2,'Latifoliadas'),(3,'Frutales nativos')");
        // estado_salud
        $this->pdo->exec("INSERT IGNORE INTO estado_salud (id, nombre) VALUES
            (1,'Excelente'),(2,'Bueno'),(3,'Regular'),(4,'Malo')");
        // tipo_destino
        $this->pdo->exec("INSERT IGNORE INTO tipo_destino (id, nombre) VALUES
            (1,'Proyecto'),(2,'Cliente'),(3,'Municipalidad')");
        // estado_fase
        $this->pdo->exec("INSERT IGNORE INTO estado_fase (id, nombre) VALUES
            (1,'Planeada'),(2,'En progreso'),(3,'Completada')");
        // fases_produccion
        $stmt = $this->pdo->query("SELECT COUNT(*) AS c FROM fases_produccion");
        if ((int)$stmt->fetch()['c'] === 0) {
            $sql = "INSERT INTO fases_produccion
                (id, nombre, descripcion, orden, duracion_min_meses, duracion_max_meses, capacidad_lote, maceta_tamano_ml, parametros_defecto)
                VALUES
                (1,'Germinación','0-2 meses, almácigos controlados',1,0,2,10000,NULL,'{\"riego\":\"diario\"}'),
                (2,'Desarrollo inicial','2-6 meses, macetas 200ml, invernadero sombra parcial',2,2,6,NULL,200,'{\"fertilización\":\"quincenal\"}'),
                (3,'Crecimiento juvenil','6-12 meses, macetas 500ml, semi-sombra',3,6,12,NULL,500,'{\"adaptación\":\"gradual\"}'),
                (4,'Maduración','12-18 meses, macetas 1L, exterior',4,12,18,NULL,1000,'{\"preparación\":\"comercialización\"}')";
            $this->pdo->exec($sql);
        }
        // clasificaciones_calidad
        $this->pdo->exec("INSERT IGNORE INTO clasificaciones_calidad (id, nombre, descripcion) VALUES
            (1,'Premium','Plantas perfectas, sin defectos'),
            (2,'Comercial','Características comerciales aceptables'),
            (3,'Descarte','Defectos impiden su comercialización')");
        // tamanos_plantas
        $this->pdo->exec("INSERT IGNORE INTO tamanos_plantas (id, codigo, nombre, altura_min_cm, altura_max_cm, descripcion) VALUES
            (1,'P','Pequeña',20,40,'Pequeña 20-40 cm'),
            (2,'M','Mediana',40,80,'Mediana 40-80 cm'),
            (3,'G','Grande',80,120,'Grande 80-120 cm'),
            (4,'XG','Extra Grande',120,999,'Más de 120 cm')");
        // tipos_tratamiento
        $this->pdo->exec("INSERT IGNORE INTO tipos_tratamiento (id, nombre) VALUES
            (1,'Fertilización'),(2,'Fitosanitario'),(3,'Poda'),(4,'Mantenimiento')");
        // causas_perdidas
        $this->pdo->exec("INSERT IGNORE INTO causas_perdidas (id, descripcion) VALUES
            (1,'Plagas'),(2,'Enfermedades'),(3,'Falta de riego'),(4,'Exceso de riego'),(5,'Daños mecánicos'),(6,'Estrés térmico')");
        // motivos_descartes
        $this->pdo->exec("INSERT IGNORE INTO motivos_descartes (id, descripcion) VALUES
            (1,'Defecto estructural'),(2,'Crecimiento insuficiente'),(3,'Daño irreversible')");
        // roles
        $this->pdo->exec("INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
            (1,'Admin','Administrador del sistema'),(2,'Técnico','Técnico de vivero')");
        // usuarios: crear admin si no existe
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute(['admin']);
        if (!$stmt->fetch()) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (username, password_hash, nombre, rol_id, email) VALUES (?,?,?,?,?)");
            $stmt->execute(['admin', $hash, 'Administrador', 1, 'admin@example.com']);
        }
        // ubicaciones por defecto (removido en seedMinimal para no generar datos ficticios)
    }

    public function getCatalogs(array $tables): array {
        $catalogs = [];
        foreach ($tables as $t) {
            $stmt = $this->pdo->query("SELECT * FROM $t ORDER BY 1");
            $catalogs[$t] = $stmt->fetchAll();
        }
        return $catalogs;
    }
}
