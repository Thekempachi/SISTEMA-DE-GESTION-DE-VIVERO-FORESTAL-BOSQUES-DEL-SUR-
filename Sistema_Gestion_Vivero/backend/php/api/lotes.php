<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/LotesController.php';
    LotesController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("LotesController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/lotes_simple.php';
}
