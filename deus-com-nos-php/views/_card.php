<?php
/** Carte produit utilisée par l'accueil, la collection et les « produits similaires ». */
function render_card(array $p): void {
    $img   = img_url($p['image'] ?? '');
    $stock = (int)$p['stock'];
    $cat   = $p['category'] ?? null;
    ?>
    <article class="product catalog-card">
        <a class="catalog-photo" href="?page=product&amp;id=<?= (int)$p['id'] ?>" aria-label="Voir <?= e($p['name']) ?>">
            <?php if ($img): ?>
                <img loading="lazy" src="<?= e($img) ?>" alt="<?= e($p['name']) ?>" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
                <span class="photo-fallback" hidden>Photo indisponible</span>
            <?php else: ?>
                <span class="photo-fallback">Photo à venir</span>
            <?php endif ?>
            <span class="catalog-photo-label"><?= e($cat ?? 'Mode') ?></span>
        </a>
        <div class="catalog-card-body">
            <span class="catalog-category"><?= e($cat ?? 'Boutique') ?></span>
            <a class="catalog-name" href="?page=product&amp;id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a>
            <div class="catalog-card-bottom">
                <span class="price"><?= money($p['price']) ?></span>
                <?php if ($stock < 1): ?>
                    <span class="catalog-stock out-stock">Épuisé</span>
                <?php elseif ($stock <= 3): ?>
                    <span class="catalog-stock low-stock">Plus que <?= $stock ?></span>
                <?php else: ?>
                    <span class="catalog-stock in-stock">Disponible</span>
                <?php endif ?>
            </div>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button <?= $stock < 1 ? 'disabled' : '' ?>><?= $stock < 1 ? 'Rupture de stock' : 'Ajouter au panier' ?></button>
            </form>
        </div>
    </article>
    <?php
}
