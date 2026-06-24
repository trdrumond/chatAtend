<?php
include('../cnf/session.php');
include('../cnf/rotina_pendencia.php');

$userId = (int)($_POST['user'] ?? 0);
if ($userId <= 0) {
    $userId = (int)($_SESSION['dados']['id_user'] ?? 0);
}
if ((int)($_SESSION['dados']['nivel_id'] ?? 0) === 4 && $userId !== (int)$_SESSION['dados']['id_user']) {
    $userId = (int)$_SESSION['dados']['id_user'];
}

$referencia = isset($_POST['referencia']) && preg_match('/^\d{4}-\d{2}$/', (string)$_POST['referencia'])
    ? (string)$_POST['referencia']
    : date('Y-m');

if ($userId <= 0) {
    echo '<div class="st-score-empty">Não foi possível carregar os dados do colaborador.</div>';
    exit;
}

function stScoreFmtTime(?string $value, string $default = '--:--:--'): string
{
    if ($value === null || $value === '') {
        return $default;
    }
    $parts = explode('.', (string)$value);
    return $parts[0] !== '' ? $parts[0] : $default;
}

function stScoreFmtRef(string $ref): string
{
    $ts = strtotime($ref . '-01');
    if (!$ts) {
        return $ref;
    }
    $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    return ($meses[(int)date('n', $ts)] ?? '') . ' / ' . date('Y', $ts);
}

function stScoreHiValid(string $alias): string
{
    return $alias . '.hora_inicio IS NOT NULL AND ' . $alias . ".hora_inicio <> '' AND " . $alias . ".hora_inicio <> '0000-00-00 00:00:00'";
}

$stmtUser = $PDO->prepare(
    'SELECT contrato_id AS ctt, fila_id, CONCAT(nome, \' \', sobrenome) AS nome_completo,'
        . ' (SELECT nome_fila FROM tbl_config_fila WHERE id_fila = tbl_user.fila_id LIMIT 1) AS nome_fila'
        . ' FROM tbl_user WHERE id_user = ? LIMIT 1'
);
$stmtUser->execute([$userId]);
$infoCtt = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [];
$cttId = (int)($infoCtt['ctt'] ?? 0);
$filaId = (int)($infoCtt['fila_id'] ?? 0);

$hiValidCf = stScoreHiValid('cf');
$hiValidCs = stScoreHiValid('cs');

$sqlUnifiedUserMes = ''
    . ' SELECT cf.id_fila_chat, cf.hora_inicio, cf.ta, cf.te, cf.assunto_id, cf.protocolo, cf.hora_fim, cf.status_fila'
    . ' FROM tbl_chat_fila cf'
    . ' WHERE cf.bko_resp = ? AND ' . $hiValidCf
    . '   AND DATE_FORMAT(cf.hora_inicio, \'%Y-%m\') = ?'
    . ' UNION ALL'
    . ' SELECT cs.id_fila_chat, cs.hora_inicio, cs.ta, cs.te, cs.assunto_id, cs.protocolo, cs.hora_fim, cs.status_fila'
    . ' FROM tbl_chat_fila_secondary cs'
    . ' WHERE cs.bko_resp = ? AND ' . $hiValidCs
    . '   AND DATE_FORMAT(cs.hora_inicio, \'%Y-%m\') = ?'
    . '   AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)';

$sqlUnifiedUserDia = ''
    . ' SELECT cf.id_fila_chat, cf.ta'
    . ' FROM tbl_chat_fila cf'
    . ' WHERE cf.bko_resp = ? AND ' . $hiValidCf
    . '   AND DATE(cf.hora_inicio) = CURDATE()'
    . ' UNION ALL'
    . ' SELECT cs.id_fila_chat, cs.ta'
    . ' FROM tbl_chat_fila_secondary cs'
    . ' WHERE cs.bko_resp = ? AND ' . $hiValidCs
    . '   AND DATE(cs.hora_inicio) = CURDATE()'
    . '   AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)';

$sqlUnifiedFilaMes = ''
    . ' SELECT cf.id_fila_chat, cf.bko_resp, cf.ta'
    . ' FROM tbl_chat_fila cf'
    . ' WHERE cf.ta <> \'\' AND cf.hora_fim IS NOT NULL'
    . '   AND DATE_FORMAT(cf.hora_fim, \'%Y-%m\') = ? AND cf.contrato_id = ? AND cf.fila_id = ?'
    . ' UNION ALL'
    . ' SELECT cs.id_fila_chat, cs.bko_resp, cs.ta'
    . ' FROM tbl_chat_fila_secondary cs'
    . ' WHERE cs.ta <> \'\' AND cs.hora_fim IS NOT NULL'
    . '   AND DATE_FORMAT(cs.hora_fim, \'%Y-%m\') = ? AND cs.contrato_id = ? AND cs.fila_id = ?'
    . '   AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)';

