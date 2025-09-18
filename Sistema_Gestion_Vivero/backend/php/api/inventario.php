<?php
// Intentar usar el controlador real, si falla usar API simple
try {
    require_once __DIR__ . '/../controllers/InventarioController.php';
    InventarioController::handle();
} catch (Throwable $e) {
    // Si hay error, usar API simple como fallback
    error_log("InventarioController failed, using simple API: " . $e->getMessage());
    include __DIR__ . '/inventario_simple.php';
}

