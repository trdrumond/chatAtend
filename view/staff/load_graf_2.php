<?php
include("../cnf/session.php");

if($_POST['id_fila']==''){
    $_POST['id_fila'] = 0;
}

//depurador($_POST);
if($_POST['id_fila']!=0){
    $sqlQuery = " and fila_id=".$_POST['id_fila'];
} else {
    $sqlQuery = "";
}


//$sql="SELECT count(*) as qtd, status_fila, (SELECT nome_situacao from tbl_situacao_chat where id_situacao=status_fila) as nome_status from tbl_chat_fila where date_format(data_hora, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') $sqlQuery group by status_fila";
$sql="SELECT count(*) as qtd, status_fila, (SELECT nome_situacao from tbl_situacao_chat where id_situacao=status_fila) as nome_status from tbl_chat_fila where date_format(data_hora, '%Y-%m-%d')=curdate() $sqlQuery group by status_fila";

//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$ddChats_2 = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($ddChats_2);

?>


   <script>
    //var chart = am4core.create("chart_2_<?php echo $_POST['id_fila']. '_'.$_POST['id_contrato']; ?>",


            am5.ready(function() {

            // Create root element
            // https://www.amcharts.com/docs/v5/getting-started/#Root_element
            var root = am5.Root.new("chart_2_<?php echo $_POST['id_fila']. '_'.$_POST['id_contrato']; ?>");

            // Set themes
            // https://www.amcharts.com/docs/v5/concepts/themes/
            root.setThemes([
            am5themes_Animated.new(root)
            ]);

            // Create chart
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/
            // start and end angle must be set both for chart and series
            var chart = root.container.children.push(am5percent.PieChart.new(root, {
            startAngle: 180,
            endAngle: 360,
            layout: root.verticalLayout,
            innerRadius: am5.percent(50)
            }));

            // Create series
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Series
            // start and end angle must be set both for chart and series
            var series = chart.series.push(am5percent.PieSeries.new(root, {
            startAngle: 180,
            endAngle: 360,
            valueField: "qtd",
            categoryField: "Situacao",
            alignLabels: false
            }));

            series.states.create("hidden", {
            startAngle: 180,
            endAngle: 180
            });

            series.slices.template.setAll({
            cornerRadius: 5
            });

            series.ticks.template.setAll({
            forceHidden: true
            });

            series.get("colors").set("colors", [
                am5.color("#252159"),
                am5.color("#FBA6A3"),
                am5.color("#FA5D57"),
                am5.color("#7A5150"),
                am5.color("#C74A46")
            ]);



            // Set data
            // https://www.amcharts.com/docs/v5/charts/percent-charts/pie-chart/#Setting_data
            series.data.setAll([
            <?php
                for($y=0;$y<count($ddChats_2);$y++){
                    $ls=$ddChats_2[$y];
                    echo '{ Situacao: "'.$ls['nome_status'].'", qtd: '.$ls['qtd'].' },';
                }
            ?>
            ]);



            series.appear(1000, 100);

            }); // end am5.ready()

    </script>
    <div class="chart-100">
        <style>
            #titulo {
                width: 100%;
                text-align: center;
            }
        </style>

        <div id="titulo"><h6>Quantidade por Status (<?= date('d/m/Y') ?>)</h6></div>
        <div id="chart_2_<?php echo $_POST['id_fila']. '_'.$_POST['id_contrato']; ?>" class="chartdiv"></div>
    </div>
