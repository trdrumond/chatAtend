<?php
/**
 * Cron / processamento manual — gera análises diárias D+1.
 * Agendar: 1x/dia (ex.: 02:00) via Task Scheduler ou curl.
 *
 * Uso: staff/cron_ia_analise_diaria.php?token=SEU_TOKEN
 * Token opcional: variável de ambiente ST_IA_CRON_TOKEN ou tbl_config_sis (futuro).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../cnf/conexao.php';
require_once __DIR__ . '/../cnf/st_ia_analytics.php';

$tokenEnv = getenv('ST_IA_CRON_TOKEN') ?: '';
$tokenReq = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
if ($tokenEnv !== '' && !hash_equals($tokenEnv, $tokenReq)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token inválido']);
    exit;
}

if (!stIaSchemaReady($PDO)) {
    echo json_encode(['ok' => false, 'error' => 'Migration não aplicada']);
    exit;
}

$refDia = preg_replace('/[^0-9\-]/', '', (string) ($_GET['dia'] ?? $_POST['dia'] ?? ''));
if ($refDia === '') {
    $refDia = stIaUltimoDiaDisponivel();
}
if ($refDia > stIaUltimoDiaDisponivel()) {
    echo json_encode(['ok' => false, 'error' => 'Dia futuro ou dia atual — use apenas D+1']);
    exit;
}

$contratos = stIaActiveContratoIds($PDO);
array_unshift($contratos, 0);

$resultados = [];
foreach ($contratos as $cttId) {
    $res = stIaGenerateDaily($PDO, $refDia, (int) $cttId, 0);
    $resultados[] = [
        'contrato_id' => (int) $cttId,
        'status' => $res['status'] ?? 'erro',
        'ok' => !empty($res['ok']),
    ];
}

echo json_encode([
    'ok' => true,
    'ref_dia' => $refDia,
    'processados' => count($resultados),
    'detalhes' => $resultados,
], JSON_UNESCAPED_UNICODE);
