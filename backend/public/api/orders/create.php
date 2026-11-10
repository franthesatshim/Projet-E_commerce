<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/bootstrap.php';
requireMethod('POST');
$user = requireAuth();

$data = readJsonBody();
$items = $data['items'] ?? [];
$shippingAddress = trim((string)($data['shipping_address'] ?? ''));

if (!is_array($items) || count($items) === 0 || $shippingAddress === '') {
    jsonResponse(['success' => false, 'message' => 'Le panier et l’adresse de livraison sont obligatoires.'], 422);
}
if (count($items) > 50) {
    jsonResponse(['success' => false, 'message' => 'Le panier contient trop de lignes.'], 422);
}

$quantities = [];
foreach ($items as $item) {
    $productId = filter_var($item['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
    if (!$productId || !$quantity || $quantity < 1 || $quantity > 100) {
        jsonResponse(['success' => false, 'message' => 'Un produit ou une quantité est invalide.'], 422);
    }
    $quantities[$productId] = ($quantities[$productId] ?? 0) + $quantity;
}

$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $findProduct = $pdo->prepare(
        'SELECT id, name, price, stock FROM products WHERE id = :id AND is_active = 1 FOR UPDATE'
    );
    $insertOrder = $pdo->prepare(
        'INSERT INTO orders (user_id, total_amount, shipping_address, status)
         VALUES (:user_id, :total, :address, "pending")'
    );
    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity)
         VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity)'
    );
    $updateStock = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id');

    $lines = [];
    $total = 0.0;
    foreach ($quantities as $productId => $quantity) {
        $findProduct->execute(['id' => $productId]);
        $product = $findProduct->fetch();
        if (!$product || (int)$product['stock'] < $quantity) {
            throw new DomainException('Stock insuffisant ou produit indisponible : ' . $productId);
        }
        $price = (float)$product['price'];
        $total += $price * $quantity;
        $lines[] = ['product' => $product, 'quantity' => $quantity, 'price' => $price];
    }

    $insertOrder->execute([
        'user_id' => $user['id'],
        'total' => number_format($total, 2, '.', ''),
        'address' => $shippingAddress,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    foreach ($lines as $line) {
        $insertItem->execute([
            'order_id' => $orderId,
            'product_id' => $line['product']['id'],
            'product_name' => $line['product']['name'],
            'unit_price' => number_format($line['price'], 2, '.', ''),
            'quantity' => $line['quantity'],
        ]);
        $updateStock->execute(['quantity' => $line['quantity'], 'id' => $line['product']['id']]);
    }

    $pdo->commit();
    jsonResponse([
        'success' => true,
        'message' => 'Commande enregistrée.',
        'data' => ['order_id' => $orderId, 'total_amount' => number_format($total, 2, '.', ''), 'currency' => 'EUR']
    ], 201);
} catch (DomainException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(['success' => false, 'message' => $exception->getMessage()], 409);
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'Impossible d’enregistrer la commande.'], 500);
}
