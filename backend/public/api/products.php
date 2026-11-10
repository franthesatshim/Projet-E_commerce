<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';
requireMethod('GET');

try {
    $pdo = getPDO();
    $category = trim((string)($_GET['category'] ?? ''));
    $search = trim((string)($_GET['q'] ?? ''));

    $sql = 'SELECT p.id, p.name, p.description,
                   p.price_usd, p.price_cdf, p.stock, p.image_path,
                   c.name AS category, c.slug AS category_slug
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.is_active = 1';
    $params = [];

    if ($category !== '') {
        $sql .= ' AND c.slug = :category';
        $params['category'] = $category;
    }
    if ($search !== '') {
        $sql .= ' AND (p.name LIKE :search OR p.description LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY p.created_at DESC, p.id DESC';
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    jsonResponse([
        'success' => true,
        'supported_currencies' => ['USD', 'CDF'],
        'data' => $statement->fetchAll(),
    ]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'Impossible de charger les produits.'], 500);
}
