<?php
require_once __DIR__ . '/../../../cnf/session.php';
if (!isset($infoUser) || !is_array($infoUser)) {
    $infoUser = [];
}
if (!isset($infoUserConfig) || !is_array($infoUserConfig)) {
    $infoUserConfig = ['contrato_id' => '0'];
}

$contratoIn = $infoUserConfig['contrato_id'];
$qryContrato = ($infoUser['nivel_id'] > 0) ? " AND contrato_id IN ($contratoIn)" : '';
$qryUser     = ($infoUser['nivel_id'] > 0) ? " AND contrato_id IN ($contratoIn)" : '';
$qryFila     = ($infoUser['nivel_id'] > 0) ? " AND contrato_id IN ($contratoIn)" : '';
$qryAssunto  = ($infoUser['nivel_id'] > 0) ? " AND contrato_id IN ($contratoIn)" : '';
$qryFaq      = ($infoUser['nivel_id'] > 0) ? " AND contrato_id IN ($contratoIn)" : '';

function cnfDashScalar(PDO $pdo, string $sql): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['total'] ?? 0);
}

$total_bko = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_user WHERE ativo=1 AND nivel_id=4 $qryUser");
$total_sol = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_user WHERE ativo=1 AND nivel_id=5 $qryUser");
$total_aco = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_user WHERE ativo=1 AND nivel_id=2 $qryUser");
$total_adm = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_user WHERE ativo=1 AND nivel_id IN (0,1,3) $qryUser");
$total_usuarios = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_user WHERE ativo=1 $qryUser");
$total_dia = cnfDashScalar($PDO, "SELECT COUNT(DISTINCT user_id) AS total FROM tbl_log_diario WHERE data_log=CURDATE() $qryContrato");
$total_online_bko = cnfDashScalar($PDO, "SELECT COUNT(DISTINCT user_id) AS total FROM tbl_log_diario WHERE data_log=CURDATE() AND date_out IS NULL AND nivel_id=4 $qryContrato");

$total_fila_ativa   = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_config_fila WHERE ativo=1 $qryFila");
$total_fila_inativa = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_config_fila WHERE ativo=0 $qryFila");
$total_contratos    = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_contrato WHERE ativo=1" . ($infoUser['nivel_id'] > 0 ? " AND id_contrato IN ($contratoIn)" : ''));
$total_assuntos     = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_assunto WHERE ativo=1 $qryAssunto");
$total_faqs         = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_faq WHERE ativo=1 $qryFaq");
$total_agencias     = cnfDashScalar($PDO, "SELECT COUNT(*) AS total FROM tbl_agencia WHERE ativo=1" . ($infoUser['nivel_id'] > 0 ? " AND contrato_id IN ($contratoIn)" : ''));

$ops_hoje = $PDO->query(
    "SELECT
        (SELECT COUNT(*) FROM tbl_chat_fila WHERE status_fila=" . ST_FILA_NA_FILA . " $qryContrato) AS em_fila,
        (SELECT COUNT(*) FROM tbl_chat_fila WHERE " . stFilaSqlAtendimentoAtivo() . " $qryContrato) AS em_atend,
        (SELECT COUNT(*) FROM tbl_chat_fila WHERE status_fila>=" . ST_FILA_CONCLUIDO . " AND hora_inicio>=CURDATE() AND hora_inicio<DATE_ADD(CURDATE(), INTERVAL 1 DAY) $qryContrato) AS concluidos,
        (SELECT COUNT(*) FROM tbl_pend_info WHERE situacao_id=3 AND data_hora_fim IS NULL AND data_hora>=CURDATE() AND data_hora<DATE_ADD(CURDATE(), INTERVAL 1 DAY)) AS pendencias"
)->fetch(PDO::FETCH_ASSOC);

$sqlNiveis = "SELECT n.nome_nivel, COUNT(u.id_user) AS total
    FROM tbl_user u
    INNER JOIN tbl_nivel n ON n.id_nivel = u.nivel_id
    WHERE u.ativo=1 $qryUser
    GROUP BY u.nivel_id, n.nome_nivel
    ORDER BY total DESC";
$porNivel = $PDO->query($sqlNiveis)->fetchAll(PDO::FETCH_ASSOC);

