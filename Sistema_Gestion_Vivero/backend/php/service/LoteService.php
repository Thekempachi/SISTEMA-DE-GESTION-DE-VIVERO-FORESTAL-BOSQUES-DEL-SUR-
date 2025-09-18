<?php
require_once __DIR__ . '/LoteRepository.php';

class LoteService {
    private LoteRepository $repo;
    private PDO $pdo;

    public function __construct(LoteRepository $repo, PDO $pdo) {
        $this->repo = $repo;
        $this->pdo = $pdo;
    }

    public function list(): array { return $this->repo->list(); }

    public function detail(int $id): ?array { return $this->repo->detail($id); }

    public function generateCodigo(): string {
        $base = 'LP-' . date('Ymd') . '-';
        do {
            $code = $base . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while ($this->repo->isCodigoUsed($code));
        return $code;
    }

    public function createAll(array $data): array {
        $this->pdo->beginTransaction();
        try {
            $proveedor_id = $data['proveedor_id'] ?? null;
            if (!$proveedor_id) {
                if (!isset($data['proveedor']['nombre'])) {
                    throw new InvalidArgumentException('Campo requerido: proveedor.nombre');
                }
                $proveedor_id = $this->repo->insertProveedor($data['proveedor']);
            }

            $ls = $data['lote_semillas'] ?? [];
            $lote_semillas_id = $this->repo->insertLoteSemillas($proveedor_id, $ls);

            if (!isset($data['lote_produccion']['especie_id'], $data['lote_produccion']['fecha_siembra'], $data['lote_produccion']['cantidad_semillas_usadas'])) {
                throw new InvalidArgumentException('Campos requeridos: lote_produccion.especie_id, fecha_siembra, cantidad_semillas_usadas');
            }
            $lp = $data['lote_produccion'];
            $codigo = $lp['codigo'] ?? $this->generateCodigo();
            $lote_produccion_id = $this->repo->insertLoteProduccion($lp, $lote_semillas_id, $codigo);

            $this->pdo->commit();
            return ['lote_produccion_id' => $lote_produccion_id, 'codigo' => $codigo];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
