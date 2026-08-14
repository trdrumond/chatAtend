<?php
/**
 * API JSON — dashboard operacional (dash-fila) em tempo real.
 * Consolida equipe, fila e indicadores em poucas queries.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../cnf/session.php';

if (!isset($infoUser) || !is_array($infoUser) || (int) ($infoUser['nivel_id'] ?? 99) >= 5) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acesso negado']);
    exit;
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}

if (function_exists('session_write_close')) {
    session_write_close();
}

$nivelUsu = (int) ($infoUser['nivel_id'] ?? 0);
$cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
$cttParams = ($nivelUsu > 0) ? $cttIn['ids'] : [];
$qryContrato = ($nivelUsu > 0) ? ' AND contrato_id IN (' . $cttIn['ph'] . ')' : '';
$qryFilaCtt = ($nivelUsu > 0) ? ' AND f.contrato_id IN (' . $cttIn['ph'] . ')' : '';
$qryUserCtt = ($nivelUsu > 0) ? ' AND u.contrato_id IN (' . $cttIn['ph'] . ')' : '';
$qryPendFilaCtt = ($nivelUsu > 0)
    ? ' AND p.fila_id IN (SELECT id_fila FROM tbl_config_fila WHERE contrato_id IN (' . $cttIn['ph'] . '))'
    : '';

$atendSql = stFilaSqlAtendimentoAtivo();

function cnfDashTrimTime(?string $time): string
{
    if ($time === null || $time === '') {
        return '--:--:--';
    }
    $parts = explode('.', (string) $time);

    return $parts[0];
}

function cnfDashShortName(string $nome, string $sobrenome): string
{
    $n = ucwords(strtolower(trim((string) $nome)));
    $s = trim((string) $sobrenome);
    $ini = $s !== '' ? strtoupper($s[0]) . '.' : '';

    return trim($n . ' ' . $ini);
}

function cnfDashElapsedSince(?string $dataHora): string
{
    if ($dataHora === null || $dataHora === '') {
        return '--:--:--';
    }
    $ts = strtotime((string) $dataHora);
    if ($ts === false) {
        return '--:--:--';
    }
    $diff = time() - $ts;

    return sec_to_time($diff < 0 ? 0 : $diff);
}

/** Alinhado a load_online.php */
function cnfDashResolveBkoStatus(bool $isOnline, string $acao, int $qtdAtend): string
{
    if (!$isOnline) {
        return 'offline';
    }
    if ($acao === 'Login' || $acao === 'Disponivel') {
        return $qtdAtend > 0 ? 'atendimento' : 'online';
    }
    if ($acao === 'Tratamento') {
        return $qtdAtend > 0 ? 'atendimento' : 'indisp';
    }
    if ($acao === 'Pausa') {
        return 'pausa';
    }
    if ($acao === 'Pos') {
        return $qtdAtend > 0 ? 'atendimento' : 'pos';
    }
    if ($acao === 'Logout') {
        return 'logout';
    }
    if ($acao === 'Indisponivel') {
        return 'indisp';
    }

    return 'offline';
}

function cnfDashFormatStar(?string $star): string
{
    $star = (string) $star;
    if (date('Y-m-d') < '2021-12-06' && $star !== '' && (float) $star < 2.5) {
        return ' -.- ';
    }
    if ($star === '' || $star === '0.0') {
        return ' -.- ';
    }

    return $star;
}

/**
 * @return array<int, array<string, mixed>>
 */
