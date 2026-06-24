<?php
/**
 * API JSON — painel de governança operacional (IDX).
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../cnf/session.php';
require_once __DIR__ . '/../cnf/st_fila_status.php';

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

if ($deDt === '' || $ateDt === '') {
    $deDt = date('Y-m-01');
    $ateDt = date('Y-m-d');
}
if ($deDt > $ateDt) {
    [$deDt, $ateDt] = [$ateDt, $deDt];
}

$deInicio = $deDt . ' 00:00:00';
$ateFim = $ateDt . ' 23:59:59';

$qryContratoPerm = ($nivelUsu > 0) ? ' AND contrato_id IN (' . $infoUserConfig['contrato_id'] . ')' : '';
if ($idContrato > 0) {
    if ($nivelUsu > 0 && strpos($infoUserConfig['contrato_id'], (string) $idContrato) === false) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Contrato não permitido']);
        exit;
    }
}

function stGovFmtTime(?string $val, string $def = '--:--:--'): string
{
    if ($val === null || $val === '') {
        return $def;
    }
    $p = explode('.', (string) $val);

    return $p[0] !== '' ? $p[0] : $def;
}

/**
 * Formata durações longas (soma de TMA no período) sem limite do tipo TIME do MySQL.
 * @param int|float|string|null $seconds
 */
function stGovFmtDuration($seconds, string $def = '--'): string
{
    $seconds = (int) $seconds;
    if ($seconds <= 0) {
        return $def;
    }
    if ($seconds < 86400) {
        return function_exists('sec_to_time') ? sec_to_time($seconds) : gmdate('H:i:s', $seconds);
    }
    $days = intdiv($seconds, 86400);
    $rem = $seconds % 86400;
    $hours = intdiv($rem, 3600);
    $mins = intdiv($rem % 3600, 60);
    $parts = [];
    if ($days > 0) {
        $parts[] = $days . 'd';
    }
    if ($hours > 0 || $days > 0) {
        $parts[] = $hours . 'h';
    }
    $parts[] = $mins . 'm';

    return implode(' ', $parts);
}

/** Primeiro nome + sobrenome para exibição compacta nos rankings. */
function stGovBkoName(?string $nome, ?string $sobrenome): string
{
    $nome = trim((string) $nome);
    $sobrenome = trim((string) $sobrenome);
    $firstParts = preg_split('/\s+/', $nome, -1, PREG_SPLIT_NO_EMPTY);
    $first = isset($firstParts[0]) ? ucwords(strtolower($firstParts[0])) : '';
    $last = $sobrenome !== '' ? ucwords(strtolower($sobrenome)) : '';
    if ($first === '' && $last === '') {
        return '';
    }

    return trim($first . ' ' . $last);
}

function stGovApplyUserNames(array &$rows, string $idKey, string $fallbackPrefix): void
{
    foreach ($rows as &$r) {
        $fmt = stGovBkoName($r['nome_part'] ?? '', $r['sobrenome'] ?? '');
        $r['nome'] = $fmt !== '' ? $fmt : ($fallbackPrefix . ' #' . ($r[$idKey] ?? ''));
        unset($r['nome_part'], $r['sobrenome']);
    }
    unset($r);
}

function stGovApplyBkoNames(array &$rows, string $fallbackPrefix = 'BKO'): void
{
    stGovApplyUserNames($rows, 'bko_resp', $fallbackPrefix);
}

function stGovPeakConcurrent(array $sessions): int
{
    $events = [];
    foreach ($sessions as $s) {
        $start = strtotime((string) ($s['hi'] ?? ''));
        $end = strtotime((string) ($s['hf'] ?? ''));
        if (!$start) {
            continue;
        }
        if (!$end || $end <= $start) {
            $end = $start + 1;
        }
        $events[] = [$start, 1];
        $events[] = [$end, -1];
    }
    if (!$events) {
        return 0;
    }
    usort($events, static function (array $a, array $b): int {
        return $a[0] <=> $b[0] ?: $a[1] <=> $b[1];
    });
    $peak = 0;
    $cur = 0;
    foreach ($events as $e) {
        $cur += $e[1];
        $peak = max($peak, $cur);
    }

    return $peak;
}

