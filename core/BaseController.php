<?php

declare(strict_types=1);

namespace Core;

/**
 * Controlador base. Orquesta peticiones y delega lógica en Models/Services.
 */
abstract class BaseController
{
    protected function view(string $viewName, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $path = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $viewName) . '.php';
        if (!is_file($path)) {
            throw new \RuntimeException("Vista no encontrada: $viewName");
        }
        require $path;
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