function cnfDashLoadTeam(PDO $pdo, int $filaId, string $qryUserCtt, string $atendSql, array $cttParams = []): array
{
    $teamParams = [];
    $sqlFila = '';
    if ($filaId > 0) {
        $sqlFila = ' AND u.fila_id = ?';
        $teamParams[] = $filaId;
    }

    $sqlUsers = "SELECT u.id_user, u.nome, u.sobrenome, u.fila_id,
            COALESCE(NULLIF((SELECT img FROM tbl_user_img_perfil WHERE user_id = u.id_user), ''), 'img/perfil.fw.png') AS img
        FROM tbl_user u
        WHERE u.ativo = 1 AND u.nivel_id = 4 $sqlFila $qryUserCtt
        ORDER BY u.fila_id, u.nome ASC";
    $stmtUsers = $pdo->prepare($sqlUsers);
    $stmtUsers->execute(array_merge($teamParams, $cttParams));
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
    if (!count($users)) {
        return [];
    }

    $userIds = [];
    foreach ($users as $u) {
        $userIds[] = (int) $u['id_user'];
    }
    $uidBind = stSqlInBind(array_values(array_unique($userIds)));
    $uidsPh = $uidBind['ph'];
    $uidsParams = $uidBind['params'];
    $stFetch = static function (PDO $pdo, string $sql, array $params): array {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    };

    $onlineMap = [];
    foreach ($stFetch(
        $pdo,
        "SELECT user_id FROM tbl_log_diario WHERE data_log = CURDATE() AND user_id IN ($uidsPh) AND date_out IS NULL",
        $uidsParams
    ) as $row) {
        $onlineMap[(int) $row['user_id']] = true;
    }

    $atendAcaoMap = [];
    $sqlUltAcao = "SELECT la.user_id, la.acao, la.data_hora
        FROM tbl_log_atendimento la
        INNER JOIN (
            SELECT user_id, MAX(data_hora) AS max_dt
            FROM tbl_log_atendimento
            WHERE user_id IN ($uidsPh)
              AND data_hora >= CURDATE()
              AND data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
              AND acao IN ('Login','Disponivel','Indisponivel','Tratamento','Logout','Pausa','Pos')
            GROUP BY user_id
        ) ult ON la.user_id = ult.user_id AND la.data_hora = ult.max_dt";
    foreach ($stFetch($pdo, $sqlUltAcao, $uidsParams) as $row) {
        $atendAcaoMap[(int) $row['user_id']] = $row;
    }

    $atendAtivoMap = [];
    foreach ($stFetch(
        $pdo,
        "SELECT resp_id, date_in FROM tbl_tma_atend
            WHERE resp_id IN ($uidsPh) AND DATE(date_disp) = CURDATE() AND DATE(date_in) = CURDATE() AND date_out IS NULL",
        $uidsParams
    ) as $row) {
        $atendAtivoMap[(int) $row['resp_id']] = $row['date_in'];
    }

    $qtdAtendMap = [];
    foreach ($stFetch(
        $pdo,
        "SELECT bko_resp, COUNT(*) AS qtd FROM tbl_chat_fila WHERE bko_resp IN ($uidsPh) AND $atendSql GROUP BY bko_resp",
        $uidsParams
    ) as $row) {
        $qtdAtendMap[(int) $row['bko_resp']] = (int) $row['qtd'];
    }

    $starMap = [];
    $day = (date('Y-m-d') < '2021-12-06') ? 1 : 5;
    $sqlStar = "SELECT ate, FORMAT(AVG(star), 1) AS star FROM tbl_classificacao
        WHERE star IS NOT NULL AND star <> ''
          AND data_hora >= '0001-01-01'
          AND data_hora < DATE_SUB(CURDATE(), INTERVAL ? DAY)
          AND ate IN ($uidsPh)
        GROUP BY ate";
    foreach ($stFetch($pdo, $sqlStar, array_merge([$day], $uidsParams)) as $row) {
        $starMap[(int) $row['ate']] = cnfDashFormatStar(isset($row['star']) ? (string) $row['star'] : null);
    }

    $team = [];
    foreach ($users as $u) {
        $uid = (int) $u['id_user'];
        $isOnline = isset($onlineMap[$uid]);
        $acaoRow = $atendAcaoMap[$uid] ?? ['acao' => '', 'data_hora' => ''];
        $acao = (string) ($acaoRow['acao'] ?? '');
        $dataHora = $acaoRow['data_hora'] ?? null;
        $qtdAtend = $qtdAtendMap[$uid] ?? 0;

        if (isset($atendAtivoMap[$uid])) {
            $acao = 'Tratamento';
            $dataHora = $atendAtivoMap[$uid];
        }

        $status = cnfDashResolveBkoStatus($isOnline, $acao, $qtdAtend);
        if ($status === 'logout') {
            $dataHora = null;
        }

        $team[] = [
            'id' => $uid,
            'fila_id' => (int) $u['fila_id'],
            'nome' => cnfDashShortName((string) $u['nome'], (string) $u['sobrenome']),
            'img' => (string) $u['img'],
            'status' => $status,
            'tempo' => cnfDashElapsedSince($dataHora !== null && $dataHora !== '' ? (string) $dataHora : null),
            'tempo_base' => $dataHora,
            'qtd_atend' => $qtdAtend,
            'star' => isset($starMap[$uid]) ? $starMap[$uid] : ' -.- ',
        ];
    }

    return stDashSortBkoTiles($team, 'status', 'nome');
}