$sqlUnifiedFilaRank = ''
    . ' SELECT cf.id_fila_chat, cf.bko_resp'
    . ' FROM tbl_chat_fila cf'
    . ' WHERE ' . $hiValidCf
    . '   AND DATE_FORMAT(cf.hora_inicio, \'%Y-%m\') = ? AND cf.contrato_id = ? AND cf.fila_id = ? AND cf.bko_resp > 0'
    . ' UNION ALL'
    . ' SELECT cs.id_fila_chat, cs.bko_resp'
    . ' FROM tbl_chat_fila_secondary cs'
    . ' WHERE ' . $hiValidCs
    . '   AND DATE_FORMAT(cs.hora_inicio, \'%Y-%m\') = ? AND cs.contrato_id = ? AND cs.fila_id = ? AND cs.bko_resp > 0'
    . '   AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)';

$stmtMes = $PDO->prepare(
    'SELECT COUNT(*) AS qtd,'
        . ' SEC_TO_TIME(AVG(TIME_TO_SEC(ta))) AS tma,'
        . ' SEC_TO_TIME(AVG(TIME_TO_SEC(te))) AS tme,'
        . ' SEC_TO_TIME(SUM(TIME_TO_SEC(ta))) AS prod,'
        . ' AVG(TIME_TO_SEC(ta)) AS tma_sec'
        . ' FROM (' . $sqlUnifiedUserMes . ') AS score_unificado'
);
$stmtMes->execute([$userId, $referencia, $userId, $referencia]);
$mes = $stmtMes->fetch(PDO::FETCH_ASSOC) ?: [];

$stmtDia = $PDO->prepare(
    'SELECT COUNT(*) AS qtd, SEC_TO_TIME(SUM(TIME_TO_SEC(ta))) AS prod'
        . ' FROM (' . $sqlUnifiedUserDia . ') AS dia_unificado'
);
$stmtDia->execute([$userId, $userId]);
$dia = $stmtDia->fetch(PDO::FETCH_ASSOC) ?: [];

$stmtFila = $PDO->prepare(
    'SELECT COUNT(*) AS qtd, SEC_TO_TIME(AVG(TIME_TO_SEC(ta))) AS tma, AVG(TIME_TO_SEC(ta)) AS tma_sec'
        . ' FROM (' . $sqlUnifiedFilaMes . ') AS fila_unificado'
);
$stmtFila->execute([$referencia, $cttId, $filaId, $referencia, $cttId, $filaId]);
$filaGeral = $stmtFila->fetch(PDO::FETCH_ASSOC) ?: [];

$stmtDiario = $PDO->prepare(
    'SELECT DATE_FORMAT(hora_inicio, \'%Y-%m-%d\') AS data, COUNT(*) AS qtd'
        . ' FROM (' . $sqlUnifiedUserMes . ') AS score_unificado'
        . ' GROUP BY DATE_FORMAT(hora_inicio, \'%Y-%m-%d\') ORDER BY data ASC'
);
$stmtDiario->execute([$userId, $referencia, $userId, $referencia]);
$ddScore = $stmtDiario->fetchAll(PDO::FETCH_ASSOC);

$chartMax = 0;
foreach ($ddScore as $row) {
    $chartMax = max($chartMax, (int)$row['qtd']);
}
$chartMax = max($chartMax, 5);

$stmtRank = $PDO->prepare(
    'SELECT bko_resp, COUNT(*) AS qtd'
        . ' FROM (' . $sqlUnifiedFilaRank . ') AS rank_unificado'
        . ' GROUP BY bko_resp ORDER BY qtd DESC, bko_resp ASC'
);
$stmtRank->execute([$referencia, $cttId, $filaId, $referencia, $cttId, $filaId]);
$rankRows = $stmtRank->fetchAll(PDO::FETCH_ASSOC);
$rankPos = 0;
$rankTotal = count($rankRows);
foreach ($rankRows as $i => $row) {
    if ((int)$row['bko_resp'] === $userId) {
        $rankPos = $i + 1;
        break;
    }
}

$stmtPend = $PDO->prepare(
    'SELECT COUNT(*) AS qtd FROM tbl_pend_info'
        . ' WHERE bko_resp = ? AND situacao_id = 3 AND data_hora_fim IS NULL'
);
$stmtPend->execute([$userId]);
$pendAbertas = (int)($stmtPend->fetchColumn() ?: 0);

