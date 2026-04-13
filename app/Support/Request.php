<?php
declare(strict_types=1);

namespace App\Support;

final class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $body;
    private array $server;
    private array $files;
    private array $headers;
    private array $routeParams = [];
    private string $rawBody;

    public function __construct(
        string $method,
        string $path,
        array $query,
        array $body,
        array $server,
        array $files,
        array $headers,
        string $rawBody
    ) {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->body = $body;
        $this->server = $server;
        $this->files = $files;
        $this->headers = $headers;
        $this->rawBody = $rawBody;
    }

    public static function capture(): self
    {
        $rawBody = file_get_contents('php://input') ?: '';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $body = $_POST;

        $contentType = strtolower((string) ($headers['Content-Type'] ?? $headers['content-type'] ?? ''));
        if ($body === [] && str_contains($contentType, 'application/json') && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            $body = is_array($decoded) ? $decoded : [];
        }

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $path,
            $_GET,
            $body,
            $_SERVER,
            $_FILES,
            $headers,
            $rawBody
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function query(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function input(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return array_merge($this->query, $this->body);
        }

        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    public function header(string $key, ?string $default = null): ?string
    {
        return $this->headers[$key] ?? $this->headers[strtolower($key)] ?? $default;
    }

    public function expectsJson(): bool
    {
        $accept = strtolower((string) $this->header('Accept', ''));
        return str_starts_with($this->path, '/api/') || str_contains($accept, 'application/json');
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function ip(): string
    {
        $realIp = $this->server['HTTP_X_REAL_IP'] ?? null;
        if (is_string($realIp) && $realIp !== '' && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        return (string) ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function userAgent(): string
    {
        return (string) ($this->server['HTTP_USER_AGENT'] ?? 'unknown');
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function backUrl(string $fallback = '/'): string
    {
        $referer = $this->server['HTTP_REFERER'] ?? '';
        if (!is_string($referer) || $referer === '') {
            return $fallback;
        }

        $path = parse_url($referer, PHP_URL_PATH);
        return is_string($path) && $path !== '' ? $path : $fallback;
    }
}