/**
 * @return array<int, array<int, array<string, mixed>>>
 */
function cnfDashGroupFilaItens(array $items)
{
    $map = [];
    foreach ($items as $item) {
        $fid = (int) ($item['id_fila'] ?? 0);
        if (!isset($map[$fid])) {
            $map[$fid] = [];
        }
        unset($item['id_fila']);
        $map[$fid][] = $item;
    }

    return $map;
}

/**
 * @return array<int, array<int, array<string, mixed>>>
 */
function cnfDashGroupTeam(array $team)
{
    $map = [];
    foreach ($team as $member) {
        $fid = (int) ($member['fila_id'] ?? 0);
        if (!isset($map[$fid])) {
            $map[$fid] = [];
        }
        $map[$fid][] = $member;
    }

    return $map;
}

function cnfDashLoadAllFilaItens(PDO $pdo)
{
    $sql = "SELECT id_fila, protocolo, hora_registro, tempo_decorrido, municipio, nome_fila, titulo_assunto
        FROM fila_atual ORDER BY hora_registro ASC";
    $items = [];
    foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $items[] = [
            'id_fila' => (int) $row['id_fila'],
            'protocolo' => $row['protocolo'],
            'municipio' => $row['municipio'],
            'nome_fila' => $row['nome_fila'],
            'hora_registro' => $row['hora_registro'],
            'tempo' => $row['tempo_decorrido'],
            'assunto' => $row['titulo_assunto'],
        ];
    }

    return $items;
}

function cnfDashFilaItensView(array $items, bool $showNomeFila): array
{
    $out = [];
    foreach ($items as $item) {
        $row = [
            'protocolo' => $item['protocolo'],
            'municipio' => $item['municipio'],
            'hora_registro' => $item['hora_registro'],
            'tempo' => $item['tempo'],
            'assunto' => $item['assunto'],
        ];
        if ($showNomeFila) {
            $row['nome_fila'] = $item['nome_fila'];
        }
        $out[] = $row;
    }

    return $out;
}

function cnfDashFormatTimeAgg(?string $val): string
{
    if ($val === null || $val === '') {
        return '--:--:--';
    }

    return cnfDashTrimTime((string) $val);
}

/**
 * @return array{menor_espera: string, maior_espera: string, atend_rapido: string, maior_atend: string}
 */
