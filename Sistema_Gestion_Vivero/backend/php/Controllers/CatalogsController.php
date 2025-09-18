<?php
require_once __DIR__ . '/../conection.php';
require_once __DIR__ . '/../repository/CatalogRepository.php';
require_once __DIR__ . '/../service/CatalogService.php';

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
            if ($e instanceof PDOException) {
                $debug = getenv('APP_DEBUG') === '1';
                $msg = $debug ? ('DB: ' . $e->getMessage()) : 'Error de base de datos';
                send_json(['error' => $msg], 500);
            } else {
                send_json(['error' => $e->getMessage()], 500);
            }
        }
    }
}
