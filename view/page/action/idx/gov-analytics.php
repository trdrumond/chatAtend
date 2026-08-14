<?php
require_once __DIR__ . '/../../../cnf/session.php';
require_once __DIR__ . '/../cnf/_cnf_ui.php';
include('cnf/rotina_pendencia.php');

$nivelLogin = (int) ($_SESSION['dados']['nivel_id'] ?? 99);
if ($nivelLogin >= 5) {
    echo '<div class="alert alert-danger">Acesso negado.</div>';
    exit;
}

$dePadrao = date('Y-m-01');
$atePadrao = date('Y-m-d');
$contratoPadrao = ($nivelLogin > 0) ? (int) ($infoUser['contrato_id'] ?? $infoUser['id_contrato'] ?? 0) : 0;
$filaPadrao = 0;

$cttOpts = '<option value="">Todos os contratos</option>';
$sqlCtt = 'SELECT id_contrato, nome_contrato, uf FROM tbl_contrato WHERE ativo = 1';
$cttParams = [];
if ($nivelLogin > 0) {
    $cttIn = stSqlInBind(stParseIdCsv($infoUserConfig['contrato_id'] ?? ''));
    $sqlCtt .= ' AND id_contrato IN (' . $cttIn['ph'] . ')';
    $cttParams = $cttIn['ids'];
}
$sqlCtt .= ' ORDER BY nome_contrato';
$stmt = $PDO->prepare($sqlCtt);
$stmt->execute($cttParams);
$listaContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($nivelLogin > 0 && count($listaContratos) === 1) {
    $contratoPadrao = (int) $listaContratos[0]['id_contrato'];
}
foreach ($listaContratos as $row) {
    $idCtt = (int) $row['id_contrato'];
    $sel = ($contratoPadrao > 0 && $idCtt === $contratoPadrao) ? ' selected' : '';
    $cttOpts .= '<option value="' . $idCtt . '"' . $sel . '>'
        . htmlspecialchars($row['nome_contrato'] . ' - ' . $row['uf']) . '</option>';
}

$filaOpts = '<option value="">Todas as filas</option>';
if ($contratoPadrao > 0) {
    $stmtFila = $PDO->prepare('SELECT id_fila, nome_fila FROM tbl_config_fila WHERE contrato_id = ? ORDER BY nome_fila');
    $stmtFila->execute([$contratoPadrao]);
    foreach ($stmtFila->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $filaOpts .= '<option value="' . (int) $row['id_fila'] . '">'
            . htmlspecialchars($row['nome_fila']) . '</option>';
    }
}

st_page_open('Governança', 'Painel administrativo de atendimentos — indicadores, rankings e tendências');
st_page_header_close();

st_filter_bar_open();
cnf_field_select('gov_contrato', 'Contrato', $cttOpts);
cnf_field_select('gov_fila', 'Fila', $filaOpts);
cnf_field_input('gov_de', 'De', ['type' => 'date', 'value' => $dePadrao, 'required' => true]);
cnf_field_input('gov_ate', 'Até', ['type' => 'date', 'value' => $atePadrao, 'required' => true]);
?>
<button type="button" id="gov_btn_filter" class="btn btn-solvetask btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
<?php
st_filter_bar_close();
?>

<div id="st-gov-root" class="st-gov-dashboard">
    <div id="st-gov-loading" class="st-gov-loading">
        <img src="img/loading.gif" alt="Carregando..." width="80">
        <span>Carregando painel de governança...</span>
    </div>
    <div id="st-gov-content" class="st-gov-content" style="display:none;"></div>
</div>

<script>
window.stGovBootConfig = {
    de: <?= json_encode($dePadrao) ?>,
    ate: <?= json_encode($atePadrao) ?>,
    contrato: <?= (int) $contratoPadrao ?>,
    fila: <?= (int) $filaPadrao ?>
};
</script>
<script type="text/javascript" src="js/dash-gov.js?v=20260616h"></script>
<script>
(function () {
    function tryBoot() {
        if (window.stGovDashboard && typeof window.stGovDashboard.bootPage === 'function') {
            return window.stGovDashboard.bootPage();
        }
        return false;
    }
    if (!tryBoot()) {
        var tries = 0;
        var timer = setInterval(function () {
            if (tryBoot() || ++tries > 80) {
                clearInterval(timer);
            }
        }, 50);
    }
})();
</script>

<?php st_page_close(); ?>