function cnfDashLoadTaTeStats(PDO $pdo, int $filaId, string $qryContrato, array $cttParams = []): array
{
    $sql = 'SELECT'
        .' SEC_TO_TIME(MIN(CASE WHEN te IS NOT NULL AND te <> \'\' AND TIME_TO_SEC(te) > 0 THEN TIME_TO_SEC(te) END)) AS menor_espera,'
        .' SEC_TO_TIME(MAX(CASE WHEN te IS NOT NULL AND te <> \'\' AND TIME_TO_SEC(te) > 0 THEN TIME_TO_SEC(te) END)) AS maior_espera,'
        .' SEC_TO_TIME(MIN(CASE WHEN ta IS NOT NULL AND ta <> \'\' AND TIME_TO_SEC(ta) > 0 THEN TIME_TO_SEC(ta) END)) AS atend_rapido,'
        .' SEC_TO_TIME(MAX(CASE WHEN ta IS NOT NULL AND ta <> \'\' AND TIME_TO_SEC(ta) > 0 THEN TIME_TO_SEC(ta) END)) AS maior_atend'
        .' FROM tbl_chat_fila'
        .' WHERE status_fila >= ' . ST_FILA_CONCLUIDO
        .' AND hora_inicio >= CURDATE()'
        .' AND hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)'
        .' AND hora_fim IS NOT NULL AND hora_fim <> \'\'';
    $tateParams = [];
    if ($filaId > 0) {
        $sql .= ' AND fila_id = ?';
        $tateParams[] = $filaId;
    }
    $sql .= $qryContrato;

    $stmtTate = $pdo->prepare($sql);
    $stmtTate->execute(array_merge($tateParams, $cttParams));
    $row = $stmtTate->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'menor_espera' => cnfDashFormatTimeAgg($row['menor_espera'] ?? null),
        'maior_espera' => cnfDashFormatTimeAgg($row['maior_espera'] ?? null),
        'atend_rapido' => cnfDashFormatTimeAgg($row['atend_rapido'] ?? null),
        'maior_atend' => cnfDashFormatTimeAgg($row['maior_atend'] ?? null),
    ];
}

/**
 * @param array<int, int> $filaIds
 * @return array<int, array{menor_espera: string, maior_espera: string, atend_rapido: string, maior_atend: string}>
 */
function cnfDashLoadTaTeStatsPorFila(PDO $pdo, array $filaIds, string $qryContrato, array $cttParams = []): array
{
    $filaIds = array_values(array_unique(array_map('intval', $filaIds)));
    if (!count($filaIds)) {
        return [];
    }
    $filaIn = stSqlInBind($filaIds);
    $sql = 'SELECT fila_id,'
        .' SEC_TO_TIME(MIN(CASE WHEN te IS NOT NULL AND te <> \'\' AND TIME_TO_SEC(te) > 0 THEN TIME_TO_SEC(te) END)) AS menor_espera,'
        .' SEC_TO_TIME(MAX(CASE WHEN te IS NOT NULL AND te <> \'\' AND TIME_TO_SEC(te) > 0 THEN TIME_TO_SEC(te) END)) AS maior_espera,'
        .' SEC_TO_TIME(MIN(CASE WHEN ta IS NOT NULL AND ta <> \'\' AND TIME_TO_SEC(ta) > 0 THEN TIME_TO_SEC(ta) END)) AS atend_rapido,'
        .' SEC_TO_TIME(MAX(CASE WHEN ta IS NOT NULL AND ta <> \'\' AND TIME_TO_SEC(ta) > 0 THEN TIME_TO_SEC(ta) END)) AS maior_atend'
        .' FROM tbl_chat_fila'
        .' WHERE status_fila >= ' . ST_FILA_CONCLUIDO
        .' AND hora_inicio >= CURDATE()'
        .' AND hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)'
        .' AND hora_fim IS NOT NULL AND hora_fim <> \'\''
        .' AND fila_id IN (' . $filaIn['ph'] . ')'
        . $qryContrato
        .' GROUP BY fila_id';

    $stmtFilaTate = $pdo->prepare($sql);
    $stmtFilaTate->execute(array_merge($filaIn['ids'], $cttParams));
    $map = [];
    foreach ($stmtFilaTate->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $fid = (int) ($row['fila_id'] ?? 0);
        if ($fid <= 0) {
            continue;
        }
        $map[$fid] = [
            'menor_espera' => cnfDashFormatTimeAgg($row['menor_espera'] ?? null),
            'maior_espera' => cnfDashFormatTimeAgg($row['maior_espera'] ?? null),
            'atend_rapido' => cnfDashFormatTimeAgg($row['atend_rapido'] ?? null),
            'maior_atend' => cnfDashFormatTimeAgg($row['maior_atend'] ?? null),
        ];
    }

    return $map;
}

