<?php
class ApiResponse
{
    public static function json(bool $success, int $statusCode, string $message, array $data = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
        ], $data));
        exit;
    }
}
