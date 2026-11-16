<?php
$title = 'Administration — DEUS COM NOS';
if (!is_admin()):
    http_response_code(403); ?>
    <div class="panel auth-panel">
        <h2>Accès administrateur</h2>
        <p>Connectez-vous avec un compte disposant du rôle administrateur pour gérer les produits, catégories, commandes et utilisateurs.</p>
        <a class="btn" href="?page=login">Se connecter</a>
    </div>
    <?php return; endif;

$stats = [
    'Produits'            => $db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'Commandes'           => $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'Commandes en attente'=> $db->query("SELECT COUNT(*) FROM orders WHERE status='en_attente'")->fetchColumn(),
    'Clients'             => $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn(),
];

// Produit en cours de modification (formulaire prérempli)
$editing = null;
if (isset($_GET['edit'])) {
    $es = $db->prepare('SELECT * FROM products WHERE id=?');
    $es->execute([(int)$_GET['edit']]);
    $editing = $es->fetch() ?: null;
}
$curImage = $editing['image'] ?? '';
$isRemote = str_starts_with($curImage, 'https://');
$inGallery = in_array($curImage, $assetImages, true) || in_array($curImage, $screenshots, true);

$products = $db->query('SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC LIMIT 200')->fetchAll();

// Commandes + leurs articles (une seule requête pour les articles)
$orders = $db->query('SELECT o.*, u.name AS customer, u.email FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 100')->fetchAll();
$itemsByOrder = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $q = $db->prepare('SELECT oi.order_id, oi.quantity, p.name FROM order_items oi JOIN products p ON p.id=oi.product_id WHERE oi.order_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
    $q->execute($ids);
    foreach ($q as $row) $itemsByOrder[(int)$row['order_id']][] = $row;
}
?>
<div class="admin-heading">
    <span class="catalog-eyebrow">GESTION DE LA BOUTIQUE</span>
    <h1>Tableau de bord</h1>
    <p>Gérez vos produits, commandes et clients depuis un espace dédié.</p>
</div>

<div class="admin-grid">
    <?php foreach ($stats as $label => $v): ?>
        <div class="stat"><h3><?= e($label) ?></h3><h1><?= (int)$v ?></h1></div>
    <?php endforeach ?>
</div>

