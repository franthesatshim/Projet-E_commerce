<?php
declare(strict_types=1);

/** Appeler dans la transaction qui enregistre l'encaissement de la commande. */
function create_paid_invoice(PDO $db, int $orderId): void
{
    if (!$db->inTransaction()) {
        throw new LogicException('La facture doit être émise dans la transaction du paiement.');
    }

    $insert = $db->prepare(<<<'SQL'
        INSERT INTO invoices (order_id, customer_name, customer_email, delivery_address,
                              total, paid_amount, paid_currency, payment_method, payment_reference)
        SELECT o.id, u.name, u.email, o.address, o.total, COALESCE(a.amount, o.total),
               COALESCE(a.currency, 'USD'), o.payment_method, o.payment_reference
        FROM orders o JOIN users u ON u.id = o.user_id
        LEFT JOIN payment_attempts a ON a.merchant_ref = o.payment_reference
          AND a.order_id = o.id AND a.status = 'completed'
        LEFT JOIN invoices i ON i.order_id = o.id
        WHERE o.id = ? AND o.payment_status = 'paye' AND i.order_id IS NULL
        ON DUPLICATE KEY UPDATE order_id = invoices.order_id
        SQL);
    $insert->execute([$orderId]);
    if ($insert->rowCount() === 0) {
        return;
    }

    $invoiceId = $db->lastInsertId();
    $lines = $db->prepare(<<<'SQL'
        INSERT INTO invoice_items (invoice_id, line_number, product_name, quantity, unit_price)
        SELECT ?, oi.id, p.name, oi.quantity, oi.unit_price
        FROM order_items oi JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
        SQL);
    $lines->execute([$invoiceId, $orderId]);
}
