<?php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Session et en-têtes de sécurité
// ---------------------------------------------------------------------------
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || str_starts_with((string)(getenv('APP_URL') ?: ''), 'https://');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $https,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ---------------------------------------------------------------------------
// Connexion à la base de données (variables d'environnement DB_HOST, DB_NAME, DB_USER, DB_PASS)
// ---------------------------------------------------------------------------
$dsn = 'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1')
     . ';dbname=' . (getenv('DB_NAME') ?: 'deus_com_nos') . ';charset=utf8mb4';
try {
    $db = new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('DEUS COM NOS - connexion BDD impossible : ' . $e->getMessage());
    http_response_code(503);
    exit('Base de données indisponible. Vérifiez config.php et importez schema.sql.');
}

// ---------------------------------------------------------------------------
// Constantes métier
// ---------------------------------------------------------------------------
const ORDER_STATUSES = [
    'en_attente' => 'En attente',
    'confirmee'  => 'Confirmée',
    'expediee'   => 'Expédiée',
    'livree'     => 'Livrée',
    'annulee'    => 'Annulée',
];

// ---------------------------------------------------------------------------
// Fonctions utilitaires
// ---------------------------------------------------------------------------
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }

function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">'; }

function check(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Session expirée. Rechargez la page et réessayez.');
    }
}

function user(): ?array { return $_SESSION['user'] ?? null; }

function is_admin(): bool { return (user()['role'] ?? '') === 'admin'; }

function redirect(string $to = 'index.php'): never { header('Location: ' . $to); exit; }

function flash(string $msg): void { $_SESSION['flash'] = $msg; }

function money($v): string { return number_format((float)$v, 2, ',', ' ') . ' $'; }

const PAYMENT_STATUSES = [
    'en_attente' => 'En attente de paiement',
    'simule' => 'Paiement simulé',
    'autorise' => 'Paiement confirmé (en cours de règlement)',
    'paye' => 'Payé',
    'echoue' => 'Paiement échoué',
    'expire' => 'Demande expirée',
    'annule' => 'Paiement annulé',
    'bloque' => 'Paiement bloqué (vérification requise)',
];
function payment_label(?string $status): string { return PAYMENT_STATUSES[$status ?? ''] ?? (string)$status; }

function payment_method_label(?string $method): string {
    return match ($method) {
        'paiement_livraison' => 'À la livraison',
        'netikash' => 'Netikash',
        'shwary' => 'Shwary Mobile Money',
        default => $method ?? 'Non précisé',
    };
}

function status_label(?string $status): string { return ORDER_STATUSES[$status ?? ''] ?? (string)$status; }

/** Adresse utilisable dans un attribut src (gère les espaces et parenthèses des noms de fichiers). */
function img_url(?string $path): string {
    if (!$path) return '';
    if (preg_match('~^https?://~i', $path)) return $path;
    return implode('/', array_map('rawurlencode', explode('/', $path)));
}

/** Liste les images (jpg, png, webp) d'un dossier, sous forme de chemins relatifs à index.php. */
function list_images(string $dir, string $prefix): array {
    $out = [];
    if (is_dir($dir)) {
        foreach (scandir($dir) ?: [] as $f) {
            if (preg_match('/\.(jpe?g|png|webp)$/i', $f) && is_file($dir . '/' . $f)) $out[] = $prefix . $f;
        }
    }
    sort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
}
