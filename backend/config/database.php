<?php
declare(strict_types=1);

/**
 * Connexion PDO à MySQL/MariaDB.
 * Les identifiants sont lus depuis les variables d'environnement.
 * Pour XAMPP en développement local uniquement : DB_HOST=127.0.0.1,
 * DB_NAME=ecommerce_db, DB_USER=root et DB_PASS vide.
 */
function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $name = getenv('DB_NAME') ?: 'ecommerce_db';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS');
    $pass = $pass === false ? '' : $pass;
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());
        throw new RuntimeException('Connexion à la base de données impossible.');
    }
}
