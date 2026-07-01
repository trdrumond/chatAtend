<?php

declare(strict_types=1);

date_default_timezone_set('America/Fortaleza');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

$tokenConfigurado = trim((string) ($config['token'] ?? '')) !== ''
    && $config['token'] !== 'solvetask-monitora-altere-este-token';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$apiBase = $scheme . '://' . $host . $basePath;
$docUrl = $apiBase . '/doc.php';
$hoje = date('Y-m-d');
$mesInicio = date('Y-m-01');

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function monitoraDocTokenSalvo(): string
{
    return trim((string) ($_SESSION['monitora_doc_token'] ?? ''));
}

if (($_GET['action'] ?? '') === 'proxy') {
    header('Content-Type: application/json; charset=utf-8');

    $token = monitoraDocTokenSalvo();
    if ($token === '') {
        http_response_code(400);
        echo json_encode([
            'erro' => true,
            'codigo' => 'PARAMETROS_INVALIDOS',
            'mensagem' => 'Token não salvo. Informe e clique em Salvar token antes de testar.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $path = trim((string) ($_GET['path'] ?? 'monitora/status'), '/');
    $query = $_GET;
    unset($query['action'], $query['path']);

    require_once dirname(__DIR__) . '/view/cnf/conexao.php';
    require_once __DIR__ . '/src/monitora_bootstrap.php';

    if (!isset($PDO) || !($PDO instanceof PDO)) {
        http_response_code(503);
        echo json_encode([
            'erro' => true,
            'codigo' => 'INDISPONIVEL',
            'mensagem' => 'Banco de dados indisponível.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** @var PDO $PDO */
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $resultado = monitoraExecutarInterno($PDO, $config, $token, $path, $query);

    http_response_code($resultado['http']);
    echo $resultado['body'];
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save_token') {
        $_SESSION['monitora_doc_token'] = trim((string) ($_POST['token'] ?? ''));
        header('Location: ' . $docUrl . '?msg=token_salvo');
        exit;
    }

    if ($action === 'clear_token') {
        unset($_SESSION['monitora_doc_token']);
        header('Location: ' . $docUrl . '?msg=token_removido');
        exit;
    }
}

$tokenTesteSalvo = monitoraDocTokenSalvo();
$temTokenTeste = $tokenTesteSalvo !== '';
$flashMsg = '';
$flashTipo = 'info';

if (($_GET['msg'] ?? '') === 'token_salvo') {
    $flashMsg = 'Token salvo com sucesso na sessão PHP. Os testes abaixo usarão esse token automaticamente.';
    $flashTipo = 'ok';
} elseif (($_GET['msg'] ?? '') === 'token_removido') {
    $flashMsg = 'Token de teste removido da sessão.';
    $flashTipo = 'info';
}

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Monitora — Documentação e Testes | Solvetask</title>
    <link rel="stylesheet" href="assets/monitora-doc.css">
</head>
<body>
<div class="page">
    <header class="hero">
        <h1>API Monitora — Solvetask</h1>
        <p>
            Contrato de integração <strong>pull</strong> para o sistema Monitora.
            O Monitora consulta estes endpoints para sincronizar contratos, filas e importar atendimentos com histórico de chat.
        </p>
        <div class="badges">
            <span class="badge">v<?= h((string) ($config['versao'] ?? '1.0.0')) ?></span>
            <span class="badge">Auth: <?= h(strtoupper((string) ($config['auth_mode'] ?? 'bearer'))) ?></span>
            <span class="badge <?= $tokenConfigurado ? 'ok' : 'warn' ?>">
                Token <?= $tokenConfigurado ? 'configurado' : 'pendente (config.local.php)' ?>
            </span>
        </div>
        <div class="flow">
            <span class="flow-step">Monitora</span>
            <span class="flow-arrow">→</span>
            <span class="flow-step">GET /monitora/*</span>
            <span class="flow-arrow">→</span>
            <span class="flow-step">Solvetask (piloto_2.0)</span>
        </div>
    </header>

    <div class="grid-2">
        <section class="card">
            <div class="card-header"><h2>Informações de conexão</h2></div>
            <div class="card-body">
                <div class="alert alert-info">
                    Cadastre no Monitora em <strong>Sistemas Integrados</strong> a URL base abaixo e o mesmo token definido em <code>config.local.php</code>.
                </div>
                <table class="doc-table">
                    <tbody>
                    <tr>
                        <th>URL base</th>
                        <td><code><?= h($apiBase) ?></code></td>
                    </tr>
                    <tr>
                        <th>Sistema</th>
                        <td><?= h((string) ($config['sistema'] ?? 'Solvetask')) ?></td>
                    </tr>
                    <tr>
                        <th>Prefixo contrato</th>
                        <td><code><?= h((string) ($config['contrato_prefixo'] ?? 'ST')) ?>-{id}</code></td>
                    </tr>
                    <tr>
                        <th>Paginação padrão</th>
                        <td><?= (int) ($config['por_pagina_padrao'] ?? 100) ?> (máx. <?= (int) ($config['por_pagina_maximo'] ?? 500) ?>)</td>
                    </tr>
                    <tr>
                        <th>Fuso horário</th>
                        <td><?= h((string) ($config['timezone'] ?? 'America/Fortaleza')) ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h2>Autenticação</h2></div>
            <div class="card-body">
                <p>Todas as requisições à API exigem token no header:</p>
                <pre class="code-block">Authorization: Bearer &lt;token&gt;
Accept: application/json</pre>
                <p style="margin-top:12px;font-size:0.9rem;color:var(--c-muted);">
                    Alternativa aceita: <code>X-Api-Key: &lt;token&gt;</code> (se <code>auth_mode</code> permitir).
                </p>
                <?php if ($flashMsg !== ''): ?>
                    <div class="alert alert-<?= h($flashTipo) ?>" id="flash-msg"><?= h($flashMsg) ?></div>
                <?php endif; ?>

                <form method="post" action="doc.php" id="form-token" class="token-form">
                    <div class="field" style="margin-top:16px;">
                        <label for="token">Token para testes</label>
                        <input
                            type="password"
                            name="token"
                            id="token"
                            value="<?= h($tokenTesteSalvo) ?>"
                            placeholder="Cole o token configurado em config.local.php"
                            autocomplete="off"
                        >
                    </div>
                    <p id="token-status" class="token-status <?= $temTokenTeste ? 'is-saved' : '' ?>">
                        <?= $temTokenTeste
                            ? 'Token salvo na sessão (' . h(str_repeat('•', min(12, strlen($tokenTesteSalvo)))) . ')'
                            : 'Nenhum token salvo ainda' ?>
                    </p>
                    <div class="btn-group">
                        <button type="submit" name="action" value="save_token" class="btn btn-primary" id="btn-save-token">
                            Salvar token
                        </button>
                        <button type="submit" name="action" value="clear_token" class="btn btn-secondary" id="btn-clear-token">
                            Limpar
                        </button>
                        <label class="btn-toggle">
                            <input type="checkbox" id="token-visible">
                            Mostrar token
                        </label>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <section class="card" style="margin-top:20px;">
        <div class="card-header"><h2>Endpoints</h2></div>
        <div class="card-body" style="padding:0;">
            <table class="doc-table">
                <thead>
                <tr>
                    <th>Método</th>
                    <th>Endpoint</th>
                    <th>Descrição</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><span class="method">GET</span></td>
                    <td><code>/monitora/status</code></td>
                    <td>Health check — botão "Testar conexão" no Monitora</td>
                </tr>
                <tr>
                    <td><span class="method">GET</span></td>
                    <td><code>/monitora/contratos</code></td>
                    <td>Lista contratos ativos (<code>ST-{id}</code>)</td>
                </tr>
                <tr>
                    <td><span class="method">GET</span></td>
                    <td><code>/monitora/filas?contrato=ST-3</code></td>
                    <td>Filas vinculadas ao contrato</td>
                </tr>
                <tr>
                    <td><span class="method">GET</span></td>
                    <td><code>/monitora/atendimentos?...</code></td>
                    <td>Listagem resumida por período (<code>data_inicio</code>, <code>data_fim</code>, <code>contrato</code>, opcional <code>fila</code>)</td>
                </tr>
                <tr>
                    <td><span class="method">GET</span></td>
                    <td><code>/monitora/atendimentos/{protocolo}</code></td>
                    <td>Detalhe completo com <code>mensagens[]</code> (log de chat)</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card" style="margin-top:20px;">
        <div class="card-header">
            <h2>Console de testes</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-warn">
                Esta página é apenas para documentação e homologação. Em produção, restrinja o acesso ou remova <code>doc.php</code>.
            </div>

            <div class="endpoint-tabs">
                <button type="button" class="tab active" data-endpoint="status">Status</button>
                <button type="button" class="tab" data-endpoint="contratos">Contratos</button>
                <button type="button" class="tab" data-endpoint="filas">Filas</button>
                <button type="button" class="tab" data-endpoint="atendimentos">Atendimentos</button>
                <button type="button" class="tab" data-endpoint="detalhe">Detalhe</button>
            </div>

            <p style="margin:0 0 14px;font-size:0.9rem;">
                Endpoint selecionado: <strong id="endpoint-label">GET /monitora/status</strong>
            </p>

            <div class="endpoint-panel" data-endpoint="status">
                <p style="color:var(--c-muted);font-size:0.9rem;margin:0;">Sem parâmetros adicionais.</p>
            </div>

            <div class="endpoint-panel" data-endpoint="contratos" style="display:none;">
                <p style="color:var(--c-muted);font-size:0.9rem;margin:0;">Retorna todos os contratos ativos do Solvetask.</p>
            </div>

            <div class="endpoint-panel" data-endpoint="filas" style="display:none;">
                <div class="field">
                    <label for="param-contrato-filas">contrato *</label>
                    <input type="text" id="param-contrato-filas" data-sync-curl placeholder="Ex.: ST-3" value="ST-3">
                </div>
            </div>

            <div class="endpoint-panel" data-endpoint="atendimentos" style="display:none;">
                <div class="field-row">
                    <div class="field">
                        <label for="param-data-inicio">data_inicio *</label>
                        <input type="date" id="param-data-inicio" data-sync-curl value="<?= h($mesInicio) ?>">
                    </div>
                    <div class="field">
                        <label for="param-data-fim">data_fim *</label>
                        <input type="date" id="param-data-fim" data-sync-curl value="<?= h($hoje) ?>">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="param-contrato">contrato *</label>
                        <input type="text" id="param-contrato" data-sync-curl placeholder="Ex.: ST-3" value="ST-3">
                    </div>
                    <div class="field">
                        <label for="param-fila">fila (opcional)</label>
                        <input type="text" id="param-fila" data-sync-curl placeholder="Ex.: 5">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label for="param-pagina">pagina</label>
                        <input type="number" id="param-pagina" data-sync-curl value="1" min="1">
                    </div>
                    <div class="field">
                        <label for="param-por-pagina">por_pagina</label>
                        <input type="number" id="param-por-pagina" data-sync-curl value="10" min="1" max="500">
                    </div>
                </div>
            </div>

            <div class="endpoint-panel" data-endpoint="detalhe" style="display:none;">
                <div class="field">
                    <label for="param-protocolo">protocolo *</label>
                    <input type="text" id="param-protocolo" data-sync-curl placeholder="Ex.: 2501099641212">
                </div>
            </div>

            <div class="btn-group" style="margin-top:18px;">
                <button type="button" class="btn btn-primary" id="btn-run">Executar teste</button>
                <button type="button" class="btn btn-secondary" id="btn-run-all">Rodar sequência (status → contratos → filas → atendimentos)</button>
                <button type="button" class="btn btn-secondary" id="btn-clear">Limpar resultado</button>
            </div>

            <div style="margin-top:24px;">
                <h3 style="margin:0 0 8px;font-size:0.95rem;">Comando cURL</h3>
                <pre class="code-block" id="curl-preview">curl -s "..."</pre>
            </div>

            <div style="margin-top:24px;">
                <h3 style="margin:0 0 8px;font-size:0.95rem;">Resposta</h3>
                <div id="result-meta" class="result-meta"></div>
                <div class="result-panel empty">
                    <pre class="code-block" id="result-body">Execute um teste para ver a resposta JSON aqui.</pre>
                </div>
            </div>
        </div>
    </section>

    <section class="card" style="margin-top:20px;">
        <div class="card-header"><h2>Mapeamento Solvetask → Monitora</h2></div>
        <div class="card-body" style="padding:0;">
            <table class="doc-table">
                <thead>
                <tr>
                    <th>Campo API</th>
                    <th>Origem no banco</th>
                </tr>
                </thead>
                <tbody>
                <tr><td><code>protocolo</code></td><td><code>tbl_chat_fila_secondary.protocolo</code></td></tr>
                <tr><td><code>contrato</code></td><td><code>ST-{tbl_contrato.id_contrato}</code></td></tr>
                <tr><td><code>fila</code></td><td><code>tbl_config_fila.id_fila</code></td></tr>
                <tr><td><code>operador</code></td><td><code>tbl_user</code> (bko_resp)</td></tr>
                <tr><td><code>data_fim</code></td><td><code>hora_fim</code> — filtro do período de importação</td></tr>
                <tr><td><code>mensagens[]</code></td><td><code>tbl_chat_msg_secondary</code> / <code>tbl_chat_msg</code></td></tr>
                <tr><td><code>metricas.csat</code></td><td><code>tbl_classificacao.star</code> (1–5)</td></tr>
                <tr><td><code>canal</code></td><td>Fixo: <code>chat</code></td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card" style="margin-top:20px;">
        <div class="card-header"><h2>Códigos de erro</h2></div>
        <div class="card-body" style="padding:0;">
            <table class="doc-table">
                <thead>
                <tr><th>HTTP</th><th>Código</th><th>Situação</th></tr>
                </thead>
                <tbody>
                <tr><td>400</td><td><code>PARAMETROS_INVALIDOS</code></td><td>Datas ou parâmetros inválidos</td></tr>
                <tr><td>401</td><td><code>NAO_AUTENTICADO</code></td><td>Token ausente ou inválido</td></tr>
                <tr><td>403</td><td><code>SEM_PERMISSAO</code></td><td>Fila não pertence ao contrato</td></tr>
                <tr><td>404</td><td><code>NAO_ENCONTRADO</code></td><td>Contrato ou protocolo inexistente</td></tr>
                <tr><td>500</td><td><code>ERRO_INTERNO</code></td><td>Falha interna</td></tr>
                <tr><td>503</td><td><code>INDISPONIVEL</code></td><td>Token ou banco não configurado</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <p class="footer-note">
        Documentação baseada em <code>API_INTEGRACAO_PADRAO.md</code> (Monitora v1.0) —
        <a href="<?= h($docUrl) ?>"><?= h($docUrl) ?></a>
    </p>
</div>

<script>
window.MONITORA_DOC = {
    apiBase: <?= json_encode($apiBase, JSON_UNESCAPED_SLASHES) ?>,
    proxyUrl: <?= json_encode($docUrl . '?action=proxy', JSON_UNESCAPED_SLASHES) ?>,
    hasToken: <?= $temTokenTeste ? 'true' : 'false' ?>
};
</script>
<script src="assets/monitora-doc.js?v=3"></script>
</body>
</html>
