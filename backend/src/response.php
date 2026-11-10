<?php
declare(strict_types=1);

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        jsonResponse(['success' => false, 'message' => 'Corps JSON invalide.'], 400);
    }
    return $data;
}

function requireMethod(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        header('Allow: ' . strtoupper($method));
        jsonResponse(['success' => false, 'message' => 'Méthode HTTP non autorisée.'], 405);
    }
}
