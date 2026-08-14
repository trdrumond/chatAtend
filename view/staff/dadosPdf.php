<?php include("../cnf/session.php"); ?>
<?php
//var_dump($infoUser);


$patch_local = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'];
$patch = 'https://solvetask.logos-ma.com.br';

?>
<link href="<?=$patch?>/css/bootstrap-5.0.2/css/bootstrap.min.css?<?= time() ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <script src="<?=$patch_local?>/js/fontawesome/js/all.js?<?= time() ?>" crossorigin="anonymous"></script>
    <script type="text/javascript" src="<?=$patch?>/js/jquery.js?<?= time() ?>"></script>
    <script type="text/javascript" src="<?=$patch?>/js/jquery.titlealert.js?<?= time() ?>"></script>
    <script type="text/javascript" src="<?=$patch?>/js/jquery.form.js?<?= time() ?>"></script>
    <script src="../js/action.js?<?= time() ?>"></script>
    <script src="../js/minus.js?<?= time() ?>"></script>

    <script src="<?=$patch?>/css/bootstrap-5.0.2/js/popper.min.js?<?= time() ?>"></script>
    <script src="<?=$patch?>/css/bootstrap-5.0.2/js/bootstrap.min.js?<?= time() ?>"></script>


    <link href="../css/style.css?<?= time() ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../css/style-materialize.css?<?= time() ?>">

    <script src="<?=$patch?>/js/charts/core.js"></script>
    <script src="<?=$patch?>/js/charts/charts.js"></script>
    <script src="<?=$patch?>/js/charts/frozen.js"></script>
    <script src="<?=$patch?>/js/charts/animated.js"></script>

    <script src="<?=$patch?>/js/charts_5/index.js"></script>
    <script src="<?=$patch?>/js/charts_5/xy.js"></script>
    <script src="<?=$patch?>/js/charts_5/percent.js"></script>
    <script src="<?=$patch?>/js/charts_5/themes/Animated.js"></script>
    <script src="<?=$patch?>/js/charts_5/themes/Dark.js"></script>





<?php
$dadosPdf = '<style>
    * {
        font-family: Arial, Helvetica, sans-serif;
    }
    #bloco_1, #bloco_2, #bloco_3 {
        padding: 5px;
        width: 100%;
        float: left;
    }
    #bloco_1{
        height: 350px;
    }

    #bloco_2{
        height: 600px;
    }
    #bloco_dados_1, #bloco_graf_1, #bloco_graf_2, #table_2 {
        width: 49%;
        float: left;
    }
    #bloco_graf_3 {
        width: 99%;
        float: left;
    }
    #table_3 {
        width: 99%;
        float: left;
    }
    .dados_50 {
        width: 49%;
        float: left;
        font-size: 12px;
    }
    .dados_30 {
        width: 32.5%;
        float: left;
        font-size: 12px;
    }
    .dados {
        font-family: Arial, Helvetica, sans-serif;
        width: 99%;
        padding: 1px;
        border: 1px solid #5C5C5C;
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        border-radius: 5px;
    }




    #grafico_1 {
        width: 100%;
        max-height: 250px;
        height: 250px;
    }

    #grafico_2 {
        width: 100%;
        max-height: 400px;
        height: 400px;
    }
    #grafico_3 {
        width: 100%;
        max-height: 450px;
        height: 450px;
    }

    #table_head {
        width: 100%;
        height: 150px;
        border-radius: 5px;
    }
    #table_head_div {
        border: 1px solid #5C5C5C;
        width: 100%;
        height: 150px;
        border-radius: 5px;
    }

    .bloco_branco {
        background-color: #FFFFFF;
        width: 100%;
        height: 3000px;
        position: absolute;
    }

    @media print {
        * {
            background: transparent;
            color: #000;
            text-shadow: none;
            filter: none;
            -ms-filter: none;
        }
        @page{
            margin: 5px;
        }

        .bloco_branco {
            display: none;
        }


    }
</style>';



$dadosPdf .='
<div id="bloco_branco" class="bloco_branco"></div>
<div id="table_head_div">
    <table id="table_head">
        <tr>
            <td width="20%"><img src="../img/chat.png" width="150"></td>
            <td align="center"><h1>SOLVETASK</h1></td>
            <td width="20%" align="center" style="font-size: 10px;">
                Emitido em:
                '.date('d/m/Y H:i:s').'<br>
                por : '.$infoUser['nome_completo'].'

            </td>
        </tr>
    </table>
