<?php
require_once __DIR__ . '/../repository/EspecieRepository.php';

class EspecieService {
    private EspecieRepository $repo;

    public function __construct(EspecieRepository $repo) { $this->repo = $repo; }

    public function getById(int $id): ?array { return $this->repo->getById($id); }

    public function list(string $q = ''): array { return $this->repo->list($q); }

    public function create(array $data): int { return $this->repo->create($data); }

    public function update(int $id, array $data): void { $this->repo->update($id, $data); }

    public function delete(int $id): void { $this->repo->delete($id); }
}
