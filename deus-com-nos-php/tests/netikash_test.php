<?php
declare(strict_types=1);
require __DIR__ . '/../payments/netikash.php';
function ok(bool $condition, string $name): void {
    if (!$condition) { fwrite(STDERR, "FAIL: $name\n"); exit(1); }
    echo "OK: $name\n";
}
ok(netikash_request_id_from_link('https://pay.netikash.com/payment-requests/abc123') === 'abc123','URL provider ID');
ok(netikash_request_id_from_link('https://pay.netikash.com.evil.org/payment-requests/abc123') === null,'No open redirect');
ok(netikash_request_id_from_link('http://pay.netikash.com/payment-requests/abc123') === null,'HTTPS only');
$secret='demo-test-not-real';$raw='{"ref":"DCN-7-AA","event":"payment.success"}';$stamp=(string)time();
$sig=base64_encode(hash_hmac('sha256',$raw,$secret,true));
ok(netikash_webhook_signature_ok($raw,$sig,$stamp,$secret),'Valid webhook signature');
ok(!netikash_webhook_signature_ok($raw,$sig,$stamp,'wrong-key'),'Wrong secret rejected');
ok(!netikash_webhook_signature_ok($raw,$sig,(string)(time()-800),$secret),'Replay timestamp rejected');
$h=hash_hmac('sha256',$stamp.'.'.$raw,$secret);
ok(netikash_webhook_signature_ok($raw,$h,$stamp,$secret,'timestamp_hex'),'Provider example alternate signature');
$attempt=['merchant_ref'=>'DCN-7-AA','currency'=>'USD','amount'=>'50.00'];
ok(netikash_verified_state($attempt,['status'=>'completed','ref'=>'DCN-7-AA','currency'=>'USD','amount'=>50])==='completed','Correct payment verified');
foreach ([['status'=>'completed','ref'=>'DCN-7-BB'],['status'=>'completed','currency'=>'CDF'],['status'=>'completed','amount'=>51]] as $data) {
    try { netikash_verified_state($attempt,$data); ok(false,'Mismatch rejected'); }
    catch(RuntimeException) { ok(true,'Mismatch rejected'); }
}
ok(netikash_order_state('approved')==='autorise' && netikash_order_state('completed')==='paye','Paid only on completed');