// Mesmo cálculo de load_star.php (#star no perfil).
$dayStar = (date('Y-m-d') < '2021-12-06') ? 1 : 5;

$stmtStar = $PDO->prepare(
    'SELECT FORMAT(AVG(star), 1) AS media, COUNT(*) AS total'
    . ' FROM tbl_classificacao'
    . ' WHERE ate = ? AND star IS NOT NULL AND star <> \'\''
    . ' AND DATE_FORMAT(data_hora, \'%Y-%m-%d\') BETWEEN \'0001-01-01\' AND DATE_SUB(CURDATE(), INTERVAL ? DAY)'
);
$stmtStar->execute([$userId, $dayStar]);
$starInfo = $stmtStar->fetch(PDO::FETCH_ASSOC) ?: [];
$starMedia = (string)($starInfo['media'] ?? '');
$starTotal = (int)($starInfo['total'] ?? 0);
$starMedia = (date('Y-m-d') < '2021-12-11' && $starMedia !== '' && $starMedia < '2.5') ? ' -.- ' : $starMedia;
$starMedia = ($starMedia === '') ? ' -.- ' : $starMedia;

$stmtAssuntos = $PDO->prepare(
    'SELECT assunto_id,'
        . ' (SELECT titulo_assunto FROM tbl_assunto WHERE id_assunto = assunto_id LIMIT 1) AS titulo,'
        . ' COUNT(*) AS qtd'
        . ' FROM (' . $sqlUnifiedUserMes . ') AS score_unificado'
        . ' GROUP BY assunto_id ORDER BY qtd DESC LIMIT 5'
);
$stmtAssuntos->execute([$userId, $referencia, $userId, $referencia]);
$topAssuntos = $stmtAssuntos->fetchAll(PDO::FETCH_ASSOC);

$stmtRecentes = $PDO->prepare(
    'SELECT protocolo, DATE_FORMAT(COALESCE(hora_fim, hora_inicio), \'%d/%m/%Y %H:%i\') AS quando,'
        . ' ta, te, status_fila,'
        . ' (SELECT nome_situacao FROM tbl_situacao_chat WHERE id_situacao = status_fila LIMIT 1) AS situacao'
        . ' FROM (' . $sqlUnifiedUserMes . ') AS score_unificado'
        . ' ORDER BY COALESCE(hora_fim, hora_inicio) DESC LIMIT 6'
);
$stmtRecentes->execute([$userId, $referencia, $userId, $referencia]);
$recentes = $stmtRecentes->fetchAll(PDO::FETCH_ASSOC);

$tmaInd = stScoreFmtTime($mes['tma'] ?? null);
$tmeInd = stScoreFmtTime($mes['tme'] ?? null);
$tmaFila = stScoreFmtTime($filaGeral['tma'] ?? null);
$prodMes = stScoreFmtTime($mes['prod'] ?? null);
$prodDia = stScoreFmtTime($dia['prod'] ?? null);
$qtdMes = (int)($mes['qtd'] ?? 0);
$qtdDia = (int)($dia['qtd'] ?? 0);

$tmaIndSec = (float)($mes['tma_sec'] ?? 0);
$tmaFilaSec = (float)($filaGeral['tma_sec'] ?? 0);
$tmaDelta = '';
$tmaDeltaClass = 'st-score-kpi__delta--neutral';
if ($tmaIndSec > 0 && $tmaFilaSec > 0) {
    $diffPct = round((($tmaIndSec - $tmaFilaSec) / $tmaFilaSec) * 100);
    if ($diffPct < 0) {
        $tmaDelta = abs($diffPct) . '% abaixo da fila';
        $tmaDeltaClass = 'st-score-kpi__delta--good';
    } elseif ($diffPct > 0) {
        $tmaDelta = $diffPct . '% acima da fila';
        $tmaDeltaClass = 'st-score-kpi__delta--warn';
    } else {
        $tmaDelta = 'Igual à média da fila';
    }
}

$mediaDia = $qtdMes > 0 ? number_format($qtdMes / max(1, count($ddScore)), 1, ',', '.') : '0';
$chartUid = 'sc' . $userId . '_' . str_replace('-', '', $referencia);
$refLabel = stScoreFmtRef($referencia);
?>

