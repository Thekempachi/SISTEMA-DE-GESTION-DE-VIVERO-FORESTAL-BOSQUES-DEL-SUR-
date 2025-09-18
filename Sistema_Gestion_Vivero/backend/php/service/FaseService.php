<?php
require_once __DIR__ . '/../repository/FaseRepository.php';

class FaseService {
    private FaseRepository $repo;
    private PDO $pdo;

    public function __construct(FaseRepository $repo, PDO $pdo) {
        $this->repo = $repo;
        $this->pdo = $pdo;
    }

    public function listByLote(int $lote_id): array { return $this->repo->listByLote($lote_id); }

    public function start(array $data): int {
        if (!isset($data['lote_produccion_id'], $data['fase_id'], $data['responsable_id'])) {
            throw new InvalidArgumentException('Campos requeridos: lote_produccion_id, fase_id, responsable_id');
        }
        return $this->repo->start($data);
    }

    public function close(array $data): int {
        if (!isset($data['lote_fase_id'], $data['responsable_id'], $data['plantas_avanzan'])) {
            throw new InvalidArgumentException('Campos requeridos: lote_fase_id, responsable_id, plantas_avanzan');
        }
        $this->pdo->beginTransaction();
        try {
            $res_id = $this->repo->close($data);
            $this->pdo->commit();
            return $res_id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function dailyReport(array $data): int {
        if (!isset($data['lote_fase_id'], $data['responsable_id'])) {
            throw new InvalidArgumentException('Campos requeridos: lote_fase_id, responsable_id');
        }
        return $this->repo->dailyReport($data);
    }
}
