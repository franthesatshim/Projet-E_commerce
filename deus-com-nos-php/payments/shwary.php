<?php
declare(strict_types=1);

require_once __DIR__ . '/../invoices.php';

const SHWARY_API = 'https://api.shwary.com/api/v1/merchants';
const SHWARY_RETRY_STATES = ['failed', 'cancelled', 'rejected'];

function shwary_callback_base(): string {
    return rtrim((string)getenv('APP_URL'), '/') . '/shwary_webhook.php';
}

function shwary_ready(): bool {
    $url = parse_url(shwary_callback_base());
    $rate = filter_var(getenv('SHWARY_CDF_PER_USD'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
    return getenv('SHWARY_ENABLED') === '1'
        && extension_loaded('curl')
        && trim((string)getenv('SHWARY_MERCHANT_ID')) !== ''
        && trim((string)getenv('SHWARY_MERCHANT_KEY')) !== ''
        && $rate !== false
        && ($url['scheme'] ?? '') === 'https'
        && !empty($url['host'])
        && !isset($url['user']) && !isset($url['pass'])
        && !isset($url['query']) && !isset($url['fragment']);
}

function shwary_sandbox(): bool {
    return getenv('SHWARY_SANDBOX') !== '0';
}

function shwary_quote(string $usd): ?int {
    if (!shwary_ready()) return null;
    $cdf = (int)round((float)$usd * (int)getenv('SHWARY_CDF_PER_USD'), 0, PHP_ROUND_HALF_UP);
    return $cdf > 2900 && $cdf <= 9999999999 ? $cdf : null;
}

function shwary_phone(string $phone): string {
    $phone = preg_replace('/[() .-]/', '', $phone);
    if (preg_match('/^0[0-9]{9}$/D', $phone)) $phone = '+243' . substr($phone, 1);
    if (!preg_match('/^\+243[0-9]{9}$/D', $phone)) {
        throw new RuntimeException('Utilisez le numéro Mobile Money congolais au format +243XXXXXXXXX ou 0XXXXXXXXX.');
    }
    return $phone;
}

function shwary_uuid(mixed $value): bool {
    return is_string($value) && preg_match('/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/iD', $value) === 1;
}

function shwary_callback_token(string $reference): string {
    return hash_hmac('sha256', 'deus-shwary-callback:' . $reference, (string)getenv('SHWARY_MERCHANT_KEY'));
}

/** @return array<string,mixed> */
function shwary_http(string $path, ?array $payload = null): array {
    $id = trim((string)getenv('SHWARY_MERCHANT_ID'));
    $key = trim((string)getenv('SHWARY_MERCHANT_KEY'));
    if (!extension_loaded('curl') || $id === '' || $key === '') throw new RuntimeException('Shwary indisponible.');
    $ch = curl_init(SHWARY_API . $path);
    if ($ch === false) throw new RuntimeException('Client HTTP indisponible.');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 7,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/json', 'x-merchant-id: ' . $id, 'x-merchant-key: ' . $key],
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
    }
    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $status === 0 || strlen($body) > 262144) {
        throw new RuntimeException('Réponse Shwary indisponible.');
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('Requête Shwary refusée (HTTP ' . $status . ').', $status);
    }
    $data = json_decode($body, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($data)) throw new RuntimeException('Réponse Shwary invalide.');
    return $data;
}

function shwary_verified_state(array $attempt, array $data): string {
    $status = $data['status'] ?? '';
    $id = $data['id'] ?? null;
    $amount = $data['amount'] ?? null;
    if (!shwary_uuid($id) || !hash_equals((string)$attempt['provider_id'], $id)
        || !is_numeric($amount) || (float)$amount !== (float)$attempt['amount']
        || ($data['currency'] ?? null) !== $attempt['currency']
        || ($data['type'] ?? null) !== 'deposit'
        || ($data['recipientPhoneNumber'] ?? null) !== $attempt['phone']
        || ($data['isSandbox'] ?? null) !== (bool)$attempt['is_sandbox']
        || !in_array($status, ['pending', 'submitted', 'completed', 'failed', 'cancelled'], true)) {
        throw new RuntimeException('La transaction Shwary ne correspond pas à cette commande.');
    }
    return $status;
}

function shwary_sync_attempt(PDO $db, int $attemptId): string {
    $lookup = $db->prepare("SELECT a.provider_id FROM payment_attempts a JOIN orders o ON o.id=a.order_id WHERE a.id=? AND o.payment_method='shwary'");
    $lookup->execute([$attemptId]);
    $id = $lookup->fetchColumn();
    if (!shwary_uuid($id)) throw new RuntimeException('Aucune transaction Shwary vérifiable pour le moment.');
    return shwary_reconcile($db, $attemptId, shwary_http('/transactions/' . rawurlencode($id)));
}

