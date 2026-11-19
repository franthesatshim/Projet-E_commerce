<?php
declare(strict_types=1);

require_once __DIR__ . '/../invoices.php';

/**
 * Netikash hosted checkout (OAuth2 client credentials).
 * Documentation: https://developer.netikash.com/payment-flow
 * A server-to-server verification is required before accepting payment success.
 */
const NETIKASH_TOKEN_ENDPOINT = 'https://accounts.netikash.com/oauth2/token';
const NETIKASH_API_ENDPOINT = 'https://api.netikash.com/api/v1/trs';

function netikash_enabled(): bool {
    return getenv('NETIKASH_ENABLED') === '1';
}

function netikash_ready(): bool {
    $url = rtrim((string)(getenv('APP_URL') ?: ''), '/');
    $p = parse_url($url);
    return netikash_enabled()
        && extension_loaded('curl')
        && trim((string)getenv('NETIKASH_CLIENT_ID')) !== ''
        && trim((string)getenv('NETIKASH_CLIENT_SECRET')) !== ''
        && trim((string)getenv('NETIKASH_WEBHOOK_SECRET')) !== ''
        && is_array($p) && strtolower((string)($p['scheme'] ?? '')) === 'https'
        && !empty($p['host']);
}

function netikash_return_url(int $orderId): string {
    if (!netikash_ready()) {
        throw new RuntimeException('Paiement en ligne non configuré. Une URL HTTPS publique et les identifiants Netikash sont nécessaires.');
    }
    // APP_URL = URL de la racine de l'application /deus-com-nos-php (pas le domaine seul si le projet est dans un sous-dossier).
    return rtrim((string)getenv('APP_URL'), '/') . '/index.php?page=order&id=' . $orderId;
}

/** @return array<string,mixed> */
function netikash_http(string $url, string $method, ?array $payload, array $headers): array {
    if (!in_array($method, ['GET', 'POST'], true)) throw new RuntimeException('Méthode API invalide.');
    $ch = curl_init($url);
    if ($ch === false) throw new RuntimeException('Client HTTP indisponible.');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Accept: application/json', 'Content-Type: application/json'], $headers));
    }
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $failure = curl_error($ch);
    curl_close($ch);
    if ($body === false || $status === 0) {
        error_log('Netikash: transport indisponible: ' . $failure);
        throw new RuntimeException('Communication temporairement impossible avec Netikash.');
    }
    if (strlen($body) > 500000) throw new RuntimeException('Réponse Netikash anormale.');
    $result = json_decode($body, true);
    if ($status < 200 || $status >= 300 || !is_array($result) || ($result['error'] ?? false) === true) {
        error_log('Netikash: réponse HTTP ' . $status . ', code=' . (string)($result['code'] ?? 'inconnu'));
        throw new RuntimeException('Netikash ne peut pas traiter cette demande actuellement.');
    }
    return $result;
}

function netikash_token(): string {
    if (!netikash_ready()) throw new RuntimeException('Netikash non configuré.');
    $basic = base64_encode((string)getenv('NETIKASH_CLIENT_ID') . ':' . (string)getenv('NETIKASH_CLIENT_SECRET'));
    $r = netikash_http(NETIKASH_TOKEN_ENDPOINT, 'POST', ['grant_type' => 'client_credentials'], [
        'Authorization: Basic ' . $basic,
    ]);
    $token = (string)($r['access_token'] ?? ($r['data']['access_token'] ?? ''));
    if ($token === '') throw new RuntimeException('Jeton Netikash absent.');
    return $token;
}

/** @return array<string,mixed> */
function netikash_create_link(string $ref, string $total, int $orderId): array {
    $response = netikash_http(NETIKASH_API_ENDPOINT . '/transactions/requests/cli-payments', 'POST', [
        'amount' => (float)$total,
        'currency' => 'USD',
        'ref' => $ref,
        'referer_url' => netikash_return_url($orderId),
    ], ['Authorization: Bearer ' . netikash_token()]);
    $d = isset($response['data']) && is_array($response['data']) ? $response['data'] : $response;
    $link = (string)($d['link'] ?? '');
    $requestId = netikash_request_id_from_link($link);
    if ($requestId === null) throw new RuntimeException('Netikash a retourné un lien de paiement non reconnu.');
    return [
        'link' => $link,
        'request_id' => $requestId,
        'transaction' => (string)($d['trans'] ?? $d['trans_id'] ?? ''),
    ];
}

function netikash_request_id_from_link(string $link): ?string {
    $url = parse_url($link);
    if (!is_array($url) || ($url['scheme'] ?? '') !== 'https' || strtolower((string)($url['host'] ?? '')) !== 'pay.netikash.com') return null;
    if (!preg_match('~^/payment-requests/([a-zA-Z0-9_-]{4,180})/?$~', (string)($url['path'] ?? ''), $m)) return null;
    if (isset($url['user']) || isset($url['pass']) || isset($url['port'])) return null;
    return $m[1];
}

