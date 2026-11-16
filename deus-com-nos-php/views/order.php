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
        <p>Mode de paiement : <?= $o['payment_method'] === 'paiement_livraison' ? 'Paiement à la livraison' : e($o['payment_method'] ?? 'Non précisé') ?></p>
        <p>Paiement : <?= $o['payment_status'] === 'simule' ? 'Non encaissé (ancien test)' : 'À régler à la livraison' ?></p>
        <p>Référence : <?= e($o['payment_reference']) ?></p>
        <?php if (!empty($o['phone'])): ?><p>Téléphone : <?= e($o['phone']) ?></p><?php endif ?>
        <p>Livraison : <?= nl2br(e($o['address'])) ?></p>
        <h2>Total : <?= money($o['total']) ?></h2>
    </aside>
</div>
