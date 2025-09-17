<?php
require_once __DIR__ . '/TratamientoRepository.php';

class TratamientoService {
    private TratamientoRepository $repo;

    public function __construct(TratamientoRepository $repo) { $this->repo = $repo; }

    public function listByLoteFase(int $lote_fase_id): array { return $this->repo->listByLoteFase($lote_fase_id); }

    public function create(array $data): int {
        if (!isset($data['lote_fase_id'], $data['tipo_tratamiento_id'], $data['usuario_id'], $data['fecha'])) {
            throw new InvalidArgumentException('Campos requeridos: lote_fase_id, tipo_tratamiento_id, usuario_id, fecha');
        }
        return $this->repo->insert($data);
    }
}
