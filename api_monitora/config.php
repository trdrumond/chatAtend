<?php

declare(strict_types=1);

/**
 * Configuração da API Monitora (padrão pull — doc/API_INTEGRACAO_PADRAO.md).
 *
 * Sobrescreva em config.local.php (não versionado) ou via variável de ambiente MONITORA_API_TOKEN.
 */
$config = [
    'token' => getenv('MONITORA_API_TOKEN') ?: 'solvetask-monitora-altere-este-token',
    'auth_mode' => 'both', // bearer | api_key | both (recomendado: both para Apache/XAMPP)
    'sistema' => 'Solvetask',
    'integracao' => 'monitora',
    'versao' => '1.0.0',
    'timezone' => 'America/Fortaleza',
    'contrato_prefixo' => 'ST',
    'por_pagina_padrao' => 500,
    'por_pagina_maximo' => 1000,
];

$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    $override = require $local;
    if (is_array($override)) {
        $config = array_merge($config, $override);
    }
}

return $config;
