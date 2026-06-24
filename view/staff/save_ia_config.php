<?php
/**
 * Salva chave OpenAI em tbl_config_sis (master/admin).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/st_ia_analytics.php';

$nivelUsu = (int) ($infoUser['nivel_id'] ?? 99);
if ($nivelUsu > 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acesso negado']);
    exit;
}

if (!stIaColumnExists($PDO, 'tbl_config_sis', 'openai_api_key')) {
    echo json_encode([
        'ok' => false,
        'error' => 'Campo openai_api_key não existe. Execute docs/sql/migration_ia_analise.sql',
    ]);
    exit;
}

$acao = (string) ($_POST['acao'] ?? 'salvar');
if ($acao === 'status') {
    $key = stIaGetApiKey($PDO);
    echo json_encode([
        'ok' => true,
        'configurada' => $key !== null,
        'mascara' => $key !== null ? ('sk-...' . substr($key, -4)) : null,
    ]);
    exit;
}

$novaChave = trim((string) ($_POST['openai_api_key'] ?? ''));
if ($novaChave === '') {
    $stmt = $PDO->prepare('UPDATE tbl_config_sis SET openai_api_key = NULL LIMIT 1');
    $stmt->execute();
    echo json_encode(['ok' => true, 'mensagem' => 'Chave removida']);
    exit;
}

if (strlen($novaChave) < 20 || strpos($novaChave, 'sk-') !== 0) {
    echo json_encode(['ok' => false, 'error' => 'Formato de chave inválido']);
    exit;
}

$stmt = $PDO->prepare('UPDATE tbl_config_sis SET openai_api_key = ? LIMIT 1');
$stmt->execute([$novaChave]);

echo json_encode(['ok' => true, 'mensagem' => 'Chave salva com sucesso', 'mascara' => 'sk-...' . substr($novaChave, -4)]);
