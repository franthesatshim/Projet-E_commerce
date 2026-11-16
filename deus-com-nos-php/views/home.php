<?php
$title = 'DEUS COM NOS — Chaussures, sacs, wax et perruques';

// Photos du carrousel : uniquement des photos réelles de la boutique (dossier assets/products)
$slides = array_values(array_filter([
    ['assets/products/wax-vlisco-s6945.jpg',            'Tissu wax Vlisco aux motifs colorés'],
    ['assets/products/sac-portefeuille-assorti.jpg',    'Sacs à main avec portefeuilles assortis'],
    ['assets/products/mules-talon-bloc-r7601.jpg',      'Mules à talon bloc en plusieurs coloris'],
    ['assets/products/perruque-bob-ombre-caramel.jpg',  'Perruque bob lisse ombré caramel'],
], fn($s) => is_file(APP_DIR . '/' . $s[0])));

$latest = $db->query(
    'SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id
     WHERE p.active=1 ORDER BY p.created_at DESC, p.id DESC LIMIT 8'
)->fetchAll();
?>
<section class="hero">
    <div class="hero-copy" data-hero-copy>
        <span class="hero-kicker">NOUVELLE COLLECTION · DEUS COM NOS</span>
        <h1>Votre style.<br><em>Votre signature.</em></h1>
        <p>Chaussures, sacs, tissus wax et perruques. Une sélection pensée pour exprimer votre personnalité.</p>
        <div class="hero-actions"><a class="btn" href="?page=shop">Découvrir la collection <span aria-hidden="true">↗</span></a></div>
        <div class="hero-note"><span>✦</span> La mode à votre image</div>
    </div>
    <?php if ($slides): ?>
    <div class="hero-art hero-carousel" data-carousel aria-label="Photos de la collection DEUS COM NOS">
        <div class="hero-slides">
            <?php foreach ($slides as $i => $s): ?>
                <img class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" src="<?= e(img_url($s[0])) ?>" alt="<?= e($s[1]) ?>"<?= $i === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?>>
            <?php endforeach ?>
        </div>
        <div class="hero-carousel-controls">
            <button type="button" class="hero-arrow" data-carousel-prev aria-label="Photo précédente">‹</button>
            <div class="hero-dots" aria-label="Choisir une photo">
                <?php foreach ($slides as $i => $s): ?>
                    <button type="button" class="hero-dot<?= $i === 0 ? ' is-active' : '' ?>" data-carousel-dot="<?= $i ?>" aria-label="Photo <?= $i + 1 ?>"<?= $i === 0 ? ' aria-current="true"' : '' ?>></button>
                <?php endforeach ?>
            </div>
            <button type="button" class="hero-arrow" data-carousel-next aria-label="Photo suivante">›</button>
        </div>
        <div class="hero-photo-counter"><span data-carousel-count>01</span> / <?= sprintf('%02d', count($slides)) ?></div>
    </div>
    <?php endif ?>
</section>

<div class="perks">
    <div class="perk"><span>01</span> Une sélection tendance</div>
    <div class="perk"><span>02</span> Des styles variés</div>
    <div class="perk"><span>03</span> Paiement à la livraison</div>
    <div class="perk"><span>04</span> À votre écoute</div>
</div>

<?php if ($shopCategories): ?>
<div class="section-title"><h2>Explorez nos univers</h2><a href="?page=shop">Voir tout →</a></div>
<div class="category-showcase">
    <?php foreach ($shopCategories as $c): ?>
        <a class="category-tile" href="?page=shop&amp;category=<?= (int)$c['id'] ?>" aria-label="Explorer <?= e($c['name']) ?>">
            <?php if ($c['cover']): ?>
                <img loading="lazy" src="<?= e(img_url($c['cover'])) ?>" alt="<?= e($c['name']) ?>" onerror="this.style.display='none'">
            <?php endif ?>
            <span class="category-tile-name"><?= e($c['name']) ?> <span aria-hidden="true">↗</span></span>
        </a>
    <?php endforeach ?>
</div>
<?php endif ?>

<div class="section-title"><h2>Les pièces du moment</h2><a href="?page=shop">Explorer toute la boutique →</a></div>
<div class="grid catalog-grid home-grid">
    <?php foreach ($latest as $p) render_card($p); ?>
</div>
<?php if (!$latest): ?>
    <div class="catalog-empty"><h3>La collection arrive bientôt</h3><p>Aucun article n'est encore en ligne.</p></div>
<?php endif ?>
