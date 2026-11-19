<?php
declare(strict_types=1);

require __DIR__ . '/config.php';

if (!user()) {
    redirect('index.php?page=login');
}
header('Cache-Control: private, no-store');

$orderId = (int)($_GET['order_id'] ?? 0);
$query = $db->prepare("SELECT i.* FROM invoices i JOIN orders o ON o.id = i.order_id
                       WHERE i.order_id = ? AND o.user_id = ? AND o.payment_status = 'paye'");
$query->execute([$orderId, user()['id']]);
$invoice = $query->fetch();
if (!$invoice) {
    http_response_code(404);
    exit('Facture introuvable.');
}

$number = 'DCN-' . str_pad((string)$invoice['id'], 6, '0', STR_PAD_LEFT);
$items = $db->prepare('SELECT product_name, quantity, unit_price, quantity * unit_price AS total
                       FROM invoice_items WHERE invoice_id = ? ORDER BY line_number');
$items->execute([$invoice['id']]);
$method = match ($invoice['payment_method']) {
    'netikash' => 'Netikash',
    'shwary' => 'Shwary',
    'paiement_livraison' => 'À la livraison',
    default => $invoice['payment_method'],
};
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facture <?= e($number) ?> · DEUS COM NOS</title>
    <link rel="stylesheet" href="invoice.css">
</head>
<body>
    <main class="invoice">
        <nav class="invoice-actions" aria-label="Actions de la facture">
            <a href="index.php?page=order&amp;id=<?= $orderId ?>">← Retour à la commande</a>
            <button type="button" onclick="window.print()">Imprimer ou enregistrer en PDF</button>
        </nav>

        <header class="invoice-heading">
            <p class="invoice-brand">DEUS COM NOS</p>
            <h1>Facture <?= e($number) ?></h1>
            <p>Émise le <?= e(date('d/m/Y', strtotime($invoice['issued_at']))) ?> · Commande n° <?= $orderId ?></p>
        </header>

        <div class="invoice-details">
            <section>
                <h2>Client</h2>
                <p><strong><?= e($invoice['customer_name']) ?></strong></p>
                <p><?= e($invoice['customer_email']) ?></p>
                <p><?= nl2br(e($invoice['delivery_address'])) ?></p>
            </section>
            <section>
                <h2>Paiement</h2>
                <p><strong>Encaissé</strong></p>
                <?php if ($method): ?><p><?= e($method) ?></p><?php endif; ?>
                <?php if ($invoice['payment_reference']): ?>
                    <p>Réf. <?= e($invoice['payment_reference']) ?></p>
                <?php endif; ?>
            </section>
        </div>

        <table>
            <thead>
                <tr>
                    <th scope="col">Article</th>
                    <th scope="col">Qté</th>
                    <th scope="col">Prix unitaire</th>
                    <th scope="col">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_name']) ?></td>
                        <td><?= (int)$item['quantity'] ?></td>
                        <td><?= money($item['unit_price']) ?></td>
                        <td><?= money($item['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th scope="row" colspan="3">Total de la commande (USD)</th>
                    <td><?= money($invoice['total']) ?></td>
                </tr>
                <?php if ($invoice['paid_currency'] !== 'USD'): ?>
                    <tr>
                        <th scope="row" colspan="3">Montant encaissé</th>
                        <td><?= e(number_format((float)$invoice['paid_amount'], 2, ',', ' ') . ' ' . $invoice['paid_currency']) ?></td>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </main>
</body>
</html>
