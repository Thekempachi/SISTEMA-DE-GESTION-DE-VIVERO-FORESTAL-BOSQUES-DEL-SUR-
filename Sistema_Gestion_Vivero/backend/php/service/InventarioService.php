<?php
require_once __DIR__ . '/InventarioRepository.php';

class InventarioService {
    private InventarioRepository $repo;

    public function __construct(InventarioRepository $repo) { $this->repo = $repo; }

    public function list(): array { return $this->repo->list(); }

    public function upsert(array $data): void {
        $planta_id = (int)$data['planta_id'];
        $clas = isset($data['clasificacion_calidad_id']) ? (int)$data['clasificacion_calidad_id'] : null;
        $tam = isset($data['tamano_id']) ? (int)$data['tamano_id'] : null;
        $this->repo->upsert($planta_id, $clas, $tam);
    }
}