<h2 id="formulaire"><?= $editing ? 'Modifier : ' . e($editing['name']) : 'Ajouter un produit' ?></h2>
<?php if ($editing): ?><p class="muted"><a href="?page=admin#formulaire">Annuler la modification et revenir à un nouveau produit</a></p><?php endif ?>
<form class="panel admin-form" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="admin_product">
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><?php endif ?>

    <label>Nom du produit<input class="field" name="name" maxlength="160" value="<?= e($editing['name'] ?? '') ?>" required></label>
    <label>Description<textarea class="field" name="description" rows="4" maxlength="2000" placeholder="Ce que montre la photo : modèle, couleurs, matière…"><?= e($editing['description'] ?? '') ?></textarea></label>
    <div class="form-row">
        <label>Prix (USD)<input class="field" name="price" type="number" min="0" step=".01" value="<?= e($editing['price'] ?? '') ?>" required></label>
        <label>Stock<input class="field" name="stock" type="number" min="0" step="1" value="<?= e($editing['stock'] ?? '') ?>" required></label>
        <label>Catégorie
            <select class="field" name="category_id">
                <option value="0">Sans catégorie</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $editing && (int)$editing['category_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </div>

    <fieldset class="photo-fieldset">
        <legend>Photo du produit</legend>
        <?php if ($curImage): ?>
            <p class="muted">Photo actuelle :</p>
            <img class="admin-current-photo" src="<?= e(img_url($curImage)) ?>" alt="Photo actuelle">
        <?php endif ?>
        <label>Envoyer une nouvelle photo (JPG, PNG ou WebP, 5 Mo maximum)
            <input class="field" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
        </label>
        <label>…ou choisir une photo déjà sur le site
            <select class="field" id="product-image" name="image">
                <option value="">Aucune photo</option>
                <?php if ($curImage && !$isRemote && !$inGallery): ?>
                    <option value="<?= e($curImage) ?>" selected><?= e($curImage) ?> (actuelle)</option>
                <?php endif ?>
                <?php if ($assetImages): ?>
                    <optgroup label="Photos de la boutique (assets/products)">
                        <?php foreach ($assetImages as $path): ?>
                            <option value="<?= e($path) ?>" <?= $curImage === $path ? 'selected' : '' ?>><?= e(basename($path)) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                <?php endif ?>
                <?php if ($screenshots): ?>
                    <optgroup label="Anciennes captures (Screenshots)">
                        <?php foreach ($screenshots as $path): ?>
                            <option value="<?= e($path) ?>" <?= $curImage === $path ? 'selected' : '' ?>><?= e(basename($path)) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                <?php endif ?>
            </select>
        </label>
        <label>…ou coller l'URL d'une photo Internet (images.unsplash.com ou images.pexels.com)
            <input class="field" type="url" name="remote_image" placeholder="https://images.unsplash.com/..." value="<?= e($isRemote ? $curImage : '') ?>">
        </label>
        <p class="muted">Priorité : fichier envoyé, puis URL, puis photo de la liste. Cliquez sur une miniature pour la sélectionner.</p>
        <?php if ($assetImages): ?>
            <div class="screenshot-grid">
                <?php foreach ($assetImages as $path): ?>
                    <button type="button" class="screenshot-choice" data-image-path="<?= e($path) ?>" title="<?= e(basename($path)) ?>">
                        <img loading="lazy" src="<?= e(img_url($path)) ?>" alt=""><small><?= e(basename($path)) ?></small>
                    </button>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </fieldset>
    <button><?= $editing ? 'Enregistrer les modifications' : 'Ajouter le produit' ?></button>
</form>

<h2>Produits (<?= count($products) ?>)</h2>
<div class="panel table-wrap">
    <table>
        <tr><th>Photo</th><th>Produit</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Visible</th><th>Modifier</th><th>Visibilité</th></tr>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><?php if ($p['image']): ?><img class="admin-thumb" src="<?= e(img_url($p['image'])) ?>" alt="" loading="lazy"><?php endif ?></td>
                <td><?= e($p['name']) ?> <small class="muted">#<?= (int)$p['id'] ?></small></td>
                <td><?= e($p['category'] ?? '—') ?></td>
                <td><?= money($p['price']) ?></td>
                <td><?= (int)$p['stock'] ?></td>
                <td><?= $p['active'] ? 'Oui' : 'Non' ?></td>
                <td><a class="btn outline" href="?page=admin&amp;edit=<?= (int)$p['id'] ?>#formulaire">Modifier</a></td>
                <td>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="admin_toggle">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="outline"><?= $p['active'] ? 'Masquer' : 'Afficher' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>

<h2>Catégories</h2>
<form class="panel" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="admin_category">
    <label>Nouvelle catégorie<input class="field" name="name" maxlength="100" required></label>
    <button>Ajouter la catégorie</button>
</form>
<div class="categories admin-chips">
    <?php foreach ($categories as $c): ?><span class="chip"><?= e($c['name']) ?> (<?= (int)$c['n'] ?>)</span><?php endforeach ?>
</div>

<h2>Commandes</h2>
<div class="panel table-wrap">
    <table>
        <tr><th>Commande</th><th>Client et livraison</th><th>Articles</th><th>Total</th><th>Paiement</th><th>Statut</th></tr>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= (int)$o['id'] ?><br><small class="muted"><?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></small></td>
                <td>
                    <strong><?= e($o['customer']) ?></strong><br>
                    <small><?= e($o['email']) ?></small><br>
                    <?php if (!empty($o['phone'])): ?><small>Tél. <?= e($o['phone']) ?></small><br><?php endif ?>
                    <small><?= nl2br(e($o['address'])) ?></small>
                </td>
                <td>
                    <?php foreach ($itemsByOrder[(int)$o['id']] ?? [] as $it): ?>
                        <?= (int)$it['quantity'] ?> × <?= e($it['name']) ?><br>
                    <?php endforeach ?>
                </td>
                <td><?= money($o['total']) ?></td>
                <td>
                    <strong><?= $o['payment_method'] === 'netikash' ? 'Netikash' : 'À la livraison' ?></strong><br>
                    <small><?= e(payment_label($o['payment_status'])) ?></small>
                </td>
                <td>
                    <form method="post" class="status-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="admin_status">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <select name="status" aria-label="Statut de la commande #<?= (int)$o['id'] ?>">
                            <?php foreach (ORDER_STATUSES as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $o['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach ?>
                        </select>
                        <button>OK</button>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>

<h2>Utilisateurs</h2>
<div class="panel table-wrap">
    <table>
        <tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Inscription</th></tr>
        <?php foreach ($db->query('SELECT name,email,role,created_at FROM users ORDER BY id DESC LIMIT 100') as $u): ?>
            <tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td></tr>
        <?php endforeach ?>
    </table>
</div>
