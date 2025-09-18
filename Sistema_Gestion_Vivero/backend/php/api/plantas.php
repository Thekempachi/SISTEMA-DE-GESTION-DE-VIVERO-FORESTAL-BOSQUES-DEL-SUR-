<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/PlantasController.php';
    PlantasController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("PlantasController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/plantas_simple.php';
}

