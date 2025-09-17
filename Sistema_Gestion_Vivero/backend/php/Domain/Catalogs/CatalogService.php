<?php
require_once __DIR__ . '/CatalogRepository.php';

class CatalogService {
    private CatalogRepository $repo;

    public function __construct(CatalogRepository $repo) { $this->repo = $repo; }

    public function seedIfRequested(string $seedFlag): void {
        if ($seedFlag === '1') {
            // Minimal seed: solo roles y usuario admin
            $this->repo->seedMinimal();
        }
    }

    public function getAll(): array {
        $tables = [
            'tipo_especie','estado_salud','tipo_destino','estado_fase','fases_produccion',
            'clasificaciones_calidad','tamanos_plantas','tipos_tratamiento','causas_perdidas','motivos_descartes','ubicaciones'
        ];
        return $this->repo->getCatalogs($tables);
    }
}