</div>
';
    //depurador($_GET);
    $diaRaw = preg_replace('/[^0-9\-]/', '', (string) ($_GET['dia'] ?? ''));
    if ($diaRaw === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $diaRaw)) {
        $diaRaw = date('Y-m-d');
    }
    $date = date_create($diaRaw);
    if ($date === false) {
        $date = date_create(date('Y-m-d'));
        $diaRaw = date('Y-m-d');
    }

    $dia = date_format($date, "d/m/Y");

    $sql="SELECT count(*) as qtd_entradas, date_format(data_hora, '%Y-%m-%d') AS dia,
        (SELECT COUNT(*) from tbl_chat_fila_secondary where date_format(data_hora, '%Y-%m-%d')=dia and (status_fila=2 or status_fila=3 or status_fila=4 or status_fila=6 or status_fila=7 or status_fila=10)) as qtd_atendido,
        (SELECT sec_to_time(avg(time_to_sec(ta))) from tbl_chat_fila_secondary where date_format(data_hora, '%Y-%m-%d')=dia and (status_fila=2 or status_fila=3 or status_fila=4 or status_fila=6 or status_fila=7 or status_fila=10)) as tma_atend,
        (SELECT sec_to_time(avg(time_to_sec(te))) from tbl_chat_fila_secondary where date_format(data_hora, '%Y-%m-%d')=dia and (status_fila=2 or status_fila=3 or status_fila=4 or status_fila=6 or status_fila=7 or status_fila=10)) as tme_atend,
        (SELECT COUNT(*) from tbl_chat_fila_secondary where date_format(data_hora, '%Y-%m-%d')=dia and (status_fila=5 or status_fila=8 or status_fila=9)) as qtd_abandono,
        (SELECT sec_to_time(avg(time_to_sec(te))) from tbl_chat_fila_secondary where date_format(data_hora, '%Y-%m-%d')=dia and (status_fila=5 or status_fila=8 or status_fila=9)) as tme_aband
        from tbl_chat_fila_secondary where date_format(data_hora, '%Y-%m-%d')=?";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$diaRaw]);
    $infoInd = $stmt->fetch(PDO::FETCH_ASSOC);

    $porc_atendido = number_format((($infoInd['qtd_atendido']*100)/$infoInd['qtd_entradas']), 2);
    $porc_abandono = number_format((($infoInd['qtd_abandono']*100)/$infoInd['qtd_entradas']), 2);

    $exTmaAtend = explode('.', $infoInd['tma_atend']);
    $infoInd['tma_atend'] = $exTmaAtend[0];

    $exTmeAtend = explode('.', $infoInd['tme_atend']);
    $infoInd['tme_atend'] = $exTmeAtend[0];

    $exTmeAband = explode('.', $infoInd['tme_aband']);
    $infoInd['tme_aband'] = $exTmeAband[0];


    $sql="SELECT distinct assunto_id, (SELECT titulo_assunto from tbl_assunto where id_assunto=assunto_id) as servico,
        count(*) as qtd_atendimento, sec_to_time(sum(time_to_sec(ta))) as ta_total,
        sec_to_time(avg(time_to_sec(ta))) as ta_medio
        from tbl_chat_fila_secondary
        where (bko_resp is not null and bko_resp<>0) and date_format(data_hora, '%Y-%m-%d')=?
         group by assunto_id order by qtd_atendimento desc limit 10";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$diaRaw]);
    $infoSvc = $stmt->fetchAll(PDO::FETCH_ASSOC);



    $sql="SELECT distinct bko_resp, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as backoffice,
        fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=fila_id) as fila,
        count(*) as qtd_atendimento, sec_to_time(sum(time_to_sec(ta))) as ta_total,
        sec_to_time(avg(time_to_sec(ta))) as ta_medio
        from tbl_chat_fila_secondary
        where (bko_resp is not null and bko_resp<>0) and date_format(data_hora, '%Y-%m-%d')=?
         group by bko_resp order by qtd_atendimento asc";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$diaRaw]);
    $infoBack = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dadosPdf.='

';

