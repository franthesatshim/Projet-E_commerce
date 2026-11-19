<?php
// ---------------------------------------------------------------------------
// Traitement de tous les formulaires (méthode POST). Inclus par index.php.
// Variables disponibles : $db, $assetImages, $screenshots
// ---------------------------------------------------------------------------

// Un envoi dépassant post_max_size arrive vide : on l'explique au lieu d'afficher « session expirée ».
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    flash('Fichier trop volumineux pour le serveur (voir post_max_size dans php.ini).');
    redirect('index.php?page=admin');
}

check();
$action = (string)($_POST['action'] ?? '');

// Page où revenir si une action échoue
$returnPage = match (true) {
    $action === 'register'                        => 'register',
    $action === 'login'                           => 'login',
    in_array($action, ['checkout', 'update_cart', 'remove'], true) => 'cart',
    in_array($action, ['netikash_start', 'netikash_sync', 'shwary_start', 'shwary_sync'], true) => 'orders',
    str_starts_with($action, 'admin_')            => 'admin',
    default                                       => 'shop',
};

/** Valide la photo d'un produit (envoi, galerie ou URL) et renvoie le chemin à enregistrer. */
function resolve_product_image(array $assetImages, array $screenshots): string {
    $file = $_FILES['photo'] ?? null;
    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'Photo trop volumineuse pour le serveur (voir upload_max_filesize dans php.ini).'
                : "L'envoi de la photo a échoué.");
        }
        if ($file['size'] > 5 * 1024 * 1024) throw new Exception('Photo trop volumineuse (5 Mo maximum).');
        $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        $info = @getimagesize($file['tmp_name']);
        if (!$info || !isset($types[$info[2]])) throw new Exception('Format de photo refusé : utilisez JPG, PNG ou WebP.');
        // Uploads persist in the Docker volume; bundled catalogue pictures are never overwritten.
        $dir = __DIR__ . '/assets/products/uploads';
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) throw new Exception("Impossible de créer le dossier assets/products.");
        $name = 'up-' . bin2hex(random_bytes(6)) . '.' . $types[$info[2]];   // nom généré : jamais celui du client
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) throw new Exception("Impossible d'enregistrer la photo.");
        return 'assets/products/uploads/' . $name;
    }

    $image = trim((string)($_POST['remote_image'] ?? '')) ?: trim((string)($_POST['image'] ?? ''));
    if ($image === '') return '';
    $okAsset  = in_array($image, $assetImages, true);
    $okShot   = in_array($image, $screenshots, true);
    $okRemote = (bool)preg_match('~^https://images\.(unsplash\.com|pexels\.com)/[a-zA-Z0-9/_?=&.%-]+$~', $image);
    if (!$okAsset && !$okShot && !$okRemote) {
        throw new Exception('Photo invalide : choisissez une photo de la liste, envoyez un fichier, ou collez une URL images.unsplash.com / images.pexels.com.');
    }
    return $image;
}

