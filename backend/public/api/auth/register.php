<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../src/bootstrap.php';
requireMethod('POST');

$data = readJsonBody();
$name = trim((string)($data['name'] ?? ''));
$email = strtolower(trim((string)($data['email'] ?? '')));
$password = (string)($data['password'] ?? '');

if ($name === '' || mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    jsonResponse([
        'success' => false,
        'message' => 'Nom, adresse e-mail valide et mot de passe de 8 caractères minimum requis.'
    ], 422);
}

try {
    $pdo = getPDO();
    $statement = $pdo->prepare(
        'INSERT INTO users (full_name, email, password_hash, role) VALUES (:name, :email, :password, "customer")'
    );
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    startAppSession();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$pdo->lastInsertId(),
        'name' => $name,
        'email' => $email,
        'role' => 'customer',
    ];

    jsonResponse(['success' => true, 'message' => 'Compte créé avec succès.', 'user' => $_SESSION['user']], 201);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        jsonResponse(['success' => false, 'message' => 'Cette adresse e-mail est déjà utilisée.'], 409);
    }
    error_log($exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'Impossible de créer le compte.'], 500);
}
