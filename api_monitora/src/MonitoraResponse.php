<?php

declare(strict_types=1);

final class MonitoraResponse
{
    /** @var bool */
    private static $capture = false;

    /** @var array{status: int, body: string}|null */
    private static $captured = null;

    public static function enableCapture(): void
    {
        self::$capture = true;
        self::$captured = null;
    }

    public static function disableCapture(): void
    {
        self::$capture = false;
        self::$captured = null;
    }

    /** @return array{status: int, body: string}|null */
    public static function getCaptured()
    {
        return self::$captured;
    }

    public static function json(array $payload, int $status = 200): void
    {
        if (self::$capture) {
            self::$captured = [
                'status' => $status,
                'body' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
            return;
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function erro(int $http, string $codigo, string $mensagem): void
    {
        self::json([
            'erro' => true,
            'codigo' => $codigo,
            'mensagem' => $mensagem,
        ], $http);
    }
}
