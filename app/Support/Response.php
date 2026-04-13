<?php
declare(strict_types=1);

namespace App\Support;

final class Response
{
    public function __construct(
        private string $body,
        private int $status = 200,
        private array $headers = []
    ) {
    }

    public static function view(string $view, array $data = [], int $status = 200): self
    {
        return new self(View::render($view, $data), $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        return new self(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            $status,
            array_merge(['Content-Type' => 'application/json; charset=UTF-8'], $headers)
        );
    }

    public static function apiSuccess(array $data = [], int $status = 200, array $meta = []): self
    {
        return self::json([
            'success' => true,
            'data' => $data,
            'error' => null,
            'meta' => array_merge(['request_id' => request_id()], $meta),
        ], $status);
    }

    public static function apiError(string $code, string $message, int $status = 400, array $details = []): self
    {
        return self::json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => [
                'request_id' => request_id(),
            ],
        ], $status);
    }

    public static function redirect(string $path, int $status = 302): self
    {
        return new self('', $status, ['Location' => $path]);
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}

