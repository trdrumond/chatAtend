<?php
include("../cnf/session.php");

$formId = (int) ($_POST['id_form'] ?? 0);
$contratoId = (int) ($_POST['id_contrato'] ?? 0);
$tableDem = 'tbl_in_dem_' . $formId . '_' . $contratoId;

if (!preg_match('/^tbl_in_dem_\d+_\d+$/', $tableDem)) {
    return;
}
if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $contratoId)) {
    return;
}

$qtd_aberto = 0;
$qtd_tratamento = 0;
$qtd_pendente = 0;
$qtd_concluido = 0;

$sql = "SELECT count(a.id_form_dem) as qtd, a.situacao_id from {$tableDem} a where date_format(a.data_hora, '%Y-%m')=date_format(curdate(), '%Y-%m') group by a.situacao_id order by qtd desc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$ddChats_2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

for ($y = 0; $y < count($ddChats_2); $y++) {
    $ls = $ddChats_2[$y];

    if ($ls['situacao_id'] == 1) {
        $qtd_aberto = $qtd_aberto + $ls['qtd'];
    }
    if ($ls['situacao_id'] == 2) {
        $qtd_tratamento = $qtd_tratamento + $ls['qtd'];
    }
    if ($ls['situacao_id'] == 3) {
        $qtd_pendente = $qtd_pendente + $ls['qtd'];
    }
    if ($ls['situacao_id'] == 4) {
        $qtd_concluido = $qtd_concluido + $ls['qtd'];
    }
}
$qtd_concluido = ($qtd_concluido == '') ? 0 : $qtd_concluido;
$qtd_aberto = ($qtd_aberto == '') ? 0 : $qtd_aberto;
$qtd_tratamento = ($qtd_tratamento == '') ? 0 : $qtd_tratamento;
$qtd_pendente = ($qtd_pendente == '') ? 0 : $qtd_pendente;
$qtd_total = $qtd_aberto + $qtd_tratamento + $qtd_pendente + $qtd_concluido;

$chartKey = $formId . '_' . $contratoId;

?>
   <script>
        am4core.ready(function() {

        am4core.useTheme(am4themes_animated);

        var chart = am4core.create("chart_2_<?= $chartKey ?>", am4charts.PieChart);
        chart.hiddenState.properties.opacity = 0;

        chart.data = [
        {
            country: "Entrada",
            value: <?php echo $qtd_total - $qtd_concluido; ?>
        }
        <?php if ($qtd_concluido > 0) { ?>
        ,{
            country: "Concluído",
            value: <?php echo $qtd_concluido; ?>
        }
        <?php } ?>
        ];
        chart.radius = am4core.percent(70);
        chart.innerRadius = am4core.percent(40);
        chart.startAngle = 180;
        chart.endAngle = 360;

        var series = chart.series.push(new am4charts.PieSeries());
        series.dataFields.value = "value";
        series.dataFields.category = "country";

        series.slices.template.cornerRadius = 10;
        series.slices.template.innerCornerRadius = 7;
        series.slices.template.draggable = true;
        series.slices.template.inert = true;
        series.alignLabels = false;

        series.hiddenState.properties.startAngle = 90;
        series.hiddenState.properties.endAngle = 90;

        });
    </script>
    <div class="chart-100">
        <div id="chart_2_<?= $chartKey ?>" class="chartdiv"></div>
    </div>

