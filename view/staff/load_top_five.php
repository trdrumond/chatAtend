<?php
include("../cnf/session.php");

//depurador($_POST);

$sql="SELECT count(a.resp_id) as qtd, a.resp_id, b.nome, b.sobrenome, c.img from tbl_in_dem_".$_POST['id_form']."_".$_POST['id_contrato']." a, tbl_user b, tbl_user_img_perfil c where a.resp_id is not null and a.situacao_id=4 and a.resp_id=b.id_user and a.resp_id=c.user_id and date_format(a.data_hora, '%Y-%m')=date_format(curdate(), '%Y-%m') group by a.resp_id order by qtd desc limit 5";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $ddChats_1 = $stmt->fetchAll( PDO::FETCH_ASSOC );
?>
    <script>
        am4core.useTheme(am4themes_animated);

        var chart = am4core.create("chart_<?php echo $_POST['id_form']. '_'.$_POST['id_contrato']; ?>", am4charts.XYChart);


        chart.paddingBottom = 30;

        // Setting data
        chart.data = [
            <?php
                for($y=0;$y<count($ddChats_1);$y++){
                    $ls=$ddChats_1[$y];
                    $ls['nome_completo']=ucwords((strtolower($ls['nome'])).' '.(strtolower($ls['sobrenome'][0]))).".";

                    echo '{
                        "id": "'.$ls['resp_id'].'",
                        "img": "'.$ls['img'].'",
                        "Colaborador": "'.$ls['nome_completo'].'",
                        "qtd": '.$ls['qtd'].'
                    },';
                }
            ?>
        ];

        var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
        categoryAxis.dataFields.category = "Colaborador";
        categoryAxis.renderer.grid.template.strokeOpacity = 0;
        categoryAxis.renderer.minGridDistance = 10;
        categoryAxis.renderer.labels.template.dy = 35;
        categoryAxis.renderer.tooltip.dy = 35;

        var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        valueAxis.renderer.inside = true;
        valueAxis.renderer.labels.template.fillOpacity = 0.3;
        valueAxis.renderer.grid.template.strokeOpacity = 0;
        valueAxis.min = 0;
        valueAxis.cursorTooltipEnabled = false;
        valueAxis.renderer.baseGrid.strokeOpacity = 0;

        var series = chart.series.push(new am4charts.ColumnSeries);
        series.dataFields.valueY = "qtd";
        series.dataFields.categoryX = "Colaborador";
        series.dataFields.id = "id";
        series.dataFields.img = "img";
        series.tooltipText = "{valueY.value}";
        series.tooltip.pointerOrientation = "vertical";
        series.tooltip.dy = - 6;
        series.columnsContainer.zIndex = 100;

        var columnTemplate = series.columns.template;
        columnTemplate.width = am4core.percent(30);
        columnTemplate.maxWidth = 66;
        columnTemplate.column.cornerRadius(60, 60, 10, 10);
        columnTemplate.strokeOpacity = 0;

        series.heatRules.push({ target: columnTemplate, property: "fill", dataField: "valueY", min: am4core.color("#e5dc36"), max: am4core.color("#5faa46") });
        series.mainContainer.mask = undefined;

        var cursor = new am4charts.XYCursor();
        chart.cursor = cursor;
        cursor.lineX.disabled = true;
        cursor.lineY.disabled = true;
        cursor.behavior = "none";

        var bullet = columnTemplate.createChild(am4charts.CircleBullet);
        bullet.circle.radius = 20;
        bullet.valign = "bottom";
        bullet.align = "center";
        bullet.isMeasured = true;
        bullet.interactionsEnabled = false;
        bullet.verticalCenter = "bottom";

        var hoverState = bullet.states.create("hover");

        var outlineCircle = bullet.createChild(am4core.Circle);
        outlineCircle.adapter.add("radius", function (radius, target) {
            var circleBullet = target.parent;
            return circleBullet.circle.pixelRadius + 5;
        })

        var image = bullet.createChild(am4core.Image);
        image.width = 70;
        image.height = 70;
        image.horizontalCenter = "middle";
        image.verticalCenter = "middle";

        image.adapter.add("href", function (href, target) {
            var dataItem = target.dataItem;

            if (dataItem) {
                return dataItem.img;
            }
        })


        image.adapter.add("mask", function (mask, target) {
            var circleBullet = target.parent;
            return circleBullet.circle;
        })

        var previousBullet;
        chart.cursor.events.on("cursorpositionchanged", function (event) {
            var dataItem = series.tooltipDataItem;

            if (dataItem.column) {
                var bullet = dataItem.column.children.getIndex(1);

                if (previousBullet && previousBullet != bullet) {
                    previousBullet.isHover = false;
                }

                if (previousBullet != bullet) {

                    var hs = bullet.states.getKey("hover");
                    hs.properties.dy = -bullet.parent.pixelHeight + 30;
                    bullet.isHover = true;

                    previousBullet = bullet;
                }
            }
        })



    </script>
    <div class="chart-100">
        <h5>Top Five Colaboradores/Mês</h5>
        <div id="chart_<?php echo $_POST['id_form']. '_'.$_POST['id_contrato']; ?>" class="chartdiv"></div>
    </div>
