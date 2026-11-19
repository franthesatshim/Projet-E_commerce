<?php
$title = 'DEUS COM NOS — Chaussures, sacs, wax et perruques';

$latest = $db->query(
    'SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id
     WHERE p.active=1 ORDER BY p.created_at DESC, p.id DESC LIMIT 8'
)->fetchAll();
?>
<section class="editorial-hero" aria-labelledby="home-title" data-home-carousel>
    <div class="editorial-slides" aria-hidden="true">
        <div class="editorial-slide editorial-slide-look is-current" data-slide-name="Tenue beige sur des marches en pierre">
            <img class="editorial-photo" src="assets/hero/editorial-tenue.webp" width="1600" height="1067" alt="" fetchpriority="high">
        </div>
        <div class="editorial-slide editorial-slide-bag" data-slide-name="Détail d’un sac en cuir brun">
            <img class="editorial-photo" src="assets/hero/editorial-sac.webp" width="1600" height="1067" alt="" decoding="async">
        </div>
        <div class="editorial-slide editorial-slide-shoes" data-slide-name="Escarpins sur des marches en pierre">
            <img class="editorial-photo" src="assets/hero/editorial-talons.webp" width="1600" height="898" alt="" decoding="async">
        </div>
    </div>
    <div class="editorial-copy">
        <h1 id="home-title">L’allure vous<br><em>appartient.</em></h1>
        <p>Chaussures, sacs, tissus wax et perruques. Trouvez les pièces qui expriment votre style.</p>
        <a class="btn editorial-cta" href="?page=shop">Découvrir la collection <span class="link-arrow" aria-hidden="true"></span></a>
    </div>
    <div class="editorial-controls" data-carousel-controls hidden>
        <button type="button" data-carousel-previous aria-label="Image précédente">←</button>
        <span class="editorial-position" data-carousel-position aria-hidden="true">1 / 3</span>
        <button type="button" data-carousel-next aria-label="Image suivante">→</button>
        <button type="button" data-carousel-toggle>Pause</button>
    </div>
    <span class="editorial-status" data-carousel-status aria-live="polite"></span>
</section>

<?php if ($shopCategories): ?>
<section class="home-section" aria-labelledby="category-title">
    <div class="section-title section-title-centered">
        <h2 id="category-title">Explorer par catégorie</h2>
    </div>
    <div class="category-showcase">
        <?php foreach ($shopCategories as $category): $cover = img_url($category['cover']); ?>
            <a class="category-tile" href="?page=shop&amp;category=<?= (int)$category['id'] ?>">
                <span class="category-tile-photo">
                    <?php if ($cover): ?>
                    <img loading="lazy" decoding="async" src="<?= e($cover) ?>" alt="" onerror="this.hidden=true">
                    <?php endif ?>
                </span>
                <span class="category-tile-name"><?= e($category['name']) ?> <span class="link-arrow" aria-hidden="true"></span></span>
            </a>
        <?php endforeach ?>
    </div>
</section>
<?php endif ?>

<section class="home-section" aria-labelledby="latest-title">
    <div class="section-title">
        <h2 id="latest-title">Les dernières pièces</h2>
        <a href="?page=shop">Voir tout <span class="link-arrow" aria-hidden="true"></span></a>
    </div>
    <?php if ($latest): ?>
    <div class="grid catalog-grid home-grid">
        <?php foreach ($latest as $p) render_card($p); ?>
    </div>
    <?php else: ?>
    <div class="catalog-empty"><p>La collection arrive bientôt.</p></div>
    <?php endif ?>
</section>
