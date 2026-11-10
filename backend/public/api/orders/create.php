<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/bootstrap.php';
requireMethod('POST');
$user = requireAuth();

$data = readJsonBody();
$items = $data['items'] ?? [];
$shippingAddress = trim((string)($data['shipping_address'] ?? ''));
$currency = strtoupper(trim((string)($data['currency'] ?? 'USD')));

if (!in_array($currency, ['USD', 'CDF'], true)) {
    jsonResponse(['success' => false, 'message' => 'Devise non prise en charge. Choisissez USD ou CDF.'], 422);
}
if (!is_array($items) || count($items) === 0 || $shippingAddress === '') {
    jsonResponse(['success' => false, 'message' => 'Le panier et l’adresse de livraison sont obligatoires.'], 422);
}
if (count($items) > 50) {
    jsonResponse(['success' => false, 'message' => 'Le panier contient trop de lignes.'], 422);
}

$quantities = [];
foreach ($items as $item) {
    if (!is_array($item)) {
        jsonResponse(['success' => false, 'message' => 'Format de produit invalide.'], 422);
    }
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

    // Le nom de colonne est choisi exclusivement parmi ces deux valeurs autorisées.
    $priceColumn = $currency === 'CDF' ? 'price_cdf' : 'price_usd';
    $findProduct = $pdo->prepare(
        "SELECT id, name, {$priceColumn} AS selected_price, stock
         FROM products WHERE id = :id AND is_active = 1 FOR UPDATE"
    );
    $insertOrder = $pdo->prepare(
        'INSERT INTO orders (user_id, currency, total_amount, shipping_address, status)
         VALUES (:user_id, :currency, :total, :address, "pending")'
    );
    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity)
         VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity)'
    );
    $updateStock = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE id = :id');

    $lines = [];
    $totalMinor = 0;
    foreach ($quantities as $productId => $quantity) {
        $findProduct->execute(['id' => $productId]);
        $product = $findProduct->fetch();

        if (!$product || (int)$product['stock'] < $quantity) {
            throw new DomainException('Stock insuffisant ou produit indisponible : ' . $productId);
        }
        if ($product['selected_price'] === null) {
            throw new DomainException('Le prix ' . $currency . ' de ce produit n’est pas encore configuré.');
        }

        $priceString = (string)$product['selected_price'];
        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $priceString)) {
            throw new RuntimeException('Prix enregistré invalide.');
        }
        [$whole, $fraction] = array_pad(explode('.', $priceString, 2), 2, '0');
        $minor = ((int)$whole * 100) + (int)str_pad($fraction, 2, '0');
        $totalMinor += $minor * $quantity;

        $lines[] = [
            'product' => $product,
            'quantity' => $quantity,
            'price' => number_format($minor / 100, 2, '.', ''),
        ];
    }

    $total = number_format($totalMinor / 100, 2, '.', '');
    $insertOrder->execute([
        'user_id' => $user['id'],
        'currency' => $currency,
        'total' => $total,
        'address' => $shippingAddress,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    foreach ($lines as $line) {
        $insertItem->execute([
            'order_id' => $orderId,
            'product_id' => $line['product']['id'],
            'product_name' => $line['product']['name'],
            'unit_price' => $line['price'],
            'quantity' => $line['quantity'],
        ]);
        $updateStock->execute([
            'quantity' => $line['quantity'],
            'id' => $line['product']['id'],
        ]);
    }

    $pdo->commit();
    jsonResponse([
        'success' => true,
        'message' => 'Commande enregistrée.',
        'data' => [
            'order_id' => $orderId,
            'total_amount' => $total,
            'currency' => $currency,
        ],
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