function cnfDashLoadAcessosUnicos(PDO $pdo, int $contratoId, string $qryContrato, array $cttParams = []): int
{
    $sql = 'SELECT COUNT(DISTINCT user_id) AS total FROM tbl_log_diario WHERE data_log = CURDATE()';
    $acessoParams = [];
    if ($contratoId > 0) {
        $sql .= ' AND contrato_id = ?';
        $acessoParams[] = $contratoId;
    } else {
        $sql .= $qryContrato;
        $acessoParams = $cttParams;
    }
    $stmtAcesso = $pdo->prepare($sql);
    $stmtAcesso->execute($acessoParams);
    $row = $stmtAcesso->fetch(PDO::FETCH_ASSOC);

    return (int) ($row['total'] ?? 0);
}

/**
 * @param array<int, int> $contratoIds
 * @return array<int, int>
 */
function cnfDashLoadAcessosUnicosPorContrato(PDO $pdo, array $contratoIds): array
{
    $contratoIds = array_values(array_unique(array_filter(array_map('intval', $contratoIds))));
    if (!count($contratoIds)) {
        return [];
    }
    $cttBind = stSqlInBind($contratoIds);
    $sql = 'SELECT contrato_id, COUNT(DISTINCT user_id) AS total'
        .' FROM tbl_log_diario'
        .' WHERE data_log = CURDATE() AND contrato_id IN (' . $cttBind['ph'] . ')'
        .' GROUP BY contrato_id';
    $map = [];
    $stmtCttAcc = $pdo->prepare($sql);
    $stmtCttAcc->execute($cttBind['ids']);
    foreach ($stmtCttAcc->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $map[(int) ($row['contrato_id'] ?? 0)] = (int) ($row['total'] ?? 0);
    }

    return $map;
}

/**
 * @param array<string, mixed> $base
 * @param array<string, mixed> $extra
 * @return array<string, mixed>
 */
function cnfDashMergeIndicadores(array $base, array $extra): array
{
    return array_merge($base, $extra);
}

function cnfDashLoadEspera(PDO $pdo, int $filaId, string $qryContrato, array $cttParams = []): array
{
    $sql = "SELECT COUNT(*) AS qtd,
            TIMEDIFF(CURTIME(), DATE_FORMAT(MIN(data_hora), '%H:%i:%s')) AS tempo_espera
        FROM tbl_chat_fila
        WHERE status_fila = " . ST_FILA_NA_FILA;
    $esperaParams = [];
    if ($filaId > 0) {
        $sql .= ' AND fila_id = ?';
        $esperaParams[] = $filaId;
    }
    $sql .= $qryContrato;

    $stmtEspera = $pdo->prepare($sql);
    $stmtEspera->execute(array_merge($esperaParams, $cttParams));
    $row = $stmtEspera->fetch(PDO::FETCH_ASSOC) ?: [];

    return [
        'qtd' => (int) ($row['qtd'] ?? 0),
        'tempo' => cnfDashTrimTime(isset($row['tempo_espera']) ? $row['tempo_espera'] : null),
    ];
}

