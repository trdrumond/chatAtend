<?php
/**
 * API JSON — insights com IA (análise diária D+1, resumo consolidado do período).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/st_ia_analytics.php';

set_time_limit(300);

$nivelUsu = (int) ($infoUser['nivel_id'] ?? 99);
if ($nivelUsu >= 5) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acesso negado']);
    exit;
}

if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}

if (function_exists('session_write_close')) {
    session_write_close();
}

$idContrato = (int) ($_GET['contrato'] ?? $_POST['contrato'] ?? 0);
$idFila = (int) ($_GET['fila'] ?? $_POST['fila'] ?? 0);
$deDt = preg_replace('/[^0-9\-]/', '', (string) ($_GET['de'] ?? $_POST['de'] ?? ''));
$ateDt = preg_replace('/[^0-9\-]/', '', (string) ($_GET['ate'] ?? $_POST['ate'] ?? ''));

$ultimoDia = stIaUltimoDiaDisponivel();

if ($deDt === '' || $ateDt === '') {
    $deDt = date('Y-m-01', strtotime($ultimoDia));
    $ateDt = $ultimoDia;
}
if ($deDt > $ateDt) {
    [$deDt, $ateDt] = [$ateDt, $deDt];
}
if ($ateDt > $ultimoDia) {
    $ateDt = $ultimoDia;
}
if ($deDt > $ultimoDia) {
    $deDt = $ultimoDia;
}

if ($idContrato > 0 && $nivelUsu > 0) {
    if (strpos($infoUserConfig['contrato_id'], (string) $idContrato) === false) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Contrato não permitido']);
        exit;
    }
}

$schemaReady = stIaSchemaReady($PDO);
$apiKeyOk = $schemaReady && stIaGetApiKey($PDO) !== null;
$storeContrato = $idContrato;
$storeFila = 0;

$geracao = [
    'dias_gerados' => 0,
    'dias_ja_existentes' => 0,
    'limite_atingido' => false,
    'detalhes' => [],
];

if ($schemaReady) {
    $ensure = stIaEnsurePeriodGenerated($PDO, $deDt, $ateDt, $storeContrato, $storeFila);
    $geracao = [
        'dias_gerados' => count($ensure['generated']),
        'dias_ja_existentes' => count($ensure['skipped']),
        'limite_atingido' => !empty($ensure['hit_limit']),
        'detalhes' => $ensure['generated'],
    ];
}

$dailyRows = [];
if ($schemaReady) {
    $dailyRows = stIaLoadDailyRange($PDO, $deDt, $ateDt, $storeContrato, $storeFila);
}

if ($idFila > 0) {
    $periodo = stIaCollectPeriodLive($PDO, $deDt, $ateDt, $idContrato, $idFila);
    $periodo['fonte'] = 'hibrido';
} elseif (count($dailyRows) > 0) {
    $periodo = stIaSumPeriodFromDaily($dailyRows);
    $periodo['fonte'] = 'armazenado';
} elseif ($schemaReady) {
    $periodo = stIaCollectPeriodLive($PDO, $deDt, $ateDt, $idContrato, 0);
    $periodo['fonte'] = 'live';
} else {
    $periodo = stIaSumPeriodFromDaily([]);
    $periodo['fonte'] = 'indisponivel';
}

$periodo['top_motivos'] = stIaNormalizeMotivoRows(
    stIaFetchMotivosForRange($PDO, $deDt, $ateDt, $idContrato, $idFila)
);

if (empty($periodo['dias_com_dados'])) {
    $periodo['dias_com_dados'] = count(stIaIterateDays($deDt, $ateDt));
}

$analisePeriodo = null;
$analiseFromCache = false;
$iaMotivo = null;

if (!$schemaReady) {
    $iaMotivo = 'Estrutura de IA não instalada. Execute a migration em docs/sql/migration_ia_analise.sql';
} elseif (!$apiKeyOk) {
    $iaMotivo = 'Chave OpenAI não configurada. Indicadores numéricos estão disponíveis.';
} elseif (!empty($geracao['limite_atingido'])) {
    $iaMotivo = 'Limite da API OpenAI atingido ao consolidar métricas. Consulte o log de limites.';
} else {
    $iaRes = stIaGetOrCreatePeriodAnalysis($PDO, $deDt, $ateDt, $idContrato, $idFila, $periodo);
    $analiseFromCache = !empty($iaRes['from_cache']);
    if (!empty($iaRes['ok']) && !empty($iaRes['text'])) {
        $analisePeriodo = (string) $iaRes['text'];
    } else {
        $status = $iaRes['status'] ?? '';
        if ($status === ST_IA_STATUS_LIMITE) {
            $iaMotivo = 'Limite da API OpenAI atingido. Consulte o log de limites.';
        } elseif ($status === ST_IA_STATUS_SEM_CHAVE) {
            $iaMotivo = 'Chave OpenAI não configurada.';
        } elseif (($iaRes['error'] ?? '') === 'Sem atendimentos no período') {
            $iaMotivo = 'Sem atendimentos no período selecionado para gerar análise.';
        } else {
            $iaMotivo = $iaRes['error'] ?? 'Não foi possível gerar o resumo do período.';
        }
    }
}

$iaDisponivel = $analisePeriodo !== null && $analisePeriodo !== '';

echo json_encode([
    'ok' => true,
    'meta' => [
        'd_plus_1' => true,
        'ultimo_dia_disponivel' => $ultimoDia,
        'aviso_dados' => 'Os indicadores refletem dados consolidados com defasagem D+1 (dia anterior). O dia atual ainda não entra na análise.',
        'periodo_soma' => 'Os valores numéricos do período são somados (TMA/TME: médias ponderadas). A interpretação da IA é um único resumo consolidado.',
        'geracao_automatica' => 'Dias ausentes no banco são gerados automaticamente ao abrir o relatório.',
    ],
    'geracao' => $geracao,
    'filtros' => [
        'contrato' => $idContrato,
        'fila' => $idFila,
        'de' => $deDt,
        'ate' => $ateDt,
    ],
    'ia' => [
        'disponivel' => $iaDisponivel,
        'motivo_indisponivel' => $iaMotivo,
        'chave_configurada' => $apiKeyOk,
        'analise_periodo' => $analisePeriodo,
        'analise_do_cache' => $analiseFromCache,
    ],
    'periodo' => $periodo,
], JSON_UNESCAPED_UNICODE);
