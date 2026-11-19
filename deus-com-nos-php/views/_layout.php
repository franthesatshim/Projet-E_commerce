<?php /** Mise en page commune. Variables : $content, $title, $page, $cartCount */
$adminLayout = ($page === 'admin' && is_admin());
$customer = user();
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="Découvrez la collection DEUS COM NOS : chaussures, sacs, tissus wax et perruques.">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='32' fill='%23312620'/%3E%3Ctext x='32' y='43' font-size='32' text-anchor='middle' fill='%23f5eadc' font-family='Georgia,serif'%3ED.%3C/text%3E%3C/svg%3E">
    <meta name="theme-color" content="#faf8f4">
    <link rel="stylesheet" href="style.css?v=20261119-editorial">
    <link rel="stylesheet" href="payments.css?v=20261119-editorial">
</head>
<body>
<a class="skip-link" href="#main-content">Aller au contenu</a>
<?php if ($adminLayout): ?>
    <header class="admin-header">
        <nav class="admin-nav" aria-label="Administration">
            <a class="logo" href="index.php?page=admin">DEUS COM NOS</a>
            <div class="links">
                <a href="index.php?page=admin" aria-current="page">Tableau de bord</a>
                <a href="index.php">Voir la boutique</a>
                <a href="index.php?page=account">Mon compte</a>
            </div>
            <span class="admin-user">Administrateur · <?= e($customer['name']) ?></span>
        </nav>
    </header>
<?php else: ?>
    <div class="announcement">Paiement à la livraison disponible</div>
    <header class="store-header">
        <nav class="nav" aria-label="Navigation principale">
            <div class="links nav-primary">
                <a href="index.php"<?= $page === 'home' ? ' aria-current="page"' : '' ?>>Accueil</a>
                <a href="?page=shop"<?= $page === 'shop' ? ' aria-current="page"' : '' ?>>Collection</a>
            </div>
            <a class="logo" href="index.php">DEUS COM NOS</a>
            <div class="links nav-actions">
                <?php if (is_admin()): ?><a class="admin-nav-link" href="?page=admin">Administration</a><?php endif ?>
                <a class="nav-account" href="?page=<?= $customer ? 'account' : 'login' ?>"<?= in_array($page, ['account', 'login'], true) ? ' aria-current="page"' : '' ?>><?= $customer ? 'Mon compte' : 'Connexion' ?></a>
                <a class="nav-cart" href="?page=cart"<?= $page === 'cart' ? ' aria-current="page"' : '' ?>>Panier <span><?= (int)$cartCount ?></span></a>
            </div>
            <form class="search" action="index.php" role="search">
                <input type="hidden" name="page" value="shop">
                <input type="search" name="q" aria-label="Rechercher un article" placeholder="Rechercher une pièce…" value="<?= e($_GET['q'] ?? '') ?>">
                <button type="submit" aria-label="Rechercher"><span class="search-icon" aria-hidden="true"></span></button>
            </form>
        </nav>
    </header>
<?php endif ?>

<main id="main-content" class="<?= $page === 'admin' ? 'admin-main' : 'store-main' ?><?= $page === 'home' ? ' home-main' : '' ?>">
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="message" role="status"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <a class="logo" href="index.php">DEUS COM NOS</a>
        <nav class="footer-links" aria-label="Liens utiles">
            <a href="?page=shop">Collection</a>
            <a href="?page=orders">Mes commandes</a>
            <a href="?page=<?= $customer ? 'account' : 'login' ?>">Mon compte</a>
        </nav>
        <small>© <?= date('Y') ?> DEUS COM NOS</small>
    </div>
</footer>

<?php if ($adminLayout): ?>
<script>
// Un clic sur une miniature sélectionne la photo dans la liste.
document.querySelectorAll('[data-image-path]').forEach(function (button) {
    button.addEventListener('click', function () {
        var select = document.getElementById('product-image');
        if (!select) return;
        select.value = button.dataset.imagePath;
        document.querySelectorAll('[data-image-path]').forEach(function (choice) {
            choice.setAttribute('aria-pressed', String(choice === button));
        });
        select.focus({preventScroll: true});
    });
});
</script>
<?php endif ?>
</body>
</html>
