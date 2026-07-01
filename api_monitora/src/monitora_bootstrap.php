<?php

declare(strict_types=1);

require_once __DIR__ . '/MonitoraResponse.php';
require_once __DIR__ . '/MonitoraAuth.php';
require_once __DIR__ . '/MonitoraRepository.php';
require_once __DIR__ . '/MonitoraController.php';

/**
 * Executa a API internamente (sem HTTP) — usado pelo proxy de testes em doc.php.
 *
 * @param array<string, mixed> $config
 * @param array<string, scalar|null> $query
 * @return array{ok: bool, http: int, body: string}
 */
function monitoraExecutarInterno(PDO $pdo, array $config, string $token, string $path, array $query): array
{
    $path = trim($path, '/');
    if ($path === '' || strpos($path, '..') !== false) {
        return [
            'ok' => false,
            'http' => 400,
            'body' => json_encode([
                'erro' => true,
                'codigo' => 'PARAMETROS_INVALIDOS',
                'mensagem' => 'Caminho de teste inválido.',
            ], JSON_UNESCAPED_UNICODE),
        ];
    }

    $segmentos = array_values(array_filter(explode('/', $path), function ($part) {
        return $part !== '';
    }));

    $queryBackup = $_GET;
    $_GET = $query;
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;

    MonitoraResponse::enableCapture();

    try {
        (new MonitoraAuth($config))->validar();

        $controller = new MonitoraController(new MonitoraRepository($pdo, $config), $config);
        $controller->despachar($segmentos);

        $captured = MonitoraResponse::getCaptured();
        if ($captured === null) {
            return [
                'ok' => false,
                'http' => 500,
                'body' => json_encode([
                    'erro' => true,
                    'codigo' => 'ERRO_INTERNO',
                    'mensagem' => 'A API não retornou resposta.',
                ], JSON_UNESCAPED_UNICODE),
            ];
        }

        return [
            'ok' => $captured['status'] >= 200 && $captured['status'] < 300,
            'http' => $captured['status'],
            'body' => $captured['body'],
        ];
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'http' => 500,
            'body' => json_encode([
                'erro' => true,
                'codigo' => 'ERRO_INTERNO',
                'mensagem' => 'Falha interna ao processar a requisição.',
            ], JSON_UNESCAPED_UNICODE),
        ];
    } finally {
        MonitoraResponse::disableCapture();
        $_GET = $queryBackup;
    }
}