try {
    switch ($action) {

        // ----------------------------------------------------------- Compte client
        case 'register':
            $name  = trim((string)($_POST['name'] ?? ''));
            $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
            $pass  = (string)($_POST['password'] ?? '');
            if (mb_strlen($name) < 2 || mb_strlen($name) > 120 || !$email || strlen($email) > 190) {
                throw new Exception('Nom et adresse e-mail valides requis.');
            }
            if (strlen($pass) < 8 || strlen($pass) > 72) throw new Exception('Le mot de passe doit contenir entre 8 et 72 caractères.');
            try {
                $db->prepare('INSERT INTO users(name,email,password) VALUES(?,?,?)')
                   ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') throw new Exception('Cette adresse e-mail est déjà utilisée.');
                throw $ex;
            }
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$db->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'user'];
            flash('Bienvenue ' . $name . ' ! Votre compte est créé.');
            redirect(empty($_SESSION['cart']) ? 'index.php' : 'index.php?page=cart');

        case 'login':
            $s = $db->prepare('SELECT * FROM users WHERE email=?');
            $s->execute([trim((string)($_POST['email'] ?? ''))]);
            $u = $s->fetch();
            if (!$u || !password_verify((string)($_POST['password'] ?? ''), $u['password'])) {
                throw new Exception('Adresse e-mail ou mot de passe incorrect.');
            }
            session_regenerate_id(true);
            $_SESSION['user'] = array_intersect_key($u, array_flip(['id', 'name', 'email', 'role']));
            redirect(empty($_SESSION['cart']) ? 'index.php' : 'index.php?page=cart');

        case 'logout':
            $_SESSION = [];
            session_regenerate_id(true);
            redirect();

        // ----------------------------------------------------------------- Panier
        case 'add':
            $id = (int)($_POST['id'] ?? 0);
            $s = $db->prepare('SELECT id,stock FROM products WHERE id=? AND active=1');
            $s->execute([$id]);
            $p = $s->fetch();
            if (!$p || (int)$p['stock'] < 1) throw new Exception('Ce produit est indisponible.');
            $qty = max(1, min(20, (int)($_POST['qty'] ?? 1)));
            $_SESSION['cart'][$id] = min((int)$p['stock'], ($_SESSION['cart'][$id] ?? 0) + $qty);
            flash('Produit ajouté au panier.');
            redirect('index.php?page=cart');

        case 'update_cart':
            $id  = (int)($_POST['id'] ?? 0);
            $qty = (int)($_POST['qty'] ?? 0);
            $s = $db->prepare('SELECT stock FROM products WHERE id=? AND active=1');
            $s->execute([$id]);
            $stock = $s->fetchColumn();
            if ($qty <= 0 || $stock === false || (int)$stock < 1) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id] = min($qty, (int)$stock);
            redirect('index.php?page=cart');

        case 'remove':
            unset($_SESSION['cart'][(int)($_POST['id'] ?? 0)]);
            redirect('index.php?page=cart');

        // --------------------------------------------------------------- Commande
        case 'checkout':
            if (!user()) {
                flash('Connectez-vous (ou créez un compte) pour valider votre commande.');
                redirect('index.php?page=login');
            }
            $method = (string)($_POST['payment_method'] ?? 'paiement_livraison');
            if (!in_array($method, ['paiement_livraison', 'netikash', 'shwary'], true)) {
                throw new Exception('Mode de paiement inconnu.');
            }
            if ($method === 'netikash') {
                require_once __DIR__ . '/payments/netikash.php';
                if (!netikash_ready()) throw new Exception('Le paiement en ligne n’est pas encore activé. Choisissez le paiement à la livraison.');
                try {
                    // Vérifier que la migration est appliquée AVANT de réserver du stock.
                    $db->query('SELECT id FROM payment_attempts LIMIT 1');
                    $db->query('SELECT order_id FROM invoices LIMIT 1');
                } catch (PDOException $e) {
                    error_log('Netikash : table payment_attempts absente : ' . $e->getMessage());
                    throw new Exception('Paiement indisponible : les migrations Netikash et factures doivent être appliquées.');
                }
            }
            $address = trim((string)($_POST['address'] ?? ''));
            $phone   = trim((string)($_POST['phone'] ?? ''));
            if (mb_strlen($address) < 8 || mb_strlen($address) > 1000) throw new Exception('Adresse de livraison invalide (8 caractères minimum).');
            if (!preg_match('/^[0-9+()\s.-]{8,20}$/', $phone)) throw new Exception('Numéro de téléphone invalide.');
            $shwaryAmount = 0;
            $shwarySandbox = true;
            if ($method === 'shwary') {
                require_once __DIR__ . '/payments/shwary.php';
                if (!shwary_ready()) throw new Exception('Le paiement Shwary est actuellement indisponible.');
                $phone = shwary_phone($phone);
                $shwaryAmount = (int)($_POST['shwary_amount'] ?? 0);
                $shwarySandbox = ($_POST['shwary_sandbox'] ?? '1') !== '0';
                if ($shwarySandbox !== shwary_sandbox()) throw new Exception('Le mode Shwary a changé. Vérifiez votre panier.');
                $db->query('SELECT is_sandbox FROM payment_attempts LIMIT 1');
                $db->query('SELECT order_id FROM invoices LIMIT 1');
            }
            $cart = $_SESSION['cart'] ?? [];
            if (!$cart) throw new Exception('Votre panier est vide.');

            $db->beginTransaction();
            $lines = [];
            $total = 0.0;
            foreach ($cart as $id => $qty) {
                $s = $db->prepare('SELECT * FROM products WHERE id=? FOR UPDATE');
                $s->execute([(int)$id]);
                $p = $s->fetch();
                if (!$p || !$p['active'] || $qty < 1 || $qty > (int)$p['stock']) {
                    throw new Exception('Stock insuffisant ou article indisponible : ' . ($p['name'] ?? 'article retiré') . '. Vérifiez votre panier.');
                }
                $lines[] = [$p, (int)$qty];
                $total += (float)$p['price'] * $qty;
            }
            if ($total <= 0.0) throw new Exception('Total invalide. Contactez la boutique.');
            if ($method === 'shwary' && shwary_quote((string)round($total, 2)) !== $shwaryAmount) {
                throw new Exception('Le montant du paiement Shwary a changé ou est indisponible. Vérifiez votre panier.');
            }
            $db->prepare('INSERT INTO orders(user_id,total,address,phone,status,payment_method,payment_status,payment_reference) VALUES(?,?,?,?,?,?,?,?)')
               ->execute([user()['id'], round($total, 2), $address, $phone, 'en_attente', $method, 'en_attente', 'CMD-' . strtoupper(bin2hex(random_bytes(6)))]);
            $orderId = (int)$db->lastInsertId();
            $insItem = $db->prepare('INSERT INTO order_items(order_id,product_id,quantity,unit_price) VALUES(?,?,?,?)');
            $decStock = $db->prepare('UPDATE products SET stock=stock-? WHERE id=?');
            foreach ($lines as [$p, $qty]) {
                $insItem->execute([$orderId, $p['id'], $qty, $p['price']]);
                $decStock->execute([$qty, $p['id']]);
            }
            $db->commit();
            unset($_SESSION['cart']);
            if ($method === 'shwary') {
                try {
                    $paymentStatus = shwary_start($db, $orderId, (int)user()['id'], $shwaryAmount, $shwarySandbox);
                    flash($paymentStatus === 'en_attente'
                        ? ($shwarySandbox ? 'Demande de test envoyée. Vérifiez son statut dans le détail de commande.'
                            : 'Demande envoyée. Validez le paiement sur votre téléphone Mobile Money.')
                        : 'État du paiement : ' . payment_label($paymentStatus));
                } catch (RuntimeException $e) {
                    if ($e instanceof PDOException) throw $e;
                    flash('Commande #' . $orderId . ' enregistrée. ' . $e->getMessage());
                }
                redirect('index.php?page=order&id=' . $orderId);
            }
            if ($method === 'netikash') {
                // Never hold database locks while calling a remote payment API.
                try {
                    $paymentUrl = netikash_start_checkout($db, $orderId, (int)user()['id']);
                    redirect($paymentUrl);
                } catch (Throwable $e) {
                    error_log('Netikash initialisation commande #' . $orderId . ': ' . $e->getMessage());
                    flash('Commande #' . $orderId . ' enregistrée, paiement non confirmé. Consultez le détail pour suivre la situation.');
                    redirect('index.php?page=order&id=' . $orderId);
                }
            }
            flash('Commande #' . $orderId . ' enregistrée. Paiement à la livraison, à la réception de vos articles.');
            redirect('index.php?page=order&id=' . $orderId);

        // --------------- Démarrer ou vérifier un paiement Netikash (POST + CSRF + propriétaire)
        case 'netikash_start':
        case 'netikash_sync':
        case 'shwary_start':
        case 'shwary_sync':
            if (!user()) throw new Exception('Connexion obligatoire.');
            $provider = str_starts_with($action, 'shwary_') ? 'shwary' : 'netikash';
            $orderId = (int)($_POST['order_id'] ?? 0);
            $orderQ = $db->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
            $orderQ->execute([$orderId, (int)user()['id']]);
            $order = $orderQ->fetch();
            if (!$order || $order['payment_method'] !== $provider) throw new Exception('Commande ou moyen de paiement introuvable.');
            if ($provider === 'shwary') {
                require_once __DIR__ . '/payments/shwary.php';
                if ($action === 'shwary_start') {
                    $sandbox = ($_POST['shwary_sandbox'] ?? '1') !== '0';
                    $paymentStatus = shwary_start($db, $orderId, (int)user()['id'], (int)($_POST['shwary_amount'] ?? 0), $sandbox);
                    flash($paymentStatus === 'en_attente'
                        ? ($sandbox ? 'Demande de test envoyée. Vérifiez son statut dans le détail de commande.'
                            : 'Demande envoyée. Validez le paiement sur votre téléphone Mobile Money.')
                        : 'État du paiement : ' . payment_label($paymentStatus));
                    redirect('index.php?page=order&id=' . $orderId);
                }
            }
            require_once __DIR__ . '/payments/netikash.php';
            if ($action === 'netikash_start') {
                $url = netikash_start_checkout($db, $orderId, (int)user()['id']);
                redirect($url);
            }
            $st = $db->prepare('SELECT id FROM payment_attempts WHERE order_id=? ORDER BY id DESC LIMIT 1');
            $st->execute([$orderId]);
            $attemptId = (int)$st->fetchColumn();
            if ($attemptId < 1) throw new Exception('Aucune tentative de paiement.');
            $paymentStatus = $provider === 'shwary' ? shwary_sync_attempt($db, $attemptId) : netikash_sync_attempt($db, $attemptId);
            flash('État du paiement vérifié : ' . payment_label($paymentStatus));
            redirect('index.php?page=order&id=' . $orderId);

        // ------------------------------------------------------- Administration
        case 'admin_category':
            if (!is_admin()) throw new Exception('Accès refusé.');
            $name = trim((string)($_POST['name'] ?? ''));
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100) throw new Exception('Nom de catégorie invalide.');
            try {
                $db->prepare('INSERT INTO categories(name) VALUES(?)')->execute([$name]);
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') throw new Exception('Cette catégorie existe déjà.');
                throw $ex;
            }
            flash('Catégorie ajoutée.');
            redirect('index.php?page=admin');

        case 'admin_product':
            if (!is_admin()) throw new Exception('Accès refusé.');
            $id    = (int)($_POST['id'] ?? 0);
            $name  = trim((string)($_POST['name'] ?? ''));
            $desc  = trim((string)($_POST['description'] ?? ''));
            $price = $_POST['price'] ?? '';
            $stock = $_POST['stock'] ?? '';
            $cat   = (int)($_POST['category_id'] ?? 0);
            if ($name === '' || mb_strlen($name) > 160) throw new Exception('Nom du produit requis (160 caractères maximum).');
            if (mb_strlen($desc) > 2000) throw new Exception('Description trop longue (2000 caractères maximum).');
            if (!is_numeric($price) || (float)$price < 0 || (float)$price > 9999999) throw new Exception('Prix invalide.');
            if (!ctype_digit((string)$stock)) throw new Exception('Stock invalide (nombre entier positif).');
            if ($cat) {
                $c = $db->prepare('SELECT COUNT(*) FROM categories WHERE id=?');
                $c->execute([$cat]);
                if (!$c->fetchColumn()) throw new Exception('Catégorie inconnue.');
            }
            $image = resolve_product_image($assetImages, $screenshots);
            if ($id) {
                $db->prepare('UPDATE products SET name=?,description=?,price=?,stock=?,category_id=?,image=? WHERE id=?')
                   ->execute([$name, $desc, $price, (int)$stock, $cat ?: null, $image, $id]);
            } else {
                $db->prepare('INSERT INTO products(name,description,price,stock,category_id,image) VALUES(?,?,?,?,?,?)')
                   ->execute([$name, $desc, $price, (int)$stock, $cat ?: null, $image]);
            }
            flash('Produit enregistré.');
            redirect('index.php?page=admin');

        case 'admin_toggle':
            if (!is_admin()) throw new Exception('Accès refusé.');
            $db->prepare('UPDATE products SET active=1-active WHERE id=?')->execute([(int)($_POST['id'] ?? 0)]);
            flash('Visibilité du produit modifiée.');
            redirect('index.php?page=admin');

        case 'admin_confirm_cod':
            if (!is_admin()) throw new Exception('Accès refusé.');
            $orderId = (int)($_POST['id'] ?? 0);
            $db->beginTransaction();
            $q = $db->prepare("SELECT payment_status FROM orders WHERE id=? AND payment_method='paiement_livraison' AND status='livree' FOR UPDATE");
            $q->execute([$orderId]);
            if (!in_array($q->fetchColumn(), ['en_attente', 'paye'], true)) {
                throw new Exception('Seules les commandes livrées payables à la livraison peuvent être encaissées ici.');
            }
            require_once __DIR__ . '/invoices.php';
            $db->prepare("UPDATE orders SET payment_status='paye' WHERE id=?")->execute([$orderId]);
            create_paid_invoice($db, $orderId);
            $db->commit();
            flash('Encaissement confirmé. La facture est disponible.');
            redirect('index.php?page=admin');

        case 'admin_status':
            if (!is_admin()) throw new Exception('Accès refusé.');
            $newStatus = (string)($_POST['status'] ?? '');
            $orderId   = (int)($_POST['id'] ?? 0);
            if (!isset(ORDER_STATUSES[$newStatus])) throw new Exception('Statut invalide.');
            $db->beginTransaction();
            $st = $db->prepare('SELECT status FROM orders WHERE id=? FOR UPDATE');
            $st->execute([$orderId]);
            $oldStatus = $st->fetchColumn();
            if ($oldStatus === false) throw new Exception('Commande introuvable.');
            $pay = $db->prepare('SELECT payment_method,payment_status FROM orders WHERE id=? FOR UPDATE');
            $pay->execute([$orderId]);
            $payment = $pay->fetch();
            if (in_array($payment['payment_method'], ['netikash', 'shwary'], true)) {
                if (in_array($newStatus, ['confirmee','expediee','livree'], true) && $payment['payment_status'] !== 'paye') {
                    throw new Exception('Paiement en ligne non encaissé : expédition impossible.');
                }
                if ($newStatus === 'annulee') {
                    $pending = $db->prepare("SELECT COUNT(*) FROM payment_attempts WHERE order_id=? AND status IN ('initiating','unknown','requested','approved','locked','pending','submitted')");
                    $pending->execute([$orderId]);
                    if ((int)$pending->fetchColumn() > 0) {
                        throw new Exception('Paiement en ligne encore actif : attendez confirmation ou annulation côté prestataire.');
                    }
                    if ($payment['payment_status'] === 'paye') {
                        throw new Exception('Commande payée : gérer le remboursement avant annulation.');
                    }
                }
            }
            if ($oldStatus === 'annulee' && $newStatus !== 'annulee') throw new Exception('Une commande annulée ne peut pas être réactivée.');
            if ($oldStatus === 'livree' && $newStatus !== 'livree')   throw new Exception('Une commande livrée ne peut pas être modifiée.');
            if ($newStatus === 'annulee' && $oldStatus !== 'annulee') {   // on remet les articles en stock, une seule fois
                $items = $db->prepare('SELECT product_id,quantity FROM order_items WHERE order_id=?');
                $items->execute([$orderId]);
                $restore = $db->prepare('UPDATE products SET stock=stock+? WHERE id=?');
                foreach ($items as $it) $restore->execute([(int)$it['quantity'], (int)$it['product_id']]);
            }
            $db->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$newStatus, $orderId]);
            $db->commit();
            flash($newStatus === 'annulee' ? 'Commande annulée et stock rétabli.' : 'Statut de la commande mis à jour.');
            redirect('index.php?page=admin');

        default:
            throw new Exception('Action inconnue.');
    }
} catch (Throwable $ex) {
    if ($db->inTransaction()) $db->rollBack();
    if ($ex instanceof PDOException) {
        error_log('DEUS COM NOS - erreur BDD : ' . $ex->getMessage());
        flash("Une erreur technique est survenue. Réessayez ou contactez l'administrateur.");
    } else {
        flash($ex->getMessage());
    }
    if (in_array($action, ['netikash_start', 'netikash_sync', 'shwary_start', 'shwary_sync'], true) && user()) {
        redirect('index.php?page=order&id=' . max(1, (int)($_POST['order_id'] ?? 0)));
    }
    redirect('index.php?page=' . $returnPage);
}