$whereExtra = '';
$paramsBase = [$deInicio, $ateFim];
if ($idContrato > 0) {
    $whereExtra .= ' AND x.contrato_id = ?';
    $paramsBase[] = $idContrato;
} elseif ($qryContratoPerm !== '') {
    $whereExtra .= str_replace('contrato_id', 'x.contrato_id', $qryContratoPerm);
}
if ($idFila > 0) {
    $whereExtra .= ' AND x.fila_id = ?';
    $paramsBase[] = $idFila;
}

$sqlUnified = ''
    . ' SELECT x.id_fila_chat, x.data_hora, x.hora_inicio, x.hora_fim, x.ta, x.te, x.status_fila,'
    . ' x.assunto_id, x.bko_resp, x.ate_resp, x.fila_id, x.contrato_id'
    . ' FROM ('
    . '   SELECT cf.id_fila_chat, cf.data_hora, cf.hora_inicio, cf.hora_fim, cf.ta, cf.te, cf.status_fila,'
    . '     cf.assunto_id, cf.bko_resp, cf.ate_resp, cf.fila_id, cf.contrato_id'
    . '   FROM tbl_chat_fila cf'
    . '   WHERE cf.data_hora >= ? AND cf.data_hora <= ?'
    . '   UNION ALL'
    . '   SELECT cs.id_fila_chat, cs.data_hora, cs.hora_inicio, cs.hora_fim, cs.ta, cs.te, cs.status_fila,'
    . '     cs.assunto_id, cs.bko_resp, cs.ate_resp, cs.fila_id, cs.contrato_id'
    . '   FROM tbl_chat_fila_secondary cs'
    . '   WHERE cs.data_hora >= ? AND cs.data_hora <= ?'
    . '     AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)'
    . ' ) x WHERE 1=1' . $whereExtra;

$paramsUnified = array_merge([$deInicio, $ateFim, $deInicio, $ateFim], array_slice($paramsBase, 2));

$statusAtendido = 'status_fila IN (2,3,4,6,7,10)';
$statusAbandono = 'status_fila IN (5,8,9)';
$statusConcluido = 'status_fila >= ' . ST_FILA_CONCLUIDO;

// Até 12h por registro — evita outliers no banco e o teto 838:59:59 do TIME no MySQL.
$taPositivo = "ta <> '' AND ta IS NOT NULL AND TIME_TO_SEC(ta) > 0";
$taPlausivel = $taPositivo . ' AND TIME_TO_SEC(ta) <= 43200';
$tePositivo = "te <> '' AND te IS NOT NULL AND TIME_TO_SEC(te) > 0";
$tePlausivel = $tePositivo . ' AND TIME_TO_SEC(te) <= 43200';

// --- KPIs consolidados ---
$sqlKpi = 'SELECT'
    . ' COUNT(*) AS entradas,'
    . ' SUM(CASE WHEN ' . $statusAtendido . ' THEN 1 ELSE 0 END) AS atendidos,'
    . ' SUM(CASE WHEN ' . $statusAbandono . ' THEN 1 ELSE 0 END) AS abandonos,'
    . ' SUM(CASE WHEN ' . $statusConcluido . ' THEN 1 ELSE 0 END) AS concluidos,'
    . ' SEC_TO_TIME(AVG(CASE WHEN ' . $statusAtendido . ' AND ' . $taPositivo . ' THEN TIME_TO_SEC(ta) END)) AS tma,'
    . ' SEC_TO_TIME(AVG(CASE WHEN ' . $statusAtendido . ' AND ' . $tePositivo . ' THEN TIME_TO_SEC(te) END)) AS tme,'
    . ' SEC_TO_TIME(MIN(CASE WHEN ' . $taPositivo . ' THEN TIME_TO_SEC(ta) END)) AS menor_tma,'
    . ' SEC_TO_TIME(MAX(CASE WHEN ' . $taPlausivel . ' THEN TIME_TO_SEC(ta) END)) AS maior_tma,'
    . ' SEC_TO_TIME(MIN(CASE WHEN ' . $tePositivo . ' THEN TIME_TO_SEC(te) END)) AS menor_te,'
    . ' SEC_TO_TIME(MAX(CASE WHEN ' . $tePlausivel . ' THEN TIME_TO_SEC(te) END)) AS maior_te,'
    . ' SUM(CASE WHEN ' . $taPositivo . ' THEN TIME_TO_SEC(ta) ELSE 0 END) AS prod_total_sec,'
    . ' COUNT(DISTINCT ate_resp) AS solicitantes,'
    . ' COUNT(DISTINCT CASE WHEN bko_resp > 0 THEN bko_resp END) AS bk_ativos'
    . ' FROM (' . $sqlUnified . ') u';
