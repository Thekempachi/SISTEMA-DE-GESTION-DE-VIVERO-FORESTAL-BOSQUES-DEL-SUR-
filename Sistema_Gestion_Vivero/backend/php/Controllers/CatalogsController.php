<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../Domain/Catalogs/CatalogRepository.php';
require_once __DIR__ . '/../Domain/Catalogs/CatalogService.php';

class CatalogsController {
    public static function handle(): void {
        try {
            $pdo = db();
            $service = new CatalogService(new CatalogRepository($pdo));

            // Optional seed if requested
            $seed = $_GET['seed'] ?? '';
            $service->seedIfRequested($seed);

            $catalogs = $service->getAll();
            send_json(['ok' => true, 'catalogs' => $catalogs]);
        } catch (Throwable $e) {
            send_json(['error' => $e->getMessage()], 500);
        }
    }
}
