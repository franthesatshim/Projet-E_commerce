<?php
$s = $db->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$s->execute([(int)($_GET['id'] ?? 0), user()['id']]);
$o = $s->fetch();
if (!$o):
    http_response_code(404);
    $title = 'Commande introuvable — DEUS COM NOS';
    ?>
    <div class="catalog-empty"><h3>Commande introuvable</h3><a class="btn" href="?page=orders">Mes commandes</a></div>
    <?php return; endif;

$attempt = null;
if ($o['payment_method'] === 'netikash') {
    $pa = $db->prepare('SELECT * FROM payment_attempts WHERE order_id=? ORDER BY id DESC LIMIT 1');
    $pa->execute([(int)$o['id']]);
    $attempt = $pa->fetch() ?: null;
}
$title = 'Commande #' . $o['id'] . ' — DEUS COM NOS';
$items = $db->prepare('SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id=?');
$items->execute([$o['id']]);
?>
<p><a href="?page=orders">← Mes commandes</a></p>
<h1>Commande #<?= (int)$o['id'] ?></h1>
<div class="layout">
    <section class="panel">
        <h2>Articles commandés</h2>
        <?php foreach ($items as $it): $img = img_url($it['image'] ?? ''); ?>
            <div class="cart-line">
                <span class="cart-thumb"><?php if ($img): ?><img src="<?= e($img) ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif ?></span>
                <div class="cart-info">
                    <strong><?= e($it['name']) ?></strong>
                    <p>Quantité : <?= (int)$it['quantity'] ?> · Prix unitaire : <?= money($it['unit_price']) ?></p>
                </div>
                <strong><?= money($it['unit_price'] * $it['quantity']) ?></strong>
            </div>
        <?php endforeach ?>
    </section>
    <aside class="panel">
        <h2>Récapitulatif</h2>
        <p>Statut : <span class="badge badge-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></p>
        <p>Date : <?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></p>
        <p>Mode de paiement : <?= $o['payment_method'] === 'paiement_livraison' ? 'À la livraison' : ($o['payment_method'] === 'netikash' ? 'Netikash (en ligne)' : e($o['payment_method'] ?? 'Non précisé')) ?></p>
        <p>Paiement : <strong><?= e(payment_label($o['payment_status'])) ?></strong></p>
        <p>Référence : <?= e($o['payment_reference']) ?></p>
        <?php if ($o['payment_method'] === 'netikash'): ?>
            <?php if ($o['payment_status'] === 'paye'): ?>
                <p class="message">Paiement encaissé et vérifié par Netikash. Merci pour votre commande.</p>
            <?php elseif ($o['payment_status'] === 'autorise'): ?>
                <p class="message">Paiement confirmé. En attente de son règlement final chez Netikash avant expédition.</p>
            <?php elseif ($o['status'] !== 'annulee'): ?>
                <?php if ($attempt && $attempt['payment_url'] && $attempt['status'] !== 'completed' && in_array($attempt['status'], ['requested','approved'], true)): ?>
                    <p><a class="btn" href="<?= e($attempt['payment_url']) ?>" rel="noopener noreferrer">Ouvrir le paiement Netikash ↗</a></p>
                <?php endif; ?>
                <?php if ($attempt && !empty($attempt['provider_id'])): ?>
                    <form method="post" class="payment-action">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="netikash_sync">
                        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <button type="submit" class="outline">Vérifier l'état du paiement</button>
                    </form>
                <?php endif; ?>
                <?php if (!$attempt || in_array($attempt['status'], ['failed','expired','canceled'], true)): ?>
                    <form method="post" class="payment-action">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="netikash_start">
                        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <button type="submit">Payer avec Netikash</button>
                    </form>
                <?php elseif (!$attempt['payment_url']): ?>
                    <p class="message">Statut d'initialisation incertain. Ne refaites pas le paiement ; contactez la boutique pour vérification.</p>
                <?php endif; ?>
                <p class="muted">Le retour sur cette page ne prouve pas un paiement. L'état est mis à jour uniquement après vérification côté serveur.</p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($o['phone'])): ?><p>Téléphone : <?= e($o['phone']) ?></p><?php endif ?>
        <p>Livraison : <?= nl2br(e($o['address'])) ?></p>
        <h2>Total : <?= money($o['total']) ?></h2>
    </aside>
</div>