$stmt = $PDO->prepare($sqlKpi);
$stmt->execute($paramsUnified);
$kpi = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$entradas = (int) ($kpi['entradas'] ?? 0);
$atendidos = (int) ($kpi['atendidos'] ?? 0);
$taxaAtend = $entradas > 0 ? round(($atendidos * 100) / $entradas, 1) : 0;
$taxaAband = $entradas > 0 ? round(((int) ($kpi['abandonos'] ?? 0) * 100) / $entradas, 1) : 0;

// Pendências abertas no período
$pendWhere = 'data_hora >= ? AND data_hora <= ?';
$pendParams = [$deInicio, $ateFim];
if ($idContrato > 0) {
    $pendWhere .= ' AND fila_id IN (SELECT id_fila FROM tbl_config_fila WHERE contrato_id = ?)';
    $pendParams[] = $idContrato;
} elseif ($nivelUsu > 0) {
    $pendWhere .= ' AND fila_id IN (SELECT id_fila FROM tbl_config_fila WHERE contrato_id IN (' . $infoUserConfig['contrato_id'] . '))';
}
if ($idFila > 0) {
    $pendWhere .= ' AND fila_id = ?';
    $pendParams[] = $idFila;
}
$stmtPend = $PDO->prepare('SELECT COUNT(*) FROM tbl_pend_info WHERE ' . $pendWhere);
$stmtPend->execute($pendParams);
$pendPeriodo = (int) $stmtPend->fetchColumn();

$stmtPendAb = $PDO->prepare('SELECT COUNT(*) FROM tbl_pend_info WHERE situacao_id = 3 AND data_hora_fim IS NULL' . ($idFila > 0 ? ' AND fila_id = ?' : ($idContrato > 0 ? ' AND fila_id IN (SELECT id_fila FROM tbl_config_fila WHERE contrato_id = ?)' : ($nivelUsu > 0 ? ' AND fila_id IN (SELECT id_fila FROM tbl_config_fila WHERE contrato_id IN (' . $infoUserConfig['contrato_id'] . '))' : ''))));
$pendAbParams = [];
if ($idFila > 0) {
    $pendAbParams[] = $idFila;
} elseif ($idContrato > 0) {
    $pendAbParams[] = $idContrato;
}
$stmtPendAb->execute($pendAbParams);
$pendAbertas = (int) $stmtPendAb->fetchColumn();

// Satisfação
$starWhere = 'c.data_hora >= ? AND c.data_hora <= ? AND c.star IS NOT NULL AND c.star <> \'\'';
$starParams = [$deInicio, $ateFim];
if ($idContrato > 0 || $idFila > 0 || $nivelUsu > 0) {
    $starWhere .= ' AND c.chat_fila_id IN (SELECT id_fila_chat FROM (' . $sqlUnified . ') su)';
    $starParams = array_merge($starParams, $paramsUnified);
}
$stmtStar = $PDO->prepare('SELECT ROUND(AVG(c.star), 1) AS media, COUNT(*) AS total FROM tbl_classificacao c WHERE ' . $starWhere);
$stmtStar->execute($starParams);
$starRow = $stmtStar->fetch(PDO::FETCH_ASSOC) ?: [];

// Logins únicos BKO no período
$logWhere = 'data_log >= ? AND data_log <= ? AND nivel_id = 4';
$logParams = [$deDt, $ateDt];
if ($idContrato > 0) {
    $logWhere .= ' AND contrato_id = ?';
    $logParams[] = $idContrato;
} elseif ($nivelUsu > 0) {
    $logWhere .= ' AND contrato_id IN (' . $infoUserConfig['contrato_id'] . ')';
}
if ($idFila > 0) {
    $logWhere .= ' AND fila_id = ?';
    $logParams[] = $idFila;
}
$stmtLog = $PDO->prepare('SELECT COUNT(DISTINCT user_id) AS logins FROM tbl_log_diario WHERE ' . $logWhere);
$stmtLog->execute($logParams);
$loginsBko = (int) ($stmtLog->fetchColumn() ?: 0);

