<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/EspeciesController.php';
    EspeciesController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("EspeciesController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/especies_simple.php';
}
