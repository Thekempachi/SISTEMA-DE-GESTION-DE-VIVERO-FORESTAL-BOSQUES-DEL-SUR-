<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/FasesController.php';
    FasesController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("FasesController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/fases_simple.php';
}