$sqlLogins = "SELECT DATE(data_log) AS dia, COUNT(DISTINCT user_id) AS total
    FROM tbl_log_diario
    WHERE data_log >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) $qryContrato
    GROUP BY DATE(data_log)
    ORDER BY dia ASC";
$logins7d = $PDO->query($sqlLogins)->fetchAll(PDO::FETCH_ASSOC);

$sqlChats = "SELECT DATE(hora_inicio) AS dia, COUNT(*) AS total
    FROM tbl_chat_fila
    WHERE status_fila >= 4
      AND hora_inicio >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      AND hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
      $qryContrato
    GROUP BY DATE(hora_inicio)
    ORDER BY dia ASC";
$chats7d = $PDO->query($sqlChats)->fetchAll(PDO::FETCH_ASSOC);

$sqlTopFilas = "SELECT f.nome_fila, COUNT(c.id_fila_chat) AS qtd
    FROM tbl_chat_fila c
    INNER JOIN tbl_config_fila f ON f.id_fila = c.fila_id
    WHERE c.hora_inicio >= CURDATE()
      AND c.hora_inicio < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
      $qryContrato
    GROUP BY c.fila_id, f.nome_fila
    ORDER BY qtd DESC
    LIMIT 6";
$topFilas = $PDO->query($sqlTopFilas)->fetchAll(PDO::FETCH_ASSOC);

$sqlUltimos = "SELECT DISTINCT l.user_id,
        (SELECT CONCAT(nome, ' ', sobrenome) FROM tbl_user WHERE id_user=l.user_id) AS nome,
        (SELECT nome_nivel FROM tbl_nivel WHERE id_nivel=l.nivel_id) AS nivel,
        DATE_FORMAT(l.date_up, '%d/%m %H:%i') AS ultimo_acesso
    FROM tbl_log_diario l
    WHERE l.data_log = CURDATE() $qryContrato
    ORDER BY l.date_up DESC
    LIMIT 8";
$ultimosAcessos = $PDO->query($sqlUltimos)->fetchAll(PDO::FETCH_ASSOC);

$chartNivel = [];
foreach ($porNivel as $row) {
    $chartNivel[] = [$row['nome_nivel'], (int) $row['total']];
}

$chartLogins = [];
foreach ($logins7d as $row) {
    $chartLogins[] = [date('d/m', strtotime($row['dia'])), (int) $row['total']];
}

$chartChats = [];
foreach ($chats7d as $row) {
    $chartChats[] = [date('d/m', strtotime($row['dia'])), (int) $row['total']];
}

$chartFilasStatus = [
    ['Ativas', $total_fila_ativa],
    ['Inativas', $total_fila_inativa],
];

$chartOpsHoje = [
    ['Em fila', (int) ($ops_hoje['em_fila'] ?? 0)],
    ['Em atend.', (int) ($ops_hoje['em_atend'] ?? 0)],
    ['Concluídos', (int) ($ops_hoje['concluidos'] ?? 0)],
    ['Pendências', (int) ($ops_hoje['pendencias'] ?? 0)],
];

$dataAtual = date('d/m/Y H:i');
?>