// Série diária
$sqlDiario = 'SELECT DATE(data_hora) AS dia, COUNT(*) AS entradas,'
    . ' SUM(CASE WHEN ' . $statusAtendido . ' THEN 1 ELSE 0 END) AS atendidos,'
    . ' SUM(CASE WHEN ' . $statusAbandono . ' THEN 1 ELSE 0 END) AS abandonos'
    . ' FROM (' . $sqlUnified . ') u GROUP BY DATE(data_hora) ORDER BY dia ASC';
$stmt = $PDO->prepare($sqlDiario);
$stmt->execute($paramsUnified);
$serieDiaria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Distribuição por hora
$sqlHora = 'SELECT HOUR(data_hora) AS hora, COUNT(*) AS qtd FROM (' . $sqlUnified . ') u GROUP BY HOUR(data_hora) ORDER BY hora ASC';
$stmt = $PDO->prepare($sqlHora);
$stmt->execute($paramsUnified);
$porHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Status
$sqlStatus = 'SELECT status_fila, COUNT(*) AS qtd FROM (' . $sqlUnified . ') u GROUP BY status_fila ORDER BY qtd DESC';
$stmt = $PDO->prepare($sqlStatus);
$stmt->execute($paramsUnified);
$porStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($porStatus as &$st) {
    $stmtNome = $PDO->prepare('SELECT nome_situacao FROM tbl_situacao_chat WHERE id_situacao = ? LIMIT 1');
    $stmtNome->execute([(int) $st['status_fila']]);
    $st['nome'] = (string) ($stmtNome->fetchColumn() ?: 'Status ' . $st['status_fila']);
}
unset($st);

// Top assuntos
$sqlAss = 'SELECT u.assunto_id,'
    . ' (SELECT titulo_assunto FROM tbl_assunto WHERE id_assunto = u.assunto_id LIMIT 1) AS titulo,'
    . ' COUNT(*) AS qtd, SEC_TO_TIME(AVG(TIME_TO_SEC(u.ta))) AS tma'
    . ' FROM (' . $sqlUnified . ') u'
    . ' WHERE u.bko_resp > 0 GROUP BY u.assunto_id ORDER BY qtd DESC LIMIT 10';
$stmt = $PDO->prepare($sqlAss);
$stmt->execute($paramsUnified);
$topAssuntos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($topAssuntos as &$a) {
    $a['tma'] = stGovFmtTime($a['tma'] ?? null);
    $a['titulo'] = $a['titulo'] ?: 'Assunto #' . $a['assunto_id'];
}
unset($a);

// Ranking solicitantes (maior volume de entradas)
$sqlSol = 'SELECT u.ate_resp,'
    . ' us.nome AS nome_part, us.sobrenome,'
    . ' (SELECT nome_empresa FROM tbl_empresa WHERE id_empresa = us.empresa_id LIMIT 1) AS empresa,'
    . ' COUNT(*) AS qtd,'
    . ' SUM(CASE WHEN ' . $statusAtendido . ' THEN 1 ELSE 0 END) AS atendidos,'
    . ' SUM(CASE WHEN ' . $statusAbandono . ' THEN 1 ELSE 0 END) AS abandonos'
    . ' FROM (' . $sqlUnified . ') u'
    . ' INNER JOIN tbl_user us ON us.id_user = u.ate_resp'
    . ' WHERE u.ate_resp > 0'
    . ' GROUP BY u.ate_resp ORDER BY qtd DESC, atendidos DESC LIMIT 15';
$stmt = $PDO->prepare($sqlSol);
$stmt->execute($paramsUnified);
$rankSolicitantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
stGovApplyUserNames($rankSolicitantes, 'ate_resp', 'Solicitante');
foreach ($rankSolicitantes as &$sol) {
    $qtd = (int) ($sol['qtd'] ?? 0);
    $at = (int) ($sol['atendidos'] ?? 0);
    $sol['pct_atendido'] = $qtd > 0 ? round(($at * 100) / $qtd, 1) : 0;
    $sol['empresa'] = trim((string) ($sol['empresa'] ?? '')) ?: '—';
}
unset($sol);