$dadosPdf .='
<div id="bloco_1">
    <center><h4>RESUMO CONSOLIDADO DO DIA</h4></center>
    <div id="bloco_dados_1">
        <div class="dados_50">
            DATA<br>
            <div class="dados">'.$dia.'</div>
        </div>
        <div class="dados_50">
            ENTRADAS<br>
            <div class="dados">'.$infoInd['qtd_entradas'].'</div>
        </div>
        <div class="dados_50">
            ATENDIMENTOS<br>
            <div class="dados">'.$infoInd['qtd_atendido'].'</div>
        </div>
        <div class="dados_50">
            % ATENDIMENTOS<br>
            <div class="dados">'.$porc_atendido.'</div>
        </div>
        <div class="dados_50">
            TMA<br>
            <div class="dados">'.$infoInd['tma_atend'].'</div>
        </div>
        <div class="dados_50">
            TME<br>
            <div class="dados">'.$infoInd['tme_atend'].'</div>
        </div>
        <div class="dados_30">
            ABANDONOS<br>
            <div class="dados">'.$infoInd['qtd_abandono'].'</div>
        </div>
        <div class="dados_30">
            % ABANDONOS<br>
            <div class="dados">'.$porc_abandono.'</div>
        </div>
        <div class="dados_30">
            TME ABANDONOS<br>
            <div class="dados">'.$infoInd['tme_aband'].'</div>
        </div>

    </div>
    <div id="bloco_graf_1">
        <script>
                am4core.useTheme(am4themes_animated);

                var chart = am4core.create("grafico_1", am4charts.XYChart);

                chart.data = [{
                    "category": "ATENDIDO",
                    "visits": '.$infoInd['qtd_atendido'].'
                }, {
                    "category": "% ATENDIDO",
                    "visits": '.$porc_atendido.'
                }, {
                    "category": "ABANDONO",
                    "visits": '.$infoInd['qtd_abandono'].'
                }, {
                    "category": "% ABANDONO",
                    "visits": '.$porc_abandono.'
                }];

                chart.padding(40, 40, 40, 40);

                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.dataFields.category = "category";
                categoryAxis.renderer.minGridDistance = 60;

                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

                var series = chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.categoryX = "category";
                series.dataFields.valueY = "visits";
                series.tooltipText = "{valueY.value}"
                series.columns.template.strokeOpacity = 0;

                var labelBullet = new am4charts.LabelBullet();
                series.bullets.push(labelBullet);
                labelBullet.label.text = "{valueY.value.formatNumber(\'#.\')}";
                labelBullet.strokeOpacity = 0;
                labelBullet.stroke = am4core.color("#dadada");
                labelBullet.dy = - 20;



                chart.cursor = new am4charts.XYCursor();
                chart.colors.list = [
                    am4core.color("#4C79A4"),
                    am4core.color("#F78F39"),
                    am4core.color("#4C79A4"),
                    am4core.color("#F78F39")
                ];

                series.columns.template.adapter.add("fill", function (fill, target) {
                    return chart.colors.getIndex(target.dataItem.index);
                });

        </script>
        <div id="grafico_1"></div>
    </div>
</div>';

$dadosPdf .='
<div id="bloco_2">
    <center><h4>RANKING DE SERVIÇOS (10+)</h4></center>
    <div id="bloco_graf_2">
        <script>
                am4core.useTheme(am4themes_animated);

                var chart_2 = am4core.create("grafico_2", am4charts.PieChart3D);


                chart_2.data = [';

                        for($x=0;$x<count($infoSvc);$x++){
                            $infoSvc[$x]['servico'] = ucwords(strtolower($infoSvc[$x]['servico']));
                            $dadosPdf .='{
                                    "country": "'.$infoSvc[$x]['servico'].'",
                                    "litres": '.$infoSvc[$x]['qtd_atendimento'].'
                                },';
                        }
                $dadosPdf .='
                ];

                chart_2.innerRadius = am4core.percent(40);
                chart_2.depth = 90;

                var series = chart_2.series.push(new am4charts.PieSeries3D());
                series.dataFields.value = "litres";
                series.dataFields.depthValue = "litres";
                series.dataFields.category = "country";
        </script>
        <div id="grafico_2"></div>
    </div>

    <div id="table_2">
        <table id="table_svc" class="table">
            <thead>
                <tr>
                    <th>SERVIÇO</th>
                    <th><center>QUANTIDADE</center></th>
                    <th><center>TMA</center></th>
                </tr>
            </thead>
            <tbody>';



                    for($x=0;$x<count($infoSvc);$x++){
                        $infoSvc[$x]['servico'] = ucwords(strtolower($infoSvc[$x]['servico']));
                        $exTAMedl = explode('.', $infoSvc[$x]['ta_medio']);
                        $infoSvc[$x]['ta_medio'] = $exTAMedl[0];

                        $dadosPdf .='<tr>
                                <td>'.$infoSvc[$x]['servico'].'</td>
                                <td align="center">'.$infoSvc[$x]['qtd_atendimento'].'</td>
                                <td align="center">'.$infoSvc[$x]['ta_medio'].'</td>
                              </tr>';
                    }

                $dadosPdf .='
            </tbody>
        </table>
    </div>
