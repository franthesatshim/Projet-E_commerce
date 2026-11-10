<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
requireMethod('GET');

try {
    getPDO()->query('SELECT 1');
    jsonResponse([
        'success' => true,
        'message' => 'API disponible et connexion MySQL opérationnelle.',
        'database' => 'connected',
    ]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'API accessible, mais la connexion MySQL est indisponible.',
        'database' => 'disconnected',
    ], 503);
}