// Ranking volume BKO
$sqlVol = 'SELECT u.bko_resp,'
    . ' us.nome AS nome_part, us.sobrenome,'
    . ' COUNT(*) AS qtd, SEC_TO_TIME(AVG(TIME_TO_SEC(u.ta))) AS tma, SEC_TO_TIME(AVG(TIME_TO_SEC(u.te))) AS tme'
    . ' FROM (' . $sqlUnified . ') u'
    . ' INNER JOIN tbl_user us ON us.id_user = u.bko_resp'
    . ' WHERE u.bko_resp > 0 GROUP BY u.bko_resp ORDER BY qtd DESC LIMIT 15';
$stmt = $PDO->prepare($sqlVol);
$stmt->execute($paramsUnified);
$rankVolume = $stmt->fetchAll(PDO::FETCH_ASSOC);
stGovApplyBkoNames($rankVolume);
foreach ($rankVolume as &$r) {
    $r['tma'] = stGovFmtTime($r['tma'] ?? null);
    $r['tme'] = stGovFmtTime($r['tme'] ?? null);
}
unset($r);

// Melhor TMA (mín. 3 atendimentos)
$sqlTma = 'SELECT u.bko_resp,'
    . ' us.nome AS nome_part, us.sobrenome,'
    . ' COUNT(*) AS qtd, SEC_TO_TIME(AVG(TIME_TO_SEC(u.ta))) AS tma'
    . ' FROM (' . $sqlUnified . ') u'
    . ' INNER JOIN tbl_user us ON us.id_user = u.bko_resp'
    . " WHERE u.bko_resp > 0 AND u.ta <> '' AND u.ta IS NOT NULL"
    . ' GROUP BY u.bko_resp HAVING COUNT(*) >= 3'
    . ' ORDER BY AVG(TIME_TO_SEC(u.ta)) ASC LIMIT 10';
$stmt = $PDO->prepare($sqlTma);
$stmt->execute($paramsUnified);
$rankTma = $stmt->fetchAll(PDO::FETCH_ASSOC);
stGovApplyBkoNames($rankTma);
foreach ($rankTma as &$r) {
    $r['tma'] = stGovFmtTime($r['tma'] ?? null);
}
unset($r);

// Melhor TME (mín. 3)
$sqlTme = 'SELECT u.bko_resp,'
    . ' us.nome AS nome_part, us.sobrenome,'
    . ' COUNT(*) AS qtd, SEC_TO_TIME(AVG(TIME_TO_SEC(u.te))) AS tme'
    . ' FROM (' . $sqlUnified . ') u'
    . ' INNER JOIN tbl_user us ON us.id_user = u.bko_resp'
    . " WHERE u.bko_resp > 0 AND u.te <> '' AND u.te IS NOT NULL"
    . ' GROUP BY u.bko_resp HAVING COUNT(*) >= 3'
    . ' ORDER BY AVG(TIME_TO_SEC(u.te)) ASC LIMIT 10';
$stmt = $PDO->prepare($sqlTme);
$stmt->execute($paramsUnified);
$rankTme = $stmt->fetchAll(PDO::FETCH_ASSOC);
stGovApplyBkoNames($rankTme);
foreach ($rankTme as &$r) {
    $r['tme'] = stGovFmtTime($r['tme'] ?? null);
}
unset($r);

// Pico simultâneo por BKO
$sqlSim = 'SELECT bko_resp, hora_inicio AS hi, COALESCE(hora_fim, hora_inicio) AS hf'
    . ' FROM (' . $sqlUnified . ') u'
    . " WHERE bko_resp > 0 AND hora_inicio IS NOT NULL AND hora_inicio <> '' AND hora_inicio <> '0000-00-00 00:00:00'";
