<?php
require_once __DIR__ . '/../repository/CondicionRepository.php';

class CondicionService {
    private CondicionRepository $repo;

    public function __construct(CondicionRepository $repo) { $this->repo = $repo; }

    public function listByLoteFase(int $lote_fase_id): array { return $this->repo->listByLoteFase($lote_fase_id); }

    public function create(array $data): int {
        if (!isset($data['lote_fase_id'], $data['fecha'])) {
            throw new InvalidArgumentException('Campos requeridos: lote_fase_id, fecha');
        }
        return $this->repo->insert($data);
    }
}