</div>';



$dadosPdf .='
<div id="bloco_3">
    <center><h4>RANKING BACKOFFICE</h4></center>

    <div id="bloco_graf_3">
        <script>
             am5.ready(function() {

                    var root = am5.Root.new("grafico_3");


                    root.setThemes([
                    am5themes_Animated.new(root)
                    ]);


                    var chart = root.container.children.push(am5xy.XYChart.new(root, {
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    pinchZoomX:true
                    }));

                    var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
                    cursor.lineY.set("visible", false);


                    var xRenderer = am5xy.AxisRendererX.new(root, { minGridDistance: 30 });
                    xRenderer.labels.template.setAll({
                    rotation: -90,
                    centerY: am5.p50,
                    centerX: am5.p100,
                    paddingRight: 15
                    });

                    var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                    maxDeviation: 0.3,
                    categoryField: "assunto",
                    renderer: xRenderer,
                    tooltip: am5.Tooltip.new(root, {})
                    }));

                    var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                    maxDeviation: 0.3,
                    renderer: am5xy.AxisRendererY.new(root, {})
                    }));


                    var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                    name: "Series 1",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "qtd",
                    sequencedInterpolation: true,
                    categoryXField: "assunto",
                    tooltip: am5.Tooltip.new(root, {
                        labelText:"{valueY}"
                    })
                    }));

                    series.columns.template.setAll({ cornerRadiusTL: 5, cornerRadiusTR: 5 });
                    series.columns.template.adapters.add("fill", function(fill, target) {
                    return chart.get("colors").getIndex(series.columns.indexOf(target));
                    });

                    series.columns.template.adapters.add("stroke", function(stroke, target) {
                    return chart.get("colors").getIndex(series.columns.indexOf(target));
                    });



                    series.bullets.push(function () {
                    return am5.Bullet.new(root, {
                        locationY: 1,
                        sprite: am5.Label.new(root, {
                        text: "{valueYWorking.formatNumber(\'#.\')}",
                        fill: root.interfaceColors.get("alternativeText"),
                        centerY: 0,
                        centerX: am5.p50,
                        populateText: true
                        })
                    });
                    });

                    // Set data
                    var data = [';
                        for($x=0;$x<count($infoBack);$x++){
                            $infoBack[$x]['backoffice'] = ucwords(strtolower($infoBack[$x]['backoffice']));
                            $dadosPdf .= '{
                                    "assunto": "'.$infoBack[$x]['backoffice'].'",
                                    "qtd": '.$infoBack[$x]['qtd_atendimento'].'
                                }, ';
                        }
                    $dadosPdf .='];

                    xAxis.data.setAll(data);
                    series.data.setAll(data);


                    series.appear(1000);
                    chart.appear(1000, 100);

                    });

        </script>
        <div id="grafico_3"></div>
    </div>

    <div id="table_3">
        <table id="table_bko" class="table">
            <thead>
                <tr>
                    <th>BACKOFFICE</th>
                    <th><center>FILA</center></th>
                    <th><center>QUANTIDADE</center></th>
                    <th><center>SOMA TOTAL TA</center></th>
                    <th><center>TMA</center></th>
                </tr>
            </thead>
            <tbody>';

                    for($x=0;$x<count($infoBack);$x++){
                        $infoBack[$x]['backoffice'] = ucwords(strtolower($infoBack[$x]['backoffice']));
                        $infoBack[$x]['fila'] = ucwords(strtolower($infoBack[$x]['fila']));
                        $exTATotl = explode('.', $infoBack[$x]['ta_total']);
                        $infoBack[$x]['ta_total'] = $exTATotl[0];
                        $exTAMedl = explode('.', $infoBack[$x]['ta_medio']);
                        $infoBack[$x]['ta_medio'] = $exTAMedl[0];

                        $dadosPdf .='<tr>
                                <td>'.$infoBack[$x]['backoffice'].'</td>
                                <td align="center">'.$infoBack[$x]['fila'].'</td>
                                <td align="center">'.$infoBack[$x]['qtd_atendimento'].'</td>
                                <td align="center">'.$infoBack[$x]['ta_total'].'</td>
                                <td align="center">'.$infoBack[$x]['ta_medio'].'</td>
                              </tr>';
                    }

                $dadosPdf .='
            </tbody>
        </table>
    </div>


</div>';
?>
