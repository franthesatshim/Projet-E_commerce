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
    <link rel="stylesheet" href="style.css?v=20261117-cinema-hd-2">
    <link rel="stylesheet" href="payments.css?v=20261117-netikash">
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

// Accueil : objets de présentation en lévitation, transitions cinématiques.
(function () {
    var carousel = document.querySelector('[data-carousel]');
    if (!carousel) return;

    var slides = Array.prototype.slice.call(carousel.querySelectorAll('.hero-slide'));
    var dots = Array.prototype.slice.call(carousel.querySelectorAll('[data-carousel-dot]'));
    var counter = carousel.querySelector('[data-carousel-count]');
    var previousButton = carousel.querySelector('[data-carousel-prev]');
    var nextButton = carousel.querySelector('[data-carousel-next]');
    if (!slides.length) return;

    var index = 0;
    var timer = null;
    var cleanupTimer = null;
    var frame = null;
    var interval = 5500;
    var transitionDuration = 1250;
    var transitioning = false;
    var hovered = false;
    var focused = false;
    var touchStart = null;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function stop() {
        if (timer !== null) window.clearTimeout(timer);
        timer = null;
        carousel.classList.remove('is-playing');
    }

    function canPlay() {
        return slides.length > 1 && !document.hidden && !reducedMotion.matches && !hovered && !focused;
    }

    function start() {
        stop();
        if (!canPlay()) return;
        // Relancer l'animation de la barre à chaque cycle.
        void carousel.offsetWidth;
        carousel.classList.add('is-playing');
        timer = window.setTimeout(function () {
            show(index + 1, 1);
        }, interval);
    }

    function updateControls() {
        dots.forEach(function (dot, i) {
            var selected = i === index;
            dot.classList.toggle('is-active', selected);
            if (selected) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
        if (counter) counter.textContent = String(index + 1).padStart(2, '0');
    }

    function show(next, direction, initial) {
        var target = (next + slides.length) % slides.length;
        if (!initial && (target === index || transitioning)) return;
        stop();
        carousel.dataset.direction = direction < 0 ? 'prev' : 'next';
        if (cleanupTimer !== null) window.clearTimeout(cleanupTimer);
        if (frame !== null) window.cancelAnimationFrame(frame);

        var outgoing = slides[index];
        var incoming = slides[target];
        if (initial) {
            slides.forEach(function (slide, i) {
                slide.classList.remove('is-leaving', 'is-preparing');
                slide.classList.toggle('is-active', i === target);
                slide.setAttribute('aria-hidden', String(i !== target));
            });
            index = target;
            updateControls();
            start();
            return;
        }

        transitioning = true;
        outgoing.classList.remove('is-active', 'is-preparing');
        outgoing.classList.add('is-leaving');
        incoming.classList.remove('is-leaving', 'is-active');
        incoming.classList.add('is-preparing');
        // Force un état de départ distinct : l'objet arrive en pivotant.
        void incoming.offsetWidth;
        index = target;
        updateControls();
        incoming.setAttribute('aria-hidden', 'false');
        outgoing.setAttribute('aria-hidden', 'true');

        frame = window.requestAnimationFrame(function () {
            frame = window.requestAnimationFrame(function () {
                incoming.classList.remove('is-preparing');
                incoming.classList.add('is-active');
            });
        });
        cleanupTimer = window.setTimeout(function () {
            outgoing.classList.remove('is-leaving');
            transitioning = false;
            cleanupTimer = null;
        }, reducedMotion.matches ? 0 : transitionDuration);
        start();
    }

    function goRelative(delta) { show(index + delta, delta); }
    if (previousButton) previousButton.addEventListener('click', function () { goRelative(-1); });
    if (nextButton) nextButton.addEventListener('click', function () { goRelative(1); });
    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { show(i, i < index ? -1 : 1); });
    });
    carousel.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') { event.preventDefault(); goRelative(-1); }
        if (event.key === 'ArrowRight') { event.preventDefault(); goRelative(1); }
    });

    // Sur mobile, glisser horizontalement pour changer d'objet.
    carousel.addEventListener('touchstart', function (event) {
        if (event.touches.length !== 1) return;
        touchStart = {x:event.touches[0].clientX, y:event.touches[0].clientY};
        stop();
    }, {passive:true});
    carousel.addEventListener('touchend', function (event) {
        if (!touchStart) return;
        var dx = event.changedTouches[0].clientX - touchStart.x;
        var dy = event.changedTouches[0].clientY - touchStart.y;
        touchStart = null;
        if (Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy) * 1.15) {
            goRelative(dx < 0 ? 1 : -1);
        } else start();
    }, {passive:true});
    carousel.addEventListener('touchcancel', function () { touchStart = null; start(); }, {passive:true});

    // Très léger parallax sur ordinateur : aucun suivi de souris sur mobile.
    carousel.addEventListener('pointermove', function (event) {
        if (event.pointerType !== 'mouse' || reducedMotion.matches) return;
        var rect = carousel.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        var x = (event.clientX - rect.left) / rect.width - .5;
        var y = (event.clientY - rect.top) / rect.height - .5;
        carousel.style.setProperty('--tilt-y', (x * 7).toFixed(1) + 'deg');
        carousel.style.setProperty('--tilt-x', (-y * 5).toFixed(1) + 'deg');
    });
    carousel.addEventListener('pointerleave', function () {
        carousel.style.removeProperty('--tilt-x');
        carousel.style.removeProperty('--tilt-y');
    });
    carousel.addEventListener('mouseenter', function () { hovered = true; stop(); });
    carousel.addEventListener('mouseleave', function () { hovered = false; start(); });
    carousel.addEventListener('focusin', function () { focused = true; stop(); });
    carousel.addEventListener('focusout', function (event) {
        if (!carousel.contains(event.relatedTarget)) { focused = false; start(); }
    });
    document.addEventListener('visibilitychange', start);
    if (typeof reducedMotion.addEventListener === 'function') {
        reducedMotion.addEventListener('change', start);
    }

    // Les visuels restent statiques pour les personnes préférant moins d'animation.
    show(0, 1, true);
})();
</script>
</body>
</html>