<style>
    .st-score-dashboard {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .st-score-ref {
        font-size: 13px;
        color: #666;
        margin: 0;
    }

    .st-score-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .st-score-kpi {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 12px 14px;
        min-height: 88px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .st-score-kpi__label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #888;
    }

    .st-score-kpi__value {
        font-size: 22px;
        font-weight: 700;
        color: #222;
        line-height: 1.1;
    }

    .st-score-kpi__value--time {
        font-size: 18px;
    }

    .st-score-kpi__sub {
        font-size: 11px;
        color: #777;
        margin-top: 4px;
    }

    .st-score-kpi__delta {
        font-size: 11px;
        margin-top: 4px;
        font-weight: 600;
    }

    .st-score-kpi__delta--good {
        color: #1e7e34;
    }

    .st-score-kpi__delta--warn {
        color: #c0392b;
    }

    .st-score-kpi__delta--neutral {
        color: #6c757d;
    }

    .st-score-kpi--accent {
        border-color: #B7202F;
        background: linear-gradient(145deg, #fff 0%, #fff7f8 100%);
    }

    .st-score-kpi--stars .st-score-kpi__value {
        color: #c9a100;
    }

    .st-score-panels {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 12px;
    }

    .st-score-panels--chart {
        grid-template-columns: 1fr;
    }

    .st-score-panel {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 14px;
    }

    .st-score-panel--chart {
        padding: 16px 18px 12px;
    }

    .st-score-panel h5 {
        margin: 0 0 10px;
        font-size: 13px;
        font-weight: 700;
        color: #444;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .st-score-chart {
        width: 100%;
        height: 260px;
    }

    .st-score-chart--main {
        height: min(420px, 48vh);
        min-height: 320px;
    }

    .st-score-chart--sm {
        height: 200px;
    }

    .st-score-table {
        width: 100%;
        font-size: 12px;
        border-collapse: collapse;
    }

    .st-score-table th,
    .st-score-table td {
        padding: 7px 8px;
        border-bottom: 1px solid #efefef;
        text-align: left;
    }

    .st-score-table th {
        color: #777;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
    }

    .st-score-table tr:last-child td {
        border-bottom: none;
    }

    .st-score-rank {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: #f4f6f8;
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }

    .st-score-empty {
        padding: 24px;
        text-align: center;
        color: #777;
    }

    @media (max-width: 1100px) {
        .st-score-panels {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="st-score-dashboard">
    <p class="st-score-ref">
        Referência: <strong><?= htmlspecialchars($refLabel) ?></strong>
        <?php if ($rankPos > 0 && $rankTotal > 1) { ?>
            · <span class="st-score-rank"><i class="fas fa-trophy" style="color:#c9a100"></i> <?= (int)$rankPos ?>º de <?= (int)$rankTotal ?> na fila</span>
        <?php } ?>
    </p>

    <div class="st-score-kpis">
        <div class="st-score-kpi st-score-kpi--accent">
            <span class="st-score-kpi__label">Atendimentos no mês</span>
            <span class="st-score-kpi__value"><?= (int)$qtdMes ?></span>
            <span class="st-score-kpi__sub"><?= (int)$qtdDia ?> hoje · média <?= htmlspecialchars($mediaDia) ?>/dia</span>
        </div>
        <div class="st-score-kpi">
            <span class="st-score-kpi__label">TMA individual</span>
            <span class="st-score-kpi__value st-score-kpi__value--time"><?= htmlspecialchars($tmaInd) ?></span>
            <?php if ($tmaDelta !== '') { ?>
                <span class="st-score-kpi__delta <?= htmlspecialchars($tmaDeltaClass) ?>"><?= htmlspecialchars($tmaDelta) ?></span>
            <?php } else { ?>
                <span class="st-score-kpi__sub">Fila: <?= htmlspecialchars($tmaFila) ?></span>
            <?php } ?>
        </div>
        <div class="st-score-kpi">
            <span class="st-score-kpi__label">TME médio</span>
            <span class="st-score-kpi__value st-score-kpi__value--time"><?= htmlspecialchars($tmeInd) ?></span>
            <span class="st-score-kpi__sub">Tempo médio em espera</span>
        </div>
        <div class="st-score-kpi st-score-kpi--stars">
            <span class="st-score-kpi__label">Classificação</span>
            <span class="st-score-kpi__value"><?= htmlspecialchars($starMedia) ?> <i class="fas fa-star" style="font-size:16px"></i></span>
            <span class="st-score-kpi__sub"><?= (int)$starTotal ?> avaliação(ões) · últimos <?= (int)$dayStar ?> dias não entram</span>
        </div>
        <div class="st-score-kpi">
            <span class="st-score-kpi__label">Produtividade hoje</span>
            <span class="st-score-kpi__value st-score-kpi__value--time"><?= htmlspecialchars($prodDia) ?></span>
            <span class="st-score-kpi__sub">Soma do TA no dia</span>
        </div>
        <div class="st-score-kpi">
            <span class="st-score-kpi__label">Produtividade mês</span>
            <span class="st-score-kpi__value st-score-kpi__value--time"><?= htmlspecialchars($prodMes) ?></span>
            <span class="st-score-kpi__sub">Soma do TA no período</span>
        </div>
        <div class="st-score-kpi">
            <span class="st-score-kpi__label">Pendências abertas</span>
            <span class="st-score-kpi__value"><?= (int)$pendAbertas ?></span>
            <span class="st-score-kpi__sub">Aguardando sua ação</span>
        </div>
        <div class="st-score-kpi">
            <span class="st-score-kpi__label">TMA da fila</span>
            <span class="st-score-kpi__value st-score-kpi__value--time"><?= htmlspecialchars($tmaFila) ?></span>
            <span class="st-score-kpi__sub"><?= (int)($filaGeral['qtd'] ?? 0) ?> atend. no período</span>
        </div>
    </div>

    <div class="st-score-panels st-score-panels--chart">
        <div class="st-score-panel st-score-panel--chart">
            <h5><i class="fas fa-chart-column"></i> Atendimentos por dia</h5>
            <div id="chart_score_bar_<?= htmlspecialchars($chartUid) ?>" class="st-score-chart st-score-chart--main"></div>
        </div>
    </div>

    <div class="st-score-panels">
        <div class="st-score-panel">
            <h5><i class="fas fa-tags"></i> Principais assuntos</h5>
            <?php if (count($topAssuntos) === 0) { ?>
                <p class="st-score-empty">Nenhum assunto registrado no período.</p>
            <?php } else { ?>
                <table class="st-score-table">
                    <thead>
                        <tr>
                            <th>Assunto</th>
                            <th>Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topAssuntos as $ass) { ?>
                            <tr>
                                <td><?= htmlspecialchars(ucwords((string)($ass['titulo'] ?? 'Sem assunto'))) ?></td>
                                <td><strong><?= (int)$ass['qtd'] ?></strong></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
        <div class="st-score-panel">
            <h5><i class="fas fa-history"></i> Últimos atendimentos</h5>
            <?php if (count($recentes) === 0) { ?>
                <p class="st-score-empty">Nenhum atendimento no período.</p>
            <?php } else { ?>
                <table class="st-score-table">
                    <thead>
                        <tr>
                            <th>Protocolo</th>
                            <th>TA</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentes as $item) { ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars((string)$item['protocolo']) ?></strong><br>
                                    <small><?= htmlspecialchars((string)$item['quando']) ?></small>
                                </td>
                                <td><?= htmlspecialchars(stScoreFmtTime($item['ta'] ?? null, '—')) ?></td>
                                <td><?= htmlspecialchars(ucwords((string)($item['situacao'] ?? '—'))) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</div>

<?php if (count($ddScore) > 0) { ?>
    <script>
        (function() {
            if (typeof am4core === 'undefined') {
                return;
            }
            am4core.useTheme(am4themes_animated);
            am4core.options.autoDispose = true;

            var chartBar = am4core.create('chart_score_bar_<?= $chartUid ?>', am4charts.XYChart);
            chartBar.data = [
                <?php foreach ($ddScore as $row) { ?> {
                        dia: '<?= date('d/m', strtotime($row['data'])) ?>',
                        qtd: <?= (int)$row['qtd'] ?>
                    },
                <?php } ?>
            ];
            chartBar.padding(20, 16, 12, 8);
            var catAxis = chartBar.xAxes.push(new am4charts.CategoryAxis());
            catAxis.dataFields.category = 'dia';
            catAxis.renderer.minGridDistance = 28;
            catAxis.renderer.grid.template.disabled = true;
            var valAxis = chartBar.yAxes.push(new am4charts.ValueAxis());
            valAxis.min = 0;
            valAxis.max = <?= (int)$chartMax ?>;
            valAxis.strictMinMax = true;
            var seriesBar = chartBar.series.push(new am4charts.ColumnSeries());
            seriesBar.dataFields.categoryX = 'dia';
            seriesBar.dataFields.valueY = 'qtd';
            seriesBar.columns.template.fill = am4core.color('#B7202F');
            seriesBar.columns.template.strokeOpacity = 0;
            seriesBar.columns.template.column.cornerRadiusTopLeft = 4;
            seriesBar.columns.template.column.cornerRadiusTopRight = 4;
            var bullet = seriesBar.bullets.push(new am4charts.LabelBullet());
            bullet.label.text = '{valueY}';
            bullet.label.dy = -14;
            bullet.label.fontSize = 11;
        })();
    </script>
<?php } else { ?>
    <p class="st-score-empty">Sem atendimentos diários no período.</p>
<?php } ?>