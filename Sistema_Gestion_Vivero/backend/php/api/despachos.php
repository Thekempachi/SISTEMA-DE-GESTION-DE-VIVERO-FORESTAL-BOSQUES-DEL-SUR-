<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/DespachosController.php';
    DespachosController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("DespachosController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/despachos_simple.php';
}