/** @return array<string,mixed> */
function netikash_verify_request(string $requestId): array {
    if (!preg_match('/^[a-zA-Z0-9_-]{4,180}$/', $requestId)) throw new RuntimeException('Identifiant de paiement invalide.');
    $r = netikash_http(NETIKASH_API_ENDPOINT . '/transactions/requests/' . rawurlencode($requestId), 'GET', null, [
        'Authorization: Bearer ' . netikash_token(),
    ]);
    $data = isset($r['data']) && is_array($r['data']) ? $r['data'] : $r;
    if (!is_array($data)) throw new RuntimeException('Réponse de vérification invalide.');
    return $data;
}

/**
 * Validate the provider's response against the *stored* merchant attempt.
 * A redirect or a webhook body alone must never mark a payment as successful.
 */
function netikash_verified_state(array $attempt, array $data): string {
    $status = strtolower(trim((string)($data['status'] ?? '')));
    $allowed = ['requested', 'approved', 'completed', 'failed', 'expired', 'locked', 'canceled'];
    if (!in_array($status, $allowed, true)) throw new RuntimeException('Statut Netikash inconnu.');
    $reference = (string)($data['ref'] ?? $data['reference'] ?? '');
    // Some provider versions omit ref on GET; if supplied it must match.
    if ($reference !== '' && !hash_equals((string)$attempt['merchant_ref'], $reference)) {
        throw new RuntimeException('Référence du paiement différente de la commande.');
    }
    $currency = strtoupper((string)($data['currency'] ?? ''));
    if ($currency !== '' && $currency !== (string)$attempt['currency']) {
        throw new RuntimeException('Devise Netikash non conforme.');
    }
    if (array_key_exists('amount', $data)) {
        $amount = $data['amount'];
        if (!is_numeric($amount) || abs((float)$amount - (float)$attempt['amount']) > 0.005) {
            throw new RuntimeException('Montant Netikash non conforme.');
        }
    }
    return $status;
}

function netikash_order_state(string $providerState): string {
    return match ($providerState) {
        'completed' => 'paye',
        'approved' => 'autorise',
        'failed' => 'echoue',
        'expired' => 'expire',
        'canceled' => 'annule',
        'locked' => 'bloque',
        default => 'en_attente',
    };
}

