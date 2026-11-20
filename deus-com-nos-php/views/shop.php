<?php
$title = 'Collection — DEUS COM NOS';
$q    = trim((string)($_GET['q'] ?? ''));
$cat  = (int)($_GET['category'] ?? 0);
$sort = (string)($_GET['sort'] ?? 'recent');
$order = match ($sort) {
    'price_asc'  => 'p.price ASC, p.id DESC',
    'price_desc' => 'p.price DESC, p.id DESC',
    default      => 'p.created_at DESC, p.id DESC',
};
$sql = 'SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.active=1';
$params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    array_push($params, $like, $like, $like);
}
if ($cat) { $sql .= ' AND p.category_id=?'; $params[] = $cat; }
$stmt = $db->prepare($sql . ' ORDER BY ' . $order . ' LIMIT 100');
$stmt->execute($params);
$products = $stmt->fetchAll();

$currentCat = null;
foreach ($shopCategories as $c) if ((int)$c['id'] === $cat) $currentCat = $c;
?>
<div class="catalog-intro" data-scroll-reveal>
    <div>
        <span class="catalog-eyebrow">COLLECTION DEUS COM NOS</span>
        <h1><?= $currentCat ? e($currentCat['name']) : 'Découvrez notre catalogue' ?></h1>
        <p><?= $q !== '' ? 'Résultats pour « ' . e($q) . ' »' : 'Des pièces tendance sélectionnées pour votre style.' ?></p>
    </div>
    <span class="catalog-count"><?= count($products) ?> article(s)</span>
</div>

<div class="catalog-tabs">
    <a class="catalog-tab <?= $cat === 0 ? 'is-active' : '' ?>" href="?page=shop">Tous les articles</a>
    <?php foreach ($shopCategories as $c): ?>
        <a class="catalog-tab <?= $cat === (int)$c['id'] ? 'is-active' : '' ?>" href="?page=shop&amp;category=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a>
    <?php endforeach ?>
</div>

<div class="shop">
    <aside class="panel catalog-sidebar">
        <h3>Filtrer les articles</h3>
        <form>
            <input type="hidden" name="page" value="shop">
            <label>Recherche<input class="field" name="q" value="<?= e($q) ?>"></label>
            <label>Catégorie
                <select class="field" name="category">
                    <option value="0">Toutes</option>
                    <?php foreach ($shopCategories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $cat === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
            <label>Trier
                <select class="field" name="sort">
                    <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                </select>
            </label>
            <button>Appliquer</button>
        </form>
    </aside>
    <section class="catalog-results">
        <div class="catalog-results-head" data-scroll-reveal><div><strong>Notre sélection</strong><span><?= count($products) ?> produit(s)</span></div></div>
        <div class="grid catalog-grid">
            <?php foreach ($products as $p) render_card($p); ?>
        </div>
        <?php if (!$products): ?>
            <div class="catalog-empty">
                <h3>Aucun article trouvé</h3>
                <p>Essayez une autre catégorie ou un autre mot-clé.</p>
                <a class="btn" href="?page=shop">Voir tous les articles</a>
            </div>
        <?php endif ?>
    </section>
</div>
