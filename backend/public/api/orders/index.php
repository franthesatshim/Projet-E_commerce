<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/bootstrap.php';
requireMethod('GET');
$user = requireAuth();

try {
    $statement = getPDO()->prepare(
        'SELECT
            o.id AS order_id,
            o.total_amount,
            o.shipping_address,
            o.status,
            o.created_at,
            oi.id AS item_id,
            oi.product_id,
            oi.product_name,
            oi.unit_price,
            oi.quantity
         FROM orders o
         LEFT JOIN order_items oi ON oi.order_id = o.id
         WHERE o.user_id = :user_id
         ORDER BY o.created_at DESC, o.id DESC, oi.id ASC'
    );
    $statement->execute(['user_id' => $user['id']]);

    $orders = [];
    foreach ($statement->fetchAll() as $row) {
        $orderId = (int)$row['order_id'];

        if (!isset($orders[$orderId])) {
            $orders[$orderId] = [
                'id' => $orderId,
                'total_amount' => $row['total_amount'],
                'shipping_address' => $row['shipping_address'],
                'status' => $row['status'],
                'created_at' => $row['created_at'],
                'items' => [],
            ];
        }

        if ($row['item_id'] !== null) {
            $orders[$orderId]['items'][] = [
                'id' => (int)$row['item_id'],
                'product_id' => $row['product_id'] !== null ? (int)$row['product_id'] : null,
                'product_name' => $row['product_name'],
                'unit_price' => $row['unit_price'],
                'quantity' => (int)$row['quantity'],
            ];
        }
    }

    jsonResponse(['success' => true, 'data' => array_values($orders)]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'Impossible de récupérer vos commandes.'], 500);
}
