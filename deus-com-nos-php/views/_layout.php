<?php /** Mise en page commune. Variables : $content, $title, $page, $cartCount */
$adminLayout = ($page === 'admin' && is_admin());
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="DEUS COM NOS : chaussures, sacs, tissus wax et perruques. Commandez en ligne et payez à la livraison.">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ccircle cx='32' cy='32' r='32' fill='%23293653'/%3E%3Ctext x='32' y='43' font-size='32' text-anchor='middle' fill='%23e2ddf5' font-family='Georgia,serif'%3ED.%3C/text%3E%3C/svg%3E">
    <meta name="theme-color" content="#293653">
    <link rel="stylesheet" href="style.css?v=20261117-blue-violet">
</head>
<body>
<?php if ($adminLayout): ?>
    <header class="admin-header">
        <nav class="nav admin-nav">
            <a class="logo" href="index.php?page=admin"><span class="brand-mark">D.</span> DEUS COM NOS <small>STUDIO ADMIN</small></a>
            <div class="links">
                <a href="index.php?page=admin">Tableau de bord</a>
                <a href="index.php">Voir la boutique</a>
                <a href="index.php?page=account">Mon compte</a>
            </div>
            <span class="admin-user">Administrateur · <?= e(user()['name']) ?></span>
        </nav>
    </header>
<?php else: ?>
    <div class="announcement">LA MODE QUI VOUS RESSEMBLE <span>✦</span> PAIEMENT À LA LIVRAISON</div>
    <header>
        <nav class="nav">
            <a class="logo" href="index.php"><span class="brand-mark">D.</span> DEUS COM NOS</a>
            <div class="links">
                <a href="index.php">Accueil</a>
                <a href="?page=shop">Collection</a>
            </div>
            <form class="search" action="index.php">
                <input type="hidden" name="page" value="shop">
                <input name="q" aria-label="Rechercher un article" placeholder="Rechercher une pièce..." value="<?= e($_GET['q'] ?? '') ?>">
                <button aria-label="Rechercher">⌕</button>
            </form>
            <a class="nav-account" href="?page=<?= user() ? 'account' : 'login' ?>"><?= e(user()['name'] ?? 'Connexion') ?></a>
            <a class="nav-cart" href="?page=cart" aria-label="Panier">Panier <span><?= (int)$cartCount ?></span></a>
            <?php if (is_admin()): ?><a href="?page=admin" class="admin-nav-link">Administration</a><?php endif ?>
        </nav>
    </header>
<?php endif ?>

<main class="<?= $page === 'admin' ? 'admin-main' : 'store-main' ?>">
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="message" role="status"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif ?>
    <?= $content ?>
</main>

<footer>
    <strong>DEUS COM NOS</strong>
    <span>La mode à votre image.</span>
    <nav class="footer-links" aria-label="Liens utiles">
        <a href="?page=shop">Collection</a>
        <a href="?page=orders">Mes commandes</a>
        <a href="?page=<?= user() ? 'account' : 'login' ?>">Mon compte</a>
    </nav>
    <small>© <?= date('Y') ?> Tous droits réservés.</small>
</footer>

<script>
// Administration : un clic sur une miniature sélectionne la photo dans la liste
document.querySelectorAll('[data-image-path]').forEach(function (button) {
    button.addEventListener('click', function () {
        var select = document.getElementById('product-image');
        if (!select) return;
        select.value = button.dataset.imagePath;
        document.querySelectorAll('[data-image-path]').forEach(function (b) { b.setAttribute('aria-pressed', String(b === button)); });
        select.focus({preventScroll: true});
    });
});

// Accueil : carrousel de photos
(function () {
    var carousel = document.querySelector('[data-carousel]');
    if (!carousel) return;
    var slides = [].slice.call(carousel.querySelectorAll('.hero-slide'));
    var dots = [].slice.call(carousel.querySelectorAll('[data-carousel-dot]'));
    var counter = carousel.querySelector('[data-carousel-count]');
    if (slides.length < 1) return;
    var index = 0, timer = null, interval = 5000;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    function show(next) {
        index = (next + slides.length) % slides.length;
        slides.forEach(function (slide, i) { slide.classList.toggle('is-active', i === index); slide.setAttribute('aria-hidden', String(i !== index)); });
        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === index);
            if (i === index) dot.setAttribute('aria-current', 'true'); else dot.removeAttribute('aria-current');
        });
        if (counter) counter.textContent = String(index + 1).padStart(2, '0');
        var copy = document.querySelector('[data-hero-copy]');
        if (copy) { copy.classList.remove('hero-copy-animate'); void copy.offsetWidth; copy.classList.add('hero-copy-animate'); }
    }
    function stop() { if (timer !== null) { clearInterval(timer); timer = null; } }
    function start() { stop(); if (slides.length > 1 && !document.hidden && !reducedMotion.matches) timer = setInterval(function () { show(index + 1); }, interval); }
    var prev = carousel.querySelector('[data-carousel-prev]'), nextBtn = carousel.querySelector('[data-carousel-next]');
    if (prev) prev.addEventListener('click', function () { show(index - 1); start(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { show(index + 1); start(); });
    dots.forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); start(); }); });
    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', function (event) { if (!carousel.contains(event.relatedTarget)) start(); });
    document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
    show(0);
    start();
})();
</script>
</body>
</html>