$stmt = $PDO->prepare($sqlSim);
$stmt->execute($paramsUnified);
$simRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$byBko = [];
foreach ($simRows as $row) {
    $bid = (int) $row['bko_resp'];
    $byBko[$bid][] = ['hi' => $row['hi'], 'hf' => $row['hf']];
}
$rankSim = [];
foreach ($byBko as $bid => $sessions) {
    $peak = stGovPeakConcurrent($sessions);
    if ($peak > 1) {
        $rankSim[] = ['bko_resp' => $bid, 'pico' => $peak];
    }
}
usort($rankSim, static function (array $a, array $b): int {
    return $b['pico'] <=> $a['pico'];
});
$rankSim = array_slice($rankSim, 0, 10);
foreach ($rankSim as &$rs) {
    $stmtN = $PDO->prepare('SELECT nome, sobrenome FROM tbl_user WHERE id_user = ? LIMIT 1');
    $stmtN->execute([(int) $rs['bko_resp']]);
    $userRow = $stmtN->fetch(PDO::FETCH_ASSOC) ?: [];
    $fmt = stGovBkoName($userRow['nome'] ?? '', $userRow['sobrenome'] ?? '');
    $rs['nome'] = $fmt !== '' ? $fmt : 'BKO #' . $rs['bko_resp'];
}
unset($rs);

// Por fila (quando sem filtro específico)
$porFila = [];
if ($idFila <= 0) {
    $sqlFila = 'SELECT u.fila_id,'
        . ' (SELECT nome_fila FROM tbl_config_fila WHERE id_fila = u.fila_id LIMIT 1) AS nome,'
        . ' COUNT(*) AS entradas,'
        . ' SUM(CASE WHEN ' . $statusAtendido . ' THEN 1 ELSE 0 END) AS atendidos,'
        . ' SEC_TO_TIME(AVG(CASE WHEN ' . $statusAtendido . " AND ta <> '' THEN TIME_TO_SEC(ta) END)) AS tma"
        . ' FROM (' . $sqlUnified . ') u GROUP BY u.fila_id ORDER BY entradas DESC LIMIT 12';
    $stmt = $PDO->prepare($sqlFila);
    $stmt->execute($paramsUnified);
    $porFila = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($porFila as &$f) {
        $f['tma'] = stGovFmtTime($f['tma'] ?? null);
        $f['nome'] = $f['nome'] ?: 'Fila #' . $f['fila_id'];
    }
    unset($f);
}

echo json_encode([
    'ok' => true,
    'filtros' => [
        'contrato' => $idContrato,
        'fila' => $idFila,
        'de' => $deDt,
        'ate' => $ateDt,
    ],
    'kpis' => [
        'entradas' => $entradas,
        'atendidos' => $atendidos,
        'concluidos' => (int) ($kpi['concluidos'] ?? 0),
        'abandonos' => (int) ($kpi['abandonos'] ?? 0),
        'taxa_atendimento' => $taxaAtend,
        'taxa_abandono' => $taxaAband,
        'tma' => stGovFmtTime($kpi['tma'] ?? null),
        'tme' => stGovFmtTime($kpi['tme'] ?? null),
        'menor_tma' => stGovFmtTime($kpi['menor_tma'] ?? null),
        'maior_tma' => stGovFmtTime($kpi['maior_tma'] ?? null),
        'menor_te' => stGovFmtTime($kpi['menor_te'] ?? null),
        'maior_te' => stGovFmtTime($kpi['maior_te'] ?? null),
        'prod_total' => stGovFmtDuration($kpi['prod_total_sec'] ?? 0),
        'solicitantes' => (int) ($kpi['solicitantes'] ?? 0),
        'bk_ativos' => (int) ($kpi['bk_ativos'] ?? 0),
        'logins_bko' => $loginsBko,
        'pendencias_periodo' => $pendPeriodo,
        'pendencias_abertas' => $pendAbertas,
        'satisfacao' => [
            'media' => $starRow['media'] ?? null,
            'total' => (int) ($starRow['total'] ?? 0),
        ],
    ],
    'serie_diaria' => $serieDiaria,
    'por_hora' => $porHora,
    'por_status' => $porStatus,
    'top_assuntos' => $topAssuntos,
    'rank_solicitantes' => $rankSolicitantes,
    'rank_volume' => $rankVolume,
    'rank_tma' => $rankTma,
    'rank_tme' => $rankTme,
    'rank_simultaneo' => $rankSim,
    'por_fila' => $porFila,
], JSON_UNESCAPED_UNICODE);
