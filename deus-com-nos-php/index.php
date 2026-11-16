<?php
// ---------------------------------------------------------------------------
// DEUS COM NOS — point d'entrée unique : formulaires (actions.php) → données → vue (views/) → mise en page
// ---------------------------------------------------------------------------
require __DIR__ . '/config.php';
const APP_DIR = __DIR__;

// Photos disponibles pour l'administration : dossier assets/products (principal) et ancien dossier ../Screenshots
$assetImages = array_merge(
    list_images(APP_DIR . '/assets/products', 'assets/products/'),
    list_images(APP_DIR . '/assets/products/uploads', 'assets/products/uploads/')
);
$screenshots = list_images(APP_DIR . '/../Screenshots', '../Screenshots/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/actions.php';   // se termine toujours par une redirection
}

// ----------------------------------------------------------------- Routage
$page  = (string)($_GET['page'] ?? 'home');
$known = ['home', 'shop', 'product', 'cart', 'login', 'register', 'account', 'orders', 'order', 'admin'];
if (!in_array($page, $known, true)) {
    http_response_code(404);
    $page = '404';
}
if (in_array($page, ['account', 'orders', 'order'], true) && !user()) {
    flash('Connectez-vous pour accéder à cette page.');
    redirect('index.php?page=login');
}
if (in_array($page, ['login', 'register'], true) && user()) redirect('index.php?page=account');

// ------------------------------------------------- Données communes à toutes les pages
// Catégories avec leur nombre de produits visibles et une photo de couverture (le premier produit illustré)
$categories = $db->query(
    "SELECT c.*,
            (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.active=1) AS n,
            (SELECT p.image FROM products p WHERE p.category_id=c.id AND p.active=1 AND p.image IS NOT NULL AND p.image<>'' ORDER BY p.id LIMIT 1) AS cover
     FROM categories c ORDER BY c.name"
)->fetchAll();
// La boutique n'affiche que les catégories qui contiennent des produits
$shopCategories = array_values(array_filter($categories, fn($c) => (int)$c['n'] > 0));

$cart      = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);

// ------------------------------------------------- Panier : on retire ce qui n'est plus vendable
$cartItems = [];
$cartTotal = 0.0;
if ($page === 'cart' && $cart) {
    $ids = array_map('intval', array_keys($cart));
    $s = $db->prepare('SELECT * FROM products WHERE active=1 AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
    $s->execute($ids);
    $byId = [];
    foreach ($s as $p) $byId[(int)$p['id']] = $p;
    $changed = false;
    foreach ($cart as $id => $qty) {
        $id = (int)$id;
        $p  = $byId[$id] ?? null;
        if (!$p || (int)$p['stock'] < 1) { unset($_SESSION['cart'][$id]); $changed = true; continue; }
        $q = min((int)$qty, (int)$p['stock']);
        if ($q !== (int)$qty) { $_SESSION['cart'][$id] = $q; $changed = true; }
        $line = $q * (float)$p['price'];
        $cartItems[] = ['p' => $p, 'qty' => $q, 'line' => $line];
        $cartTotal  += $line;
    }
    if ($changed) flash('Votre panier a été mis à jour : certains articles ne sont plus disponibles ou en quantité réduite.');
    $cartCount = array_sum($_SESSION['cart'] ?? []);
}

// ------------------------------------------------- Vue + mise en page
require_once __DIR__ . '/views/_card.php';
$title = 'DEUS COM NOS — Boutique';
$view  = in_array($page, ['login', 'register'], true) ? 'auth' : $page;
ob_start();
require __DIR__ . '/views/' . $view . '.php';
$content = ob_get_clean();
require __DIR__ . '/views/_layout.php';
