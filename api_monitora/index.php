<?php

declare(strict_types=1);

/**
 * API de integração Monitora (pull) — Solvetask piloto_2.0
 *
 * Endpoints:
 *   GET /monitora/status
 *   GET /monitora/contratos
 *   GET /monitora/filas?contrato=ST-{id}
 *   GET /monitora/atendimentos?data_inicio=&data_fim=&contrato=&fila=&pagina=&por_pagina=
 *   GET /monitora/atendimentos/lote?data_inicio=&data_fim=&contrato=&fila=&pagina=&por_pagina=
 *   GET /monitora/atendimentos/{protocolo}
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('America/Fortaleza');

$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/src/MonitoraResponse.php';
require_once __DIR__ . '/src/MonitoraAuth.php';
require_once __DIR__ . '/src/MonitoraRepository.php';
require_once __DIR__ . '/src/MonitoraController.php';

try {
    require_once dirname(__DIR__) . '/view/cnf/conexao.php';

    if (!isset($PDO) || !($PDO instanceof PDO)) {
        MonitoraResponse::erro(503, 'INDISPONIVEL', 'Banco de dados indisponível.');
    }

    /** @var PDO $PDO */
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    (new MonitoraAuth($config))->validar();

    $segmentos = resolverSegmentosUri();
    $controller = new MonitoraController(new MonitoraRepository($PDO, $config), $config);
    $controller->despachar($segmentos);
} catch (Throwable $e) {
    MonitoraResponse::erro(500, 'ERRO_INTERNO', 'Falha interna ao processar a requisição.');
}

/** @return list<string> */
function resolverSegmentosUri(): array
{
    $uri = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    if ($base !== '' && strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }

    $uri = trim($uri, '/');
    if ($uri === '' || $uri === 'index.php') {
        return ['monitora', 'status'];
    }

    if (substr($uri, -strlen('index.php')) === 'index.php') {
        $uri = trim(substr($uri, 0, -strlen('index.php')), '/');
    }

    $segmentos = array_values(array_filter(explode('/', $uri), function ($p) {
        return $p !== '';
    }));

    return $segmentos;
}
