<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/CatalogsController.php';
    CatalogsController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("CatalogsController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/catalogs_simple.php';
}

