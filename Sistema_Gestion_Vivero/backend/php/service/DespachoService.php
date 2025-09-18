<?php
require_once __DIR__ . '/DespachoRepository.php';

class DespachoService {
    private DespachoRepository $repo;

    public function __construct(DespachoRepository $repo) { $this->repo = $repo; }

    public function list(): array { return $this->repo->list(); }

    public function createOrder(array $data): array {
        if (!isset($data['destino_id'], $data['fecha'], $data['responsable_despacho_id'])) {
            throw new InvalidArgumentException('Campos requeridos: destino_id, fecha, responsable_despacho_id');
        }
        return $this->repo->createOrder($data);
    }

    public function addLine(array $data): int {
        if (!isset($data['orden_despacho_id'], $data['planta_id'], $data['cantidad'], $data['estado_al_despacho_id'])) {
            throw new InvalidArgumentException('Campos requeridos: orden_despacho_id, planta_id, cantidad, estado_al_despacho_id');
        }
        return $this->repo->addLine($data);
    }
}