/** DB writes are idempotent. Never downgrade a completed payment. */
function netikash_reconcile_verified(PDO $db, int $attemptId, array $verification): string {
    $db->beginTransaction();
    try {
        $s = $db->prepare("SELECT a.*,o.payment_status,o.status AS order_status
                           FROM payment_attempts a JOIN orders o ON o.id=a.order_id
                           WHERE a.id=? AND o.payment_method='netikash' FOR UPDATE");
        $s->execute([$attemptId]);
        $attempt = $s->fetch();
        if (!$attempt) throw new RuntimeException('Tentative de paiement introuvable.');
        $state = netikash_verified_state($attempt, $verification);
        $latest = $db->prepare('SELECT id FROM payment_attempts WHERE order_id=? ORDER BY id DESC LIMIT 1');
        $latest->execute([$attempt['order_id']]);
        if ((int)$latest->fetchColumn() !== $attemptId && $state !== 'completed') {
            // A delayed notification for an old attempt cannot overwrite a newer attempt.
            $db->commit();
            return (string)$attempt['payment_status'];
        }
        if ($attempt['payment_status'] === 'paye') {
            create_paid_invoice($db, (int)$attempt['order_id']);
            $db->commit();
            return 'paye';
        }
        // A provider may report stale requested after approved. Do not regress confirmed status.
        if ($attempt['status'] === 'approved' && $state === 'requested') {
            $db->commit();
            return 'autorise';
        }
        $db->prepare('UPDATE payment_attempts SET status=?,updated_at=NOW() WHERE id=?')->execute([$state, $attemptId]);
        $orderStatus = netikash_order_state($state);
        if ($orderStatus === 'paye') {
            $db->prepare('UPDATE orders SET payment_status=?, payment_reference=? WHERE id=?')
                ->execute([$orderStatus, $attempt['merchant_ref'], $attempt['order_id']]);
            create_paid_invoice($db, (int)$attempt['order_id']);
        } else {
            $db->prepare('UPDATE orders SET payment_status=? WHERE id=?')->execute([$orderStatus, $attempt['order_id']]);
        }
        if ($state === 'completed' && $attempt['order_status'] === 'annulee') {
            error_log('URGENT: Netikash completed for canceled order #' . $attempt['order_id'] . ' — refund/manual review required.');
        }
        $db->commit();
        return $orderStatus;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function netikash_sync_attempt(PDO $db, int $attemptId): string {
    $s = $db->prepare("SELECT a.* FROM payment_attempts a JOIN orders o ON o.id=a.order_id
                       WHERE a.id=? AND o.payment_method='netikash'");
    $s->execute([$attemptId]);
    $attempt = $s->fetch();
    if (!$attempt) throw new RuntimeException('Paiement introuvable.');
    if (empty($attempt['provider_id'])) throw new RuntimeException('Identifiant prestataire manquant : contactez le support.');
    $data = netikash_verify_request((string)$attempt['provider_id']);
    return netikash_reconcile_verified($db, $attemptId, $data);
}

/**
 * Start/restart one hosted checkout for an existing order. Only terminal provider states allow a new attempt.
 * A provider timeout is ambiguous: NO automatic retry to avoid a duplicate payment request.
 */
function netikash_start_checkout(PDO $db, int $orderId, int $userId): string {
    if (!netikash_ready()) throw new RuntimeException('Paiement Netikash indisponible ou non configuré.');
    $db->beginTransaction();
    try {
        $s = $db->prepare('SELECT * FROM orders WHERE id=? AND user_id=? FOR UPDATE');
        $s->execute([$orderId, $userId]);
        $order = $s->fetch();
        if (!$order || $order['payment_method'] !== 'netikash') throw new RuntimeException('Commande non éligible au paiement en ligne.');
        if ($order['status'] === 'annulee' || $order['status'] === 'livree') throw new RuntimeException('Cette commande est clôturée.');
        if ($order['payment_status'] === 'paye' || $order['payment_status'] === 'autorise') throw new RuntimeException('Paiement déjà confirmé ou en traitement.');
        $q = $db->prepare('SELECT * FROM payment_attempts WHERE order_id=? ORDER BY id DESC LIMIT 1 FOR UPDATE');
        $q->execute([$orderId]);
        $old = $q->fetch();
        if ($old && !in_array($old['status'], ['failed','expired','canceled'], true)) {
            $db->commit();
            if ($old['payment_url'] && netikash_request_id_from_link((string)$old['payment_url']) !== null) return $old['payment_url'];
            throw new RuntimeException('Demande de paiement déjà initiée : vérifiez le statut ou contactez le support avant de réessayer.');
        }
        $ref = 'DCN-' . $orderId . '-' . strtoupper(bin2hex(random_bytes(6)));
        $db->prepare("INSERT INTO payment_attempts(order_id,merchant_ref,amount,currency,status) VALUES(?,?,?,'USD','initiating')")
           ->execute([$orderId, $ref, $order['total']]);
        $attemptId = (int)$db->lastInsertId();
        $db->prepare('UPDATE orders SET payment_reference=?, payment_status=\'en_attente\' WHERE id=?')
           ->execute([$ref, $orderId]);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }

    try {
        $result = netikash_create_link($ref, (string)$order['total'], $orderId);
        $db->prepare("UPDATE payment_attempts SET provider_id=?,provider_transaction=?,payment_url=?,status='requested',updated_at=NOW() WHERE id=?")
           ->execute([$result['request_id'], $result['transaction'], $result['link'], $attemptId]);
        return $result['link'];
    } catch (Throwable $e) {
        // If Netikash accepted a request but the network response was lost, we MUST NOT recreate it blindly.
        $db->prepare("UPDATE payment_attempts SET status='unknown',updated_at=NOW() WHERE id=? AND status='initiating'")->execute([$attemptId]);
        error_log('Netikash checkout failed for order #' . $orderId . ': ' . $e->getMessage());
        throw new RuntimeException('Initialisation Netikash non confirmée. Ne payez pas deux fois ; contactez le support si le paiement a été initié.');
    }
}

function netikash_webhook_signature_ok(string $raw, string $signature, string $timestamp, string $secret, string $mode = 'raw_base64'): bool {
    if ($secret === '' || $signature === '' || $timestamp === '') return false;
    $ts = ctype_digit($timestamp) ? (int)$timestamp : strtotime($timestamp);
    if (!$ts) return false;
    if ($ts > 9999999999) $ts = (int)floor($ts / 1000); // milliseconds timestamp
    if (abs(time() - $ts) > 600) return false;
    if ($mode === 'timestamp_hex') {
        // The /examples page uses HMAC(timestamp + '.' + rawBody) with HEX output.
        $expected = hash_hmac('sha256', $timestamp . '.' . $raw, $secret);
        return hash_equals($expected, strtolower($signature));
    }
    // The /documentation webhook section uses HMAC(rawBody) with Base64 output.
    $expected = base64_encode(hash_hmac('sha256', $raw, $secret, true));
    return hash_equals($expected, $signature);
}