try {
    $sqlGeral = "SELECT
        (SELECT COUNT(*) FROM tbl_chat_fila WHERE status_fila = " . ST_FILA_NA_FILA . " $qryContrato) AS em_fila,
        (SELECT COUNT(*) FROM tbl_chat_fila WHERE $atendSql $qryContrato) AS em_atend,
        (SELECT COUNT(*) FROM tbl_chat_fila WHERE status_fila >= " . ST_FILA_CONCLUIDO
        . " AND hora_inicio >= CURDATE() AND hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY) $qryContrato) AS concluidos,
        (SELECT COUNT(*) FROM tbl_pend_info p WHERE p.data_hora >= CURDATE()
            AND p.data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY) $qryPendFilaCtt) AS pendencias,
        (SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(ta))) FROM tbl_chat_fila
            WHERE status_fila >= " . ST_FILA_CONCLUIDO . " AND hora_fim IS NOT NULL AND hora_fim <> ''
            AND DATE(hora_inicio) = CURDATE() $qryContrato) AS tma,
        (SELECT SEC_TO_TIME(AVG(TIME_TO_SEC(te))) FROM tbl_chat_fila
            WHERE status_fila >= " . ST_FILA_CONCLUIDO . " AND hora_fim IS NOT NULL AND hora_fim <> ''
            AND DATE(hora_inicio) = CURDATE() $qryContrato) AS tme";

    $geralParams = $cttParams === []
        ? []
        : array_merge($cttParams, $cttParams, $cttParams, $cttParams, $cttParams, $cttParams);
    $stmtGeral = $PDO->prepare($sqlGeral);
    $stmtGeral->execute($geralParams);
    $geralRow = $stmtGeral->fetch(PDO::FETCH_ASSOC) ?: [];

    $sqlFilas = "SELECT f.id_fila, f.nome_fila, f.contrato_id, f.ativo,
            CONCAT(c.nome_contrato, '/', c.uf) AS nome_contrato,
            (SELECT COUNT(id_fila_chat) FROM tbl_chat_fila
                WHERE fila_id = f.id_fila AND (status_fila = " . ST_FILA_NA_FILA . " OR $atendSql)) AS qtd_on
        FROM tbl_config_fila f
        INNER JOIN tbl_contrato c ON c.id_contrato = f.contrato_id
        WHERE c.ativo = 1 $qryFilaCtt
          AND (f.ativo = 1 OR (
              SELECT COUNT(id_fila_chat) FROM tbl_chat_fila
              WHERE fila_id = f.id_fila AND (status_fila = " . ST_FILA_NA_FILA . " OR $atendSql)
          ) > 0)
        ORDER BY f.nome_fila ASC";
    $stmtFilas = $PDO->prepare($sqlFilas);
    $stmtFilas->execute($cttParams);
    $filas = $stmtFilas->fetchAll(PDO::FETCH_ASSOC);
    $filaIds = [];
    foreach ($filas as $f) {
        $filaIds[] = (int) $f['id_fila'];
    }

    $statsPorFila = [];
    $pendHojePorFila = [];
    $pendTotalPorFila = [];
    if (count($filaIds) > 0) {
        $filaBind = stSqlInBind($filaIds);
        $idsPh = $filaBind['ph'];
        $idsParams = $filaBind['params'];

        $sqlStats = "SELECT fila_id,
                SUM(CASE WHEN status_fila = " . ST_FILA_NA_FILA . " THEN 1 ELSE 0 END) AS em_fila,
                SUM(CASE WHEN $atendSql THEN 1 ELSE 0 END) AS em_atend,
                SUM(CASE WHEN status_fila >= " . ST_FILA_CONCLUIDO
            . " AND DATE(hora_inicio) = CURDATE() THEN 1 ELSE 0 END) AS concluidos,
                SEC_TO_TIME(AVG(CASE WHEN status_fila >= " . ST_FILA_CONCLUIDO
            . " AND DATE(hora_fim) = CURDATE() AND ta IS NOT NULL AND ta <> '' THEN TIME_TO_SEC(ta) END)) AS tma,
                SEC_TO_TIME(AVG(CASE WHEN status_fila >= " . ST_FILA_CONCLUIDO
            . " AND DATE(hora_fim) = CURDATE() AND te IS NOT NULL AND te <> '' THEN TIME_TO_SEC(te) END)) AS tme
            FROM tbl_chat_fila WHERE fila_id IN ($idsPh) GROUP BY fila_id";
        $stmtStats = $PDO->prepare($sqlStats);
        $stmtStats->execute($idsParams);
        foreach ($stmtStats->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $statsPorFila[(int) $row['fila_id']] = $row;
        }

        $sqlPendHoje = 'SELECT fila_id, COUNT(*) AS pend FROM tbl_pend_info p'
            .' WHERE p.data_hora >= CURDATE()'
            .' AND p.data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)'
            .' AND p.fila_id IN (' . $idsPh . ')'
            .' GROUP BY fila_id';
        $stmtPendHoje = $PDO->prepare($sqlPendHoje);
        $stmtPendHoje->execute($idsParams);
        foreach ($stmtPendHoje->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pendHojePorFila[(int) $row['fila_id']] = (int) $row['pend'];
        }

        $sqlPendTotal = 'SELECT fila_id, COUNT(*) AS pend FROM tbl_pend_info'
            .' WHERE situacao_id = 3 AND data_hora_fim IS NULL'
            .' AND fila_id IN (' . $idsPh . ')'
            .' GROUP BY fila_id';
        $stmtPendTotal = $PDO->prepare($sqlPendTotal);
        $stmtPendTotal->execute($idsParams);
        foreach ($stmtPendTotal->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pendTotalPorFila[(int) $row['fila_id']] = (int) $row['pend'];
        }
    }

    $bkoOnlineGeral = 0;
    $bkoOnlinePorFila = [];
    foreach ($PDO->query(
        "SELECT l.user_id, l.fila_id,
            (SELECT COUNT(*) FROM tbl_log_atendimento la WHERE la.user_id = l.user_id AND DATE(la.data_hora) = CURDATE()) AS acao_cnt,
            (SELECT la.data_hora FROM tbl_log_atendimento la WHERE la.user_id = l.user_id AND DATE(la.data_hora) = CURDATE() AND la.acao = 'Logout' ORDER BY la.data_hora DESC LIMIT 1) AS logout_hora,
            (SELECT la.data_hora FROM tbl_log_atendimento la WHERE la.user_id = l.user_id AND DATE(la.data_hora) = CURDATE() AND la.acao <> 'Logout' ORDER BY la.data_hora DESC LIMIT 1) AS atend_hora
        FROM tbl_log_diario l
        WHERE l.data_log = CURDATE() AND l.nivel_id = 4 AND l.date_out IS NULL"
    )->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if ((int) ($row['acao_cnt'] ?? 0) <= 0) {
            continue;
        }
        $logout = $row['logout_hora'] ?? '';
        $atend = $row['atend_hora'] ?? '';
        if ($logout !== '' && $atend !== '' && $logout >= $atend) {
            continue;
        }
        $bkoOnlineGeral++;
        $fid = (int) ($row['fila_id'] ?? 0);
        if ($fid > 0) {
            $bkoOnlinePorFila[$fid] = ($bkoOnlinePorFila[$fid] ?? 0) + 1;
        }
    }

    $esperaGeral = cnfDashLoadEspera($PDO, 0, $qryContrato, $cttParams);
    $esperaPorFila = [];
    if (count($filaIds) > 0) {
        $espBind = stSqlInBind($filaIds);
        $sqlEsp = "SELECT fila_id, COUNT(*) AS qtd,
                TIMEDIFF(CURTIME(), DATE_FORMAT(MIN(data_hora), '%H:%i:%s')) AS tempo_espera
            FROM tbl_chat_fila
            WHERE status_fila = " . ST_FILA_NA_FILA . " AND fila_id IN ({$espBind['ph']})
            GROUP BY fila_id";
        $stmtEsp = $PDO->prepare($sqlEsp);
        $stmtEsp->execute($espBind['params']);
        foreach ($stmtEsp->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $esperaPorFila[(int) $row['fila_id']] = [
                'qtd' => (int) $row['qtd'],
                'tempo' => cnfDashTrimTime(isset($row['tempo_espera']) ? $row['tempo_espera'] : null),
            ];
        }
    }

    $allTeam = cnfDashLoadTeam($PDO, 0, $qryUserCtt, $atendSql, $cttParams);
    $teamByFila = cnfDashGroupTeam($allTeam);
    $allFilaItens = cnfDashLoadAllFilaItens($PDO);
    $filaItensByFila = cnfDashGroupFilaItens($allFilaItens);
    $geralFilaItens = cnfDashFilaItensView($allFilaItens, true);

    $geralTaTe = cnfDashLoadTaTeStats($PDO, 0, $qryContrato, $cttParams);
    $geralAcessos = cnfDashLoadAcessosUnicos($PDO, 0, $qryContrato, $cttParams);
    $taTePorFila = cnfDashLoadTaTeStatsPorFila($PDO, $filaIds, $qryContrato, $cttParams);

    $filasOut = [];
    foreach ($filas as $f) {
        $fid = (int) $f['id_fila'];
        $st = $statsPorFila[$fid] ?? [];
        $espera = isset($esperaPorFila[$fid]) ? $esperaPorFila[$fid] : ['qtd' => 0, 'tempo' => '--:--:--'];

        $cttId = (int) $f['contrato_id'];
        $taTeFila = $taTePorFila[$fid] ?? [
            'menor_espera' => '--:--:--',
            'maior_espera' => '--:--:--',
            'atend_rapido' => '--:--:--',
            'maior_atend' => '--:--:--',
        ];

        $filasOut[] = [
            'id_fila' => $fid,
            'nome_fila' => $f['nome_fila'],
            'contrato_id' => $cttId,
            'nome_contrato' => $f['nome_contrato'],
            'ativo' => (int) $f['ativo'],
            'bko_online' => $bkoOnlinePorFila[$fid] ?? 0,
            'em_fila' => (int) ($st['em_fila'] ?? 0),
            'em_atend' => (int) ($st['em_atend'] ?? 0),
            'concluidos' => (int) ($st['concluidos'] ?? 0),
            'espera_qtd' => $espera['qtd'],
            'espera_tempo' => $espera['tempo'],
            'fila_itens' => cnfDashFilaItensView(isset($filaItensByFila[$fid]) ? $filaItensByFila[$fid] : [], false),
            'team' => isset($teamByFila[$fid]) ? $teamByFila[$fid] : [],
            'indicadores' => cnfDashMergeIndicadores([
                'bko_online' => $bkoOnlinePorFila[$fid] ?? 0,
                'em_fila' => (int) ($st['em_fila'] ?? 0),
                'em_atend' => (int) ($st['em_atend'] ?? 0),
                'pendencias_fila' => $pendTotalPorFila[$fid] ?? 0,
                'concluidos' => (int) ($st['concluidos'] ?? 0),
                'pendencias' => $pendHojePorFila[$fid] ?? 0,
                'tma' => cnfDashTrimTime(isset($st['tma']) ? $st['tma'] : null),
                'tme' => cnfDashTrimTime(isset($st['tme']) ? $st['tme'] : null),
            ], $taTeFila),
        ];
    }

    echo json_encode([
        'ok' => true,
        'ts' => date('d/m/Y H:i:s'),
        'geral' => [
            'bko_online' => $bkoOnlineGeral,
            'em_fila' => (int) ($geralRow['em_fila'] ?? 0),
            'em_atend' => (int) ($geralRow['em_atend'] ?? 0),
            'concluidos' => (int) ($geralRow['concluidos'] ?? 0),
            'pendencias' => (int) ($geralRow['pendencias'] ?? 0),
            'tma' => cnfDashTrimTime(isset($geralRow['tma']) ? $geralRow['tma'] : null),
            'tme' => cnfDashTrimTime(isset($geralRow['tme']) ? $geralRow['tme'] : null),
            'espera_qtd' => $esperaGeral['qtd'],
            'espera_tempo' => $esperaGeral['tempo'],
            'team' => $allTeam,
            'fila_itens' => $geralFilaItens,
            'indicadores' => cnfDashMergeIndicadores([
                'bko_online' => $bkoOnlineGeral,
                'em_fila' => (int) ($geralRow['em_fila'] ?? 0),
                'em_atend' => (int) ($geralRow['em_atend'] ?? 0),
                'acessos_unicos' => $geralAcessos,
                'concluidos' => (int) ($geralRow['concluidos'] ?? 0),
                'pendencias' => (int) ($geralRow['pendencias'] ?? 0),
                'tma' => cnfDashTrimTime(isset($geralRow['tma']) ? $geralRow['tma'] : null),
                'tme' => cnfDashTrimTime(isset($geralRow['tme']) ? $geralRow['tme'] : null),
            ], $geralTaTe),
        ],
        'filas' => $filasOut,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erro ao carregar indicadores']);
}
