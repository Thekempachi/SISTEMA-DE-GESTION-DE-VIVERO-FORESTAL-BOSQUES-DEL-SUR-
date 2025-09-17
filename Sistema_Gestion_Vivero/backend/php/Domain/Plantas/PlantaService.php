<?php
require_once __DIR__ . '/PlantaRepository.php';

class PlantaService {
    private PlantaRepository $repo;

    public function __construct(PlantaRepository $repo) { $this->repo = $repo; }

    public function getById(int $id): ?array { return $this->repo->getById($id); }

    public function list(?int $lote_produccion_id = null): array { return $this->repo->list($lote_produccion_id); }

    public function create(array $data): array {
        if (!isset($data['lote_produccion_id'], $data['estado_salud_id'])) {
            throw new InvalidArgumentException('Campos requeridos: lote_produccion_id, estado_salud_id');
        }
        $codigo_qr = $data['codigo_qr'] ?? null;
        return $this->repo->create($data, $codigo_qr);
    }

    public function update(int $id, array $data): void { $this->repo->update($id, $data); }
}
