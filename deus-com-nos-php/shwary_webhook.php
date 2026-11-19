<?php
declare(strict_types=1);

require_once __DIR__ . '/payments/shwary.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

$ref = $_GET['ref'] ?? null;
$token = $_GET['token'] ?? null;
if (!getenv('SHWARY_MERCHANT_KEY') || !is_string($ref) || !is_string($token)
    || !preg_match('/^SW-[a-f0-9]{32}$/D', $ref)
    || !hash_equals(shwary_callback_token($ref), $token)) {
    http_response_code(403);
    exit;
}

$raw = file_get_contents('php://input', false, null, 0, 32769);
$data = $raw !== false && strlen($raw) <= 32768 ? json_decode($raw, true, 16) : null;
if (!is_array($data) || !shwary_uuid($data['id'] ?? null)) {
    http_response_code(400);
    exit;
}

// The callback only triggers verification. Its claimed status is never used.
require __DIR__ . '/config.php';
session_write_close();
try {
    $q = $db->prepare("SELECT a.id FROM payment_attempts a JOIN orders o ON o.id=a.order_id
        WHERE merchant_ref=? AND provider_id=? AND o.payment_method='shwary'");
    $q->execute([$ref, $data['id']]);
    $id = (int)$q->fetchColumn();
    if ($id > 0) shwary_sync_attempt($db, $id);
    http_response_code(204);
} catch (Throwable $e) {
    error_log('Shwary : vérification du callback impossible : ' . $e->getMessage());
    http_response_code(503);
}
