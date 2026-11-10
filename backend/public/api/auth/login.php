<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/bootstrap.php';
requireMethod('POST');

$data = readJsonBody();
$email = strtolower(trim((string)($data['email'] ?? '')));
$password = (string)($data['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    jsonResponse(['success' => false, 'message' => 'E-mail et mot de passe requis.'], 422);
}

try {
    $statement = getPDO()->prepare(
        'SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'message' => 'Identifiants incorrects.'], 401);
    }

    startAppSession();
    session_regenerate_id(true);
    unset($user['password_hash']);
    $user['id'] = (int)$user['id'];
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    jsonResponse(['success' => true, 'message' => 'Connexion réussie.', 'user' => $_SESSION['user']]);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'Erreur lors de la connexion.'], 500);
}
