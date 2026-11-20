<?php
$s = $db->prepare('SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.active=1');
$s->execute([(int)($_GET['id'] ?? 0)]);
$p = $s->fetch();
if (!$p):
    http_response_code(404);
    $title = 'Produit introuvable — DEUS COM NOS';
    ?>
    <div class="catalog-empty"><h3>Produit introuvable</h3><p>Cet article n'existe pas ou n'est plus en vente.</p><a class="btn" href="?page=shop">Retour à la collection</a></div>
    <?php return; endif;

$title = $p['name'] . ' — DEUS COM NOS';
$stock = (int)$p['stock'];
$img   = img_url($p['image'] ?? '');

$rel = $db->prepare('SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id
                     WHERE p.active=1 AND p.category_id=? AND p.id<>? ORDER BY p.created_at DESC, p.id DESC LIMIT 4');
$rel->execute([(int)$p['category_id'], (int)$p['id']]);
$related = $p['category_id'] ? $rel->fetchAll() : [];
?>
<nav class="crumbs" aria-label="Fil d'Ariane">
    <a href="index.php">Accueil</a> <span>/</span> <a href="?page=shop">Collection</a>
    <?php if ($p['category_id']): ?><span>/</span> <a href="?page=shop&amp;category=<?= (int)$p['category_id'] ?>"><?= e($p['category']) ?></a><?php endif ?>
    <span>/</span> <strong><?= e($p['name']) ?></strong>
</nav>

<div class="product-detail">
    <div class="product-gallery" data-scroll-reveal>
        <?php if ($img): ?>
            <img src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
            <div class="placeholder" hidden>Photo indisponible</div>
        <?php else: ?>
            <div class="placeholder">Photo à venir</div>
        <?php endif ?>
    </div>
    <div class="product-info">
        <?php if ($p['category']): ?><span class="catalog-category"><?= e($p['category']) ?></span><?php endif ?>
        <h1 data-scroll-reveal><?= e($p['name']) ?></h1>
        <p class="price product-price"><?= money($p['price']) ?></p>
        <?php if ($stock < 1): ?>
            <p class="product-stock out-stock">Épuisé pour le moment</p>
        <?php elseif ($stock <= 3): ?>
            <p class="product-stock low-stock">Plus que <?= $stock ?> en stock</p>
        <?php else: ?>
            <p class="product-stock in-stock">Disponible</p>
        <?php endif ?>
        <p class="product-desc"><?= nl2br(e($p['description'])) ?></p>
        <form method="post" class="product-buy">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <label>Quantité<input class="field" type="number" name="qty" min="1" max="<?= max(1, min(20, $stock)) ?>" value="1"></label>
            <button <?= $stock < 1 ? 'disabled' : '' ?>>Ajouter au panier</button>
        </form>
        <ul class="product-perks">
            <li>Paiement à la livraison, rien à payer en ligne</li>
            <li>Livraison à l'adresse de votre choix</li>
        </ul>
    </div>
</div>

<?php if ($related): ?>
    <div class="section-title" data-scroll-reveal><h2>Vous aimerez aussi</h2><a href="?page=shop&amp;category=<?= (int)$p['category_id'] ?>">Voir la catégorie →</a></div>
    <div class="grid catalog-grid home-grid">
        <?php foreach ($related as $r) render_card($r); ?>
    </div>
<?php endif ?>
