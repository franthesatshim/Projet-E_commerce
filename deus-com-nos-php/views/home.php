<?php
$title = 'DEUS COM NOS — Chaussures, sacs, wax et perruques';

// Images d'ambiance haute résolution (Unsplash / Pexels), isolées VISUELLEMENT
// grâce au mode de fusion CSS. Les PNG locaux servent de secours hors ligne.
// Ce ne sont pas des fiches de produits ni des photos garanties du stock.
$slides = [
    [
        'image' => 'https://images.unsplash.com/photo-1691480250099-a63081ecfcb8?auto=format&fit=crop&w=1600&q=86',
        'fallback' => 'assets/hero/sac-bordeaux.png',
        'alt' => 'Sac à main brun élégant suspendu dans le carrousel',
        'style' => 'bag',
    ],
    [
        'image' => 'https://images.pexels.com/photos/28821783/pexels-photo-28821783.jpeg?auto=compress&cs=tinysrgb&w=1600',
        'fallback' => 'assets/hero/chaussures-ballerines.png',
        'alt' => 'Escarpins féminins élégants semblant flotter',
        'style' => 'heels',
    ],
    [
        'image' => 'https://images.pexels.com/photos/17152271/pexels-photo-17152271.jpeg?auto=compress&cs=tinysrgb&w=1600',
        'fallback' => 'assets/hero/sac-dore.png',
        'alt' => 'Petit sac à main noir flottant',
        'style' => 'small-bag',
    ],
    [
        'image' => 'https://images.pexels.com/photos/26856057/pexels-photo-26856057.jpeg?auto=compress&cs=tinysrgb&w=1600',
        'fallback' => 'assets/hero/chaussures-noires.png',
        'alt' => 'Sandales à talons féminines en lévitation',
        'style' => 'sandals',
    ],
];

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
    <div class="hero-art hero-carousel" data-carousel data-direction="next" role="region" aria-roledescription="carrousel" aria-label="Objets de mode flottants DEUS COM NOS">
        <div class="hero-slides" aria-live="off">
            <?php foreach ($slides as $i => $s): ?>
                <div class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" role="group" aria-roledescription="diapositive" aria-label="<?= ($i + 1) . ' sur ' . count($slides) ?>" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
                    <div class="hero-slide-float hero-item-<?= e($s['style']) ?>">
                        <img class="hero-floating-object hero-object-online"
                             src="<?= e($s['image']) ?>"
                             data-fallback="<?= e(img_url($s['fallback'])) ?>"
                             alt="<?= e($s['alt']) ?>"
                             decoding="async" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                             <?= $i === 0 ? 'fetchpriority="high"' : '' ?>
                             onerror="this.onerror=null;this.src=this.dataset.fallback;this.classList.remove('hero-object-online');this.classList.add('hero-object-local')">
                    </div>
                </div>
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
        <div class="hero-carousel-progress" aria-hidden="true"><span data-carousel-progress></span></div>
        <span class="hero-carousel-disclaimer">Visuels de présentation</span>
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
