<?php

declare(strict_types=1);

namespace Core;

/**
 * Router frontal. Resuelve URI a controlador y acción.
 */
class Router
{
    private const DEFAULT_CONTROLLER = 'Home';
    private const DEFAULT_ACTION     = 'index';

    private string $controllerName = self::DEFAULT_CONTROLLER;
    private string $actionName     = self::DEFAULT_ACTION;
    private array  $params         = [];

    public function dispatch(): void
    {
        $uri = $this->getRequestUri();
        $segments = $this->parseUri($uri);

        if (count($segments) > 0 && $segments[0] !== '') {
            $this->controllerName = $this->toControllerName($segments[0]);
        }
        if (count($segments) > 1 && $segments[1] !== '') {
            $this->actionName = $this->toActionName($segments[1]);
        }
        if (count($segments) > 2) {
            $this->params = array_slice($segments, 2);
        }

        $controller = $this->instantiateController();
        $this->invokeAction($controller);
    }

    private function getRequestUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $queryPos = strpos($uri, '?');
        if ($queryPos !== false) {
            $uri = substr($uri, 0, $queryPos);
        }
        $uri = rawurldecode($uri);
        $base = $this->getBasePath();
        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }
        return $uri ?: '/';
    }

    private function getBasePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        return str_replace('\\', '/', dirname($script));
    }

    private function parseUri(string $uri): array
    {
        $uri = trim($uri, '/');
        if ($uri === '') {
            return [];
        }
        return array_values(array_filter(explode('/', $uri), static fn(string $s): bool => $s !== ''));
    }

    private function toControllerName(string $segment): string
    {
        $name = str_replace(['-', '_'], ' ', $segment);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name ?: self::DEFAULT_CONTROLLER;
    }

    private function toActionName(string $segment): string
    {
        $name = str_replace(['-', '_'], ' ', $segment);
        $name = lcfirst(str_replace(' ', '', ucwords($name)));
        return $name ?: self::DEFAULT_ACTION;
    }

    private function instantiateController(): BaseController
    {
        $class = 'App\\Controllers\\' . $this->controllerName . 'Controller';
        if (!class_exists($class)) {
            $this->sendNotFound();
        }
        $controller = new $class();
        if (!$controller instanceof BaseController) {
            throw new \RuntimeException("El controlador $class debe extender Core\\BaseController.");
        }
        return $controller;
    }

    private function invokeAction(BaseController $controller): void
    {
        $method = $this->actionName;
        if (!method_exists($controller, $method)) {
            $this->sendNotFound();
        }
        $controller->$method(...$this->params);
    }

    private function sendNotFound(): void
    {
        http_response_code(404);
        if (function_exists('header')) {
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404</title></head><body><h1>Recurso no encontrado</h1></body></html>';
        exit;
    }
}