<div class="cnf-dash">
    <header class="cnf-dash-header">
        <div>
            <h5 class="cnf-dash-title">Dashboard de Configurações</h5>
            <p class="cnf-dash-sub">Visão geral do ambiente · atualizado em <?= htmlspecialchars($dataAtual) ?></p>
        </div>
        <span class="cnf-dash-badge"><i class="fas fa-shield-alt"></i> Painel administrativo</span>
    </header>

    <section class="cnf-dash-section">
        <h6 class="cnf-dash-section-title">Usuários</h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="cnf-kpi cnf-kpi--navy">
                    <i class="fas fa-users cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_usuarios ?></span>
                    <span class="cnf-kpi-label">Ativos (total)</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="cnf-kpi cnf-kpi--magenta">
                    <i class="fas fa-headset cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_bko ?></span>
                    <span class="cnf-kpi-label">Backoffice</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="cnf-kpi cnf-kpi--sky">
                    <i class="fas fa-user-tag cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_sol ?></span>
                    <span class="cnf-kpi-label">Solicitantes</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="cnf-kpi cnf-kpi--coral">
                    <i class="fas fa-user-friends cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_aco ?></span>
                    <span class="cnf-kpi-label">Acompanhamento</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="cnf-kpi cnf-kpi--purple">
                    <i class="fas fa-user-cog cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_adm ?></span>
                    <span class="cnf-kpi-label">Administradores</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="cnf-kpi cnf-kpi--green">
                    <i class="fas fa-signal cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_online_bko ?></span>
                    <span class="cnf-kpi-label">BKO online agora</span>
                </div>
            </div>
        </div>
    </section>

    <section class="cnf-dash-section">
        <h6 class="cnf-dash-section-title">Operação hoje</h6>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="cnf-kpi cnf-kpi--outline">
                    <span class="cnf-kpi-value"><?= (int) ($ops_hoje['em_fila'] ?? 0) ?></span>
                    <span class="cnf-kpi-label">Chats em fila</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cnf-kpi cnf-kpi--outline">
                    <span class="cnf-kpi-value"><?= (int) ($ops_hoje['em_atend'] ?? 0) ?></span>
                    <span class="cnf-kpi-label">Em atendimento</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cnf-kpi cnf-kpi--outline">
                    <span class="cnf-kpi-value"><?= (int) ($ops_hoje['concluidos'] ?? 0) ?></span>
                    <span class="cnf-kpi-label">Concluídos hoje</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cnf-kpi cnf-kpi--outline">
                    <span class="cnf-kpi-value"><?= (int) ($ops_hoje['pendencias'] ?? 0) ?></span>
                    <span class="cnf-kpi-label">Pendências abertas</span>
                </div>
            </div>
        </div>
    </section>

    <section class="cnf-dash-section">
        <h6 class="cnf-dash-section-title">Cadastros do sistema</h6>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cnf-kpi cnf-kpi--soft">
                    <i class="fas fa-building cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_contratos ?></span>
                    <span class="cnf-kpi-label">Contratos</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cnf-kpi cnf-kpi--soft">
                    <i class="fas fa-list-alt cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_fila_ativa ?></span>
                    <span class="cnf-kpi-label">Filas ativas</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cnf-kpi cnf-kpi--soft">
                    <i class="fas fa-list cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_fila_inativa ?></span>
                    <span class="cnf-kpi-label">Filas inativas</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cnf-kpi cnf-kpi--soft">
                    <i class="fas fa-bookmark cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_assuntos ?></span>
                    <span class="cnf-kpi-label">Assuntos</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cnf-kpi cnf-kpi--soft">
                    <i class="fas fa-question-circle cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_faqs ?></span>
                    <span class="cnf-kpi-label">FAQs</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="cnf-kpi cnf-kpi--soft">
                    <i class="fas fa-map-marker-alt cnf-kpi-icon"></i>
                    <span class="cnf-kpi-value"><?= $total_agencias ?></span>
                    <span class="cnf-kpi-label">Agências</span>
                </div>
            </div>
        </div>
    </section>

    <section class="cnf-dash-section">
        <h6 class="cnf-dash-section-title">Gráficos</h6>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="cnf-chart-card">
                    <h6>Usuários por perfil</h6>
                    <div id="cnfChartNivel" class="cnf-chart-area"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cnf-chart-card">
                    <h6>Status das filas</h6>
                    <div id="cnfChartFilas" class="cnf-chart-area"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cnf-chart-card">
                    <h6>Acessos únicos — últimos 7 dias</h6>
                    <div id="cnfChartLogins" class="cnf-chart-area"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cnf-chart-card">
                    <h6>Chats concluídos — últimos 7 dias</h6>
                    <div id="cnfChartChats" class="cnf-chart-area"></div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="cnf-chart-card">
                    <h6>Operação em tempo real (hoje)</h6>
                    <div id="cnfChartOps" class="cnf-chart-area cnf-chart-area--wide"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="cnf-dash-section">
        <h6 class="cnf-dash-section-title">Detalhes</h6>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="cnf-table-card">
                    <h6><i class="fas fa-trophy"></i> Top filas hoje (volume)</h6>
                    <?php if (count($topFilas) > 0) { ?>
                    <table class="table table-sm cnf-dash-table mb-0">
                        <thead>
                            <tr><th>Fila</th><th class="text-end">Chats</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topFilas as $fila) { ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['nome_fila']) ?></td>
                                <td class="text-end"><strong><?= (int) $fila['qtd'] ?></strong></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } else { ?>
                    <p class="cnf-dash-empty">Nenhum chat registrado hoje.</p>
                    <?php } ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="cnf-table-card">
                    <h6><i class="fas fa-clock"></i> Últimos acessos hoje (<?= $total_dia ?> usuários)</h6>
                    <?php if (count($ultimosAcessos) > 0) { ?>
                    <table class="table table-sm cnf-dash-table mb-0">
                        <thead>
                            <tr><th>Usuário</th><th>Perfil</th><th class="text-end">Último ping</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimosAcessos as $ac) { ?>
                            <tr>
                                <td><?= htmlspecialchars($ac['nome'] ?? '—') ?></td>
                                <td><span class="cnf-dash-tag"><?= htmlspecialchars($ac['nivel'] ?? '—') ?></span></td>
                                <td class="text-end text-muted"><?= htmlspecialchars($ac['ultimo_acesso'] ?? '—') ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } else { ?>
                    <p class="cnf-dash-empty">Sem acessos registrados hoje.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://www.gstatic.com/charts/loader.js"></script>
<script>
(function () {
    var chartNivel  = <?= json_encode($chartNivel, JSON_UNESCAPED_UNICODE) ?>;
    var chartFilas  = <?= json_encode($chartFilasStatus, JSON_UNESCAPED_UNICODE) ?>;
    var chartLogins = <?= json_encode($chartLogins, JSON_UNESCAPED_UNICODE) ?>;
    var chartChats  = <?= json_encode($chartChats, JSON_UNESCAPED_UNICODE) ?>;
    var chartOps    = <?= json_encode($chartOpsHoje, JSON_UNESCAPED_UNICODE) ?>;

    var colors = ['#28285E', '#D6336C', '#FA5252', '#5ba4c4', '#C1E7F5', '#2FAB4E', '#e67700'];

    function emptyRow(msg) {
        return [['—', 0], [msg, 0.0001]];
    }

    google.charts.load('current', { packages: ['corechart'] });
    google.charts.setOnLoadCallback(drawAll);

    function drawPie(elId, rows, title) {
        var dataRows = rows.length ? rows : emptyRow('Sem dados');
        var data = google.visualization.arrayToDataTable([['Item', 'Qtd']].concat(dataRows));
        var chart = new google.visualization.PieChart(document.getElementById(elId));
        chart.draw(data, {
            title: title,
            pieHole: 0.42,
            colors: colors,
            chartArea: { width: '88%', height: '72%' },
            legend: { position: 'bottom', maxLines: 2 },
            fontName: 'Inter, Segoe UI, sans-serif',
            backgroundColor: 'transparent'
        });
    }

    function drawColumn(elId, rows, title) {
        var dataRows = rows.length ? rows : [['—', 0]];
        var data = google.visualization.arrayToDataTable([['Período', 'Qtd']].concat(dataRows));
        var chart = new google.visualization.ColumnChart(document.getElementById(elId));
        chart.draw(data, {
            title: title,
            colors: ['#D6336C'],
            chartArea: { width: '82%', height: '68%' },
            legend: { position: 'none' },
            fontName: 'Inter, Segoe UI, sans-serif',
            backgroundColor: 'transparent',
            hAxis: { textStyle: { fontSize: 11 } },
            vAxis: { minValue: 0, gridlines: { color: '#e8f0f8' } }
        });
    }

    function drawBar(elId, rows, title) {
        var dataRows = rows.length ? rows : [['—', 0]];
        var data = google.visualization.arrayToDataTable([['Situação', 'Qtd']].concat(dataRows));
        var chart = new google.visualization.BarChart(document.getElementById(elId));
        chart.draw(data, {
            title: title,
            colors: ['#28285E', '#D6336C', '#2FAB4E', '#FA5252'],
            chartArea: { width: '70%', height: '65%' },
            legend: { position: 'none' },
            fontName: 'Inter, Segoe UI, sans-serif',
            backgroundColor: 'transparent'
        });
    }

    function drawAll() {
        drawPie('cnfChartNivel', chartNivel, '');
        drawPie('cnfChartFilas', chartFilas, '');
        drawColumn('cnfChartLogins', chartLogins, '');
        drawColumn('cnfChartChats', chartChats, '');
        drawBar('cnfChartOps', chartOps, '');
    }

    window.addEventListener('resize', function () {
        if (typeof google !== 'undefined' && google.visualization) {
            drawAll();
        }
    });
})();
</script>
