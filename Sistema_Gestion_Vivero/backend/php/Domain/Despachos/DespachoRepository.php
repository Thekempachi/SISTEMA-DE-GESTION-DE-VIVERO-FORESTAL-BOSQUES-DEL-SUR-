<?php
class DespachoRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function list(): array {
        $stmt = $this->pdo->query("SELECT od.*, d.nombre AS destino
            FROM ordenes_despacho od JOIN destinos d ON d.id = od.destino_id ORDER BY od.id DESC");
        $orders = $stmt->fetchAll();
        foreach ($orders as &$o) {
            $st = $this->pdo->prepare("SELECT l.*, p.codigo_qr, es.nombre_comun AS especie, es.id AS especie_id
                FROM ordenes_despacho_lineas l
                JOIN plantas p ON p.id = l.planta_id
                JOIN lotes_produccion lp ON lp.id = p.lote_produccion_id
                JOIN especies es ON es.id = lp.especie_id
                WHERE l.orden_despacho_id = ?");
            $st->execute([$o['id']]);
            $o['lineas'] = $st->fetchAll();
        }
        return $orders;
    }

    public function createOrder(array $data): array {
        $numero = $data['numero'] ?? ('OD-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6)));
        $stmt = $this->pdo->prepare("INSERT INTO ordenes_despacho (numero, destino_id, fecha, responsable_despacho_id, personal_transporte, notas) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$numero, $data['destino_id'], $data['fecha'], $data['responsable_despacho_id'], $data['personal_transporte'] ?? null, $data['notas'] ?? null]);
        return ['orden_despacho_id' => (int)$this->pdo->lastInsertId(), 'numero' => $numero];
    }

    public function addLine(array $data): int {
        $stmt = $this->pdo->prepare("INSERT INTO ordenes_despacho_lineas (orden_despacho_id, planta_id, cantidad, estado_al_despacho_id, observaciones) VALUES (?,?,?,?,?)");
        $stmt->execute([$data['orden_despacho_id'], $data['planta_id'], $data['cantidad'], $data['estado_al_despacho_id'], $data['observaciones'] ?? null]);
        return (int)$this->pdo->lastInsertId();
    }
}
