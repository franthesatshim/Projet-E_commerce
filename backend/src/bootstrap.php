<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/auth.php';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
