<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/bootstrap.php';
requireMethod('GET');

$user = currentUser();

jsonResponse([
    'success' => true,
    'authenticated' => $user !== null,
    'user' => $user,
]);
