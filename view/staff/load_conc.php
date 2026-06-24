<?php
include("../cnf/session.php");

//depurador($_POST);

$sql="SELECT count(a.id_form_dem) as qtd, a.situacao_id from tbl_in_dem_".$_POST['id_form']."_".$_POST['id_contrato']." a where date_format(a.data_hora, '%Y-%m')=date_format(curdate(), '%Y-%m') group by a.situacao_id order by qtd desc";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$ddChats_2 = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($ddChats_2);
for($y=0;$y<count($ddChats_2);$y++){
    $ls=$ddChats_2[$y];

    if($ls['situacao_id']==1){
        $qtd_aberto = $qtd_aberto + $ls['qtd'];
    }
    if($ls['situacao_id']==2){
        $qtd_tratamento = $qtd_tratamento + $ls['qtd'];
    }
    if($ls['situacao_id']==3){
        $qtd_pendente = $qtd_pendente + $ls['qtd'];
    }
    if($ls['situacao_id']==4){
        $qtd_concluido = $qtd_concluido + $ls['qtd'];
    }
}
$qtd_concluido = ($qtd_concluido=='') ? 0 : $qtd_concluido;
$qtd_aberto = ($qtd_aberto=='') ? 0 : $qtd_aberto;
$qtd_tratamento = ($qtd_tratamento=='') ? 0 : $qtd_tratamento;
$qtd_pendente = ($qtd_pendente=='') ? 0 : $qtd_pendente;
$qtd_total = $qtd_aberto + $qtd_tratamento + $qtd_pendente + $qtd_concluido;

//echo "<br>Geral: ".$qtd_total;
//echo "<br>Concluído: ".$qtd_concluido;

?>
   <script>
        am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_animated);
        // Themes end

        var chart = am4core.create("chart_2_<?php echo $_POST['id_form']. '_'.$_POST['id_contrato']; ?>", am4charts.PieChart);
        chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

        chart.data = [
        {
            country: "Entrada",
            value: <?php echo $qtd_total-$qtd_concluido; ?>
        }
        <?php if($qtd_concluido>0){ ?>
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

        //chart.legend = new am4charts.Legend();

        }); // end am4core.ready()
    </script>
    <div class="chart-100">
        <div id="chart_2_<?php echo $_POST['id_form']. '_'.$_POST['id_contrato']; ?>" class="chartdiv"></div>
    </div>
