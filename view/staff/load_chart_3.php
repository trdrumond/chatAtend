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
$qtd_total = $qtd_aberto + $qtd_tratamento + $qtd_pendente + $qtd_concluido;

?>
   <script>
        am4core.ready(function() {

        // Themes begin
        am4core.useTheme(am4themes_frozen);
        am4core.useTheme(am4themes_animated);
        // Themes end

        // create chart
        var chart = am4core.create("chart_3_<?php echo $dadosContratos[$x]['id_form']. '_'.$infoUser['contrato_id']; ?>", am4charts.GaugeChart);
        chart.innerRadius = am4core.percent(82);

        /**
         * Normal axis
         */

        var axis = chart.xAxes.push(new am4charts.ValueAxis());
        axis.min = 0;
        axis.max = 100;
        axis.strictMinMax = true;
        axis.renderer.radius = am4core.percent(90);
        axis.renderer.inside = true;
        axis.renderer.line.strokeOpacity = 1;
        axis.renderer.ticks.template.disabled = false
        axis.renderer.ticks.template.strokeOpacity = 1;
        axis.renderer.ticks.template.length = 10;
        axis.renderer.grid.template.disabled = true;
        axis.renderer.labels.template.radius = 35;
        axis.renderer.labels.template.adapter.add("text", function(text) {
        return text + "%";
        })

        /**
         * Axis for ranges
         */

        var colorSet = new am4core.ColorSet();

        var axis2 = chart.xAxes.push(new am4charts.ValueAxis());
        axis2.min = 0;
        axis2.max = 100;
        axis2.strictMinMax = true;
        axis2.renderer.labels.template.disabled = true;
        axis2.renderer.ticks.template.disabled = true;
        axis2.renderer.grid.template.disabled = true;

        var range0 = axis2.axisRanges.create();
        range0.value = 0;
        range0.endValue = 50;
        range0.axisFill.fillOpacity = 1;
        range0.axisFill.fill = colorSet.getIndex(0);

        var range1 = axis2.axisRanges.create();
        range1.value = 50;
        range1.endValue = 100;
        range1.axisFill.fillOpacity = 1;
        range1.axisFill.fill = colorSet.getIndex(2);

        /**
         * Label
         */

        var label = chart.radarContainer.createChild(am4core.Label);
        label.isMeasured = false;
        label.fontSize = 20;
        label.x = am4core.percent(10);
        label.y = am4core.percent(100);
        label.horizontalCenter = "middle";
        label.verticalCenter = "bottom";
        label.text = "5%";


        /**
         * Hand
         */

        var hand = chart.hands.push(new am4charts.ClockHand());
        hand.axis = axis2;
        hand.innerRadius = am4core.percent(20);
        hand.startWidth = 10;
        hand.pin.disabled = true;
        hand.value = 50;

        hand.events.on("propertychanged", function(ev) {
        range0.endValue = ev.target.value;
        range1.value = ev.target.value;
        label.text = axis2.positionToValue(hand.currentPosition).toFixed(1);
        axis2.invalidate();
        });

        setInterval(function() {
        var value = Math.round(Math.random() * 100);
        var animation = new am4core.Animation(hand, {
            property: "value",
            to: value
        }, 1000, am4core.ease.cubicOut).start();
        }, 2000);

        }); // end am4core.ready()
    </script>
    <div class="chart-100">
        <div id="chart_3_<?php echo $dadosContratos[$x]['id_form']. '_'.$infoUser['contrato_id']; ?>" class="chartdiv"></div>
    </div>
