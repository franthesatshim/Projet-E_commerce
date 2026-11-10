<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
requireMethod('GET');

try {
    $statement = getPDO()->query(
        'SELECT c.id, c.name, c.slug, COUNT(p.id) AS products_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
         GROUP BY c.id, c.name, c.slug
         ORDER BY c.name ASC'
    );

    jsonResponse(['success' => true, 'data' => $statement->fetchAll()]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'Impossible de charger les catégories.'], 500);
}