function shwary_reconcile(PDO $db, int $attemptId, array $data): string {
    $lookup = $db->prepare('SELECT order_id FROM payment_attempts WHERE id=?');
    $lookup->execute([$attemptId]);
    $orderId = (int)$lookup->fetchColumn();
    $db->beginTransaction();
    try {
        // Use the same lock order as checkout and administration.
        $q = $db->prepare("SELECT payment_status, status, phone FROM orders WHERE id=? AND payment_method='shwary' FOR UPDATE");
        $q->execute([$orderId]);
        $order = $q->fetch();
        if (!$order) throw new RuntimeException('Commande Shwary introuvable.');
        $q = $db->prepare('SELECT * FROM payment_attempts WHERE id=? FOR UPDATE');
        $q->execute([$attemptId]);
        $attempt = $q->fetch();
        if (!$attempt) throw new RuntimeException('Transaction Shwary introuvable.');
        $state = shwary_verified_state($attempt + ['phone' => $order['phone']], $data);
        $latest = $db->prepare('SELECT id FROM payment_attempts WHERE order_id=? ORDER BY id DESC LIMIT 1');
        $latest->execute([$orderId]);
        $isLatest = (int)$latest->fetchColumn() === $attemptId;
        $orderState = (string)$order['payment_status'];

        // A stale provider read must not undo a terminal state or regress a submitted payment.
        $advance = $attempt['status'] !== 'completed'
            && ($state === 'completed' || !in_array($attempt['status'], SHWARY_RETRY_STATES, true))
            && !($attempt['status'] === 'submitted' && $state === 'pending');
        if ($advance) {
            $db->prepare('UPDATE payment_attempts SET status=? WHERE id=?')->execute([$state, $attemptId]);
        }
        if ($state === 'completed' && !(bool)$attempt['is_sandbox']) {
            if ($orderState === 'paye' && $attempt['status'] !== 'completed') {
                error_log('Shwary : deuxième paiement confirmé pour la commande #' . $orderId);
            }
            if ($orderState !== 'paye') {
                $db->prepare("UPDATE orders SET payment_status='paye', payment_reference=? WHERE id=?")
                    ->execute([$attempt['merchant_ref'], $orderId]);
                if ($order['status'] === 'annulee') error_log('Shwary : paiement confirmé après annulation de la commande #' . $orderId);
            }
            create_paid_invoice($db, $orderId);
            $orderState = 'paye';
        } elseif ($advance && $isLatest && $orderState !== 'paye') {
            $orderState = match ($state) {
                'completed' => 'simule',
                'failed' => 'echoue',
                'cancelled' => 'annule',
                default => 'en_attente',
            };
            $db->prepare('UPDATE orders SET payment_status=? WHERE id=?')->execute([$orderState, $orderId]);
        }
        $db->commit();
        return $orderState;
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }
}

function shwary_start(PDO $db, int $orderId, int $userId, int $quotedAmount, bool $sandbox): string {
    if (!shwary_ready()) throw new RuntimeException('Shwary est actuellement indisponible.');
    if ($sandbox !== shwary_sandbox()) throw new RuntimeException('Le mode de paiement Shwary a changé. Actualisez la commande.');
    $db->beginTransaction();
    try {
        $q = $db->prepare("SELECT * FROM orders WHERE id=? AND user_id=? AND payment_method='shwary' FOR UPDATE");
        $q->execute([$orderId, $userId]);
        $order = $q->fetch();
        if (!$order || in_array($order['status'], ['annulee', 'livree'], true)
            || in_array($order['payment_status'], ['paye', 'simule'], true)) {
            throw new RuntimeException('Cette commande ne peut pas faire l’objet d’un nouveau paiement.');
        }
        $q = $db->prepare('SELECT status FROM payment_attempts WHERE order_id=? ORDER BY id DESC LIMIT 1 FOR UPDATE');
        $q->execute([$orderId]);
        $previous = $q->fetchColumn();
        if ($previous !== false && !in_array($previous, SHWARY_RETRY_STATES, true)) {
            throw new RuntimeException('Une demande existe déjà. Vérifiez son état dans le détail de la commande.');
        }
        $amount = shwary_quote((string)$order['total']);
        if ($amount === null || $amount !== $quotedAmount) {
            throw new RuntimeException('Le montant Shwary a changé ou est indisponible. Vérifiez le total avant de réessayer.');
        }
        $ref = 'SW-' . bin2hex(random_bytes(16));
        $db->prepare("INSERT INTO payment_attempts(order_id,merchant_ref,amount,currency,is_sandbox,status) VALUES(?,?,?,'CDF',?,'initiating')")
            ->execute([$orderId, $ref, $amount, (int)$sandbox]);
        $attemptId = (int)$db->lastInsertId();
        $db->prepare("UPDATE orders SET payment_reference=?,payment_status='en_attente' WHERE id=?")
            ->execute([$ref, $orderId]);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        throw $e;
    }

    try {
        $path = $sandbox ? '/payment/sandbox/DRC' : '/payment/DRC';
        $data = shwary_http($path, [
            'amount' => $amount,
            'clientPhoneNumber' => $order['phone'],
            'callbackUrl' => shwary_callback_base() . '?' . http_build_query([
                'ref' => $ref, 'token' => shwary_callback_token($ref),
            ]),
        ]);
        if (!shwary_uuid($data['id'] ?? null)) throw new RuntimeException('Identifiant Shwary absent.');
        // Store the transaction ID before attempting a fresh authenticated read.
        $db->prepare("UPDATE payment_attempts SET provider_id=?, status='pending' WHERE id=?")
            ->execute([$data['id'], $attemptId]);
    } catch (Throwable $e) {
        $rejected = in_array($e->getCode(), [400, 401, 403, 404, 422], true);
        $db->prepare("UPDATE payment_attempts SET status=? WHERE id=? AND status='initiating'")
            ->execute([$rejected ? 'rejected' : 'unknown', $attemptId]);
        error_log('Shwary : initialisation commande #' . $orderId . ' : ' . $e->getMessage());
        throw new RuntimeException($rejected
            ? 'Shwary a refusé la demande. Vérifiez le numéro et les paramètres de paiement.'
            : 'Envoi Shwary non confirmé. Contactez la boutique avant de retenter un paiement.');
    }

    // Covers immediate completion and callbacks that arrived before the ID was stored.
    try {
        return shwary_sync_attempt($db, $attemptId);
    } catch (Throwable $e) {
        error_log('Shwary : première vérification de la commande #' . $orderId . ' : ' . $e->getMessage());
        return 'en_attente';
    }
}
