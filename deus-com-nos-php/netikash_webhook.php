<?php
declare(strict_types=1);
// Route publique destinée uniquement au serveur Netikash. Aucun utilisateur ne peut valider son paiement par un simple retour navigateur.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
require __DIR__ . '/config.php';
require __DIR__ . '/payments/netikash.php';
header('Content-Type: application/json; charset=utf-8');
$secret = (string)getenv('NETIKASH_WEBHOOK_SECRET');
if (!netikash_ready() || $secret === '') { http_response_code(503); echo '{"error":"not_configured"}'; exit; }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 32768) { http_response_code(413); exit; }
$raw = file_get_contents('php://input');
$signature = trim((string)($_SERVER['HTTP_X_SIGNATURE'] ?? ''));
$timestamp = trim((string)($_SERVER['HTTP_X_TIMESTAMP'] ?? ''));
$mode = (string)(getenv('NETIKASH_WEBHOOK_SIGNATURE_MODE') ?: 'raw_base64');
if (!is_string($raw) || strlen($raw) > 32768 || !netikash_webhook_signature_ok($raw,$signature,$timestamp,$secret,$mode)) {
    http_response_code(401); echo '{"error":"unauthorized"}'; exit;
}
$data = json_decode($raw, true);
if (!is_array($data) || !is_string($data['ref'] ?? null) || !is_string($data['trx'] ?? null)) {
    http_response_code(400); echo '{"error":"invalid_payload"}'; exit;
}
try {
    $q = $db->prepare('SELECT * FROM payment_attempts WHERE merchant_ref=?');
    $q->execute([(string)$data['ref']]);
    $attempt = $q->fetch();
    if (!$attempt) { http_response_code(404); echo '{"error":"unknown_ref"}'; exit; }
    // Une notification signée reste indicative : la source de vérité est GET Netikash.
    // Le champ trx est un ID de transaction et peut être différent du request_id issu du lien.
    $status = netikash_sync_attempt($db, (int)$attempt['id']);
    echo json_encode(['received'=>true,'status'=>$status], JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('Netikash webhook verify: ' . $e->getMessage());
    http_response_code(503); echo '{"error":"retry_later"}';
}
