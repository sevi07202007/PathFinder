<?php
declare(strict_types=1);

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonBody(): array
{
    $body = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($body)) {
        jsonResponse(['success' => false, 'message' => 'A valid JSON request body is required.'], 400);
    }
    return $body;
}

function requestMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        header('Allow: ' . $method);
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function serverError(Throwable $exception): never
{
    error_log('PathFinder API: ' . $exception->getMessage());
    jsonResponse(['success' => false, 'message' => 'The request could not be completed.'], 500);
}
