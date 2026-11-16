<?php /** Données préparées par index.php : $cartItems, $cartTotal */
$title = 'Mon panier — DEUS COM NOS';
?>
<h1>Mon panier</h1>
<?php if (!$cartItems): ?>
    <div class="catalog-empty"><h3>Votre panier est vide</h3><p>Parcourez la collection et ajoutez vos coups de cœur.</p><a class="btn" href="?page=shop">Voir la collection</a></div>
<?php else: ?>
<div class="layout">
    <section class="panel">
        <?php foreach ($cartItems as $it): $p = $it['p']; $img = img_url($p['image'] ?? ''); ?>
            <div class="cart-line">
                <a class="cart-thumb" href="?page=product&amp;id=<?= (int)$p['id'] ?>" aria-hidden="true" tabindex="-1">
                    <?php if ($img): ?><img src="<?= e($img) ?>" alt="" loading="lazy" onerror="this.style.display='none'"><?php endif ?>
                </a>
                <div class="cart-info">
                    <a href="?page=product&amp;id=<?= (int)$p['id'] ?>"><strong><?= e($p['name']) ?></strong></a>
                    <p><?= money($p['price']) ?> × <?= $it['qty'] ?></p>
                    <p class="cart-line-total"><?= money($it['line']) ?></p>
                </div>
                <form method="post" class="quantity-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_cart">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <input type="number" name="qty" min="0" max="<?= (int)$p['stock'] ?>" value="<?= $it['qty'] ?>" aria-label="Quantité de <?= e($p['name']) ?>">
                    <button class="outline">Actualiser</button>
                </form>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="outline">Retirer</button>
                </form>
            </div>
        <?php endforeach ?>
    </section>
    <aside class="panel">
        <h2>Résumé de commande</h2>
        <h2>Total : <?= money($cartTotal) ?></h2>
        <?php if (user()): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="checkout">
                <label>Téléphone (pour la livraison)<input class="field" type="tel" name="phone" required minlength="8" maxlength="20" placeholder="+243 ..." autocomplete="tel"></label>
                <label>Adresse de livraison<textarea class="field" name="address" required minlength="8" maxlength="1000" rows="3" placeholder="Quartier, avenue, numéro, commune..." autocomplete="street-address"></textarea></label>
                <fieldset class="payment-selector">
                    <legend>Choisissez le paiement</legend>
                    <label class="payment-option"><input type="radio" name="payment_method" value="paiement_livraison" checked>
                        <span><strong>À la livraison</strong><small>Régler à la réception de vos articles.</small></span>
                    </label>
                    <?php require_once __DIR__ . '/../payments/netikash.php'; if (netikash_ready()): ?>
                    <label class="payment-option"><input type="radio" name="payment_method" value="netikash">
                        <span><strong>En ligne — Netikash</strong><small>Mobile Money et autres moyens proposés par Netikash sur sa page sécurisée. Montant : <?= money($cartTotal) ?> (USD).</small></span>
                    </label>
                    <?php else: ?>
                    <p class="muted">Le paiement en ligne sera disponible après activation du compte marchand Netikash et de l'adresse HTTPS.</p>
                    <?php endif; ?>
                </fieldset>
                <p class="muted">Ne communiquez jamais vos codes PIN ou mots de passe à la boutique.</p>
                <button>Confirmer ma commande</button>
            </form>
        <?php else: ?>
            <p class="message">Connectez-vous ou créez un compte pour valider votre commande. Votre panier sera conservé.</p>
            <a class="btn" href="?page=login">Se connecter</a>
            <a class="btn outline" href="?page=register">Créer un compte</a>
        <?php endif ?>
    </aside>
</div>
<?php endif ?>
