<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function currentUser(): ?array
{
    startAppSession();
    return isset($_SESSION['user']) && is_array($_SESSION['user'])
        ? $_SESSION['user']
        : null;
}

function requireAuth(): array
{
    $user = currentUser();
    if ($user === null) {
        jsonResponse(['success' => false, 'message' => 'Authentification requise.'], 401);
    }
    return $user;
}

function requireAdmin(): array
{
    $user = requireAuth();
    if (($user['role'] ?? '') !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Accès réservé à l’administration.'], 403);
    }
    return $user;
}
