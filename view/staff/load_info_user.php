<?php
    include("../cnf/session.php");
    require_once __DIR__ . '/../cnf/st_fila_status.php';
    require_once __DIR__ . '/../cnf/st_dash_acomp.php';

    $userId = (int)($_POST['user_id'] ?? 0);
    $idFila = (int)($_POST['id_fila'] ?? 0);
    $idContrato = (int)($_POST['id_contrato'] ?? 0);

    $sqlChats = 'SELECT ci.id_chat, ci.fila_chat_id, ci.status_chat, ci.rem_chat, ci.dest_chat, cf.protocolo'
        .' FROM tbl_chat_info ci'
        .' INNER JOIN tbl_chat_fila cf ON cf.id_fila_chat = ci.fila_chat_id'
        .' WHERE cf.bko_resp = ?'
        .' AND ' . stFilaSqlAtendimentoAtivo()
        .' AND ci.status_chat = 1';
    $chatParams = [$userId];
    if ($idFila > 0) {
        $sqlChats .= ' AND cf.fila_id = ?';
        $chatParams[] = $idFila;
    }
    if ($idContrato > 0) {
        $sqlChats .= ' AND cf.contrato_id = ?';
        $chatParams[] = $idContrato;
    }
    $sqlChats .= ' ORDER BY ci.data_hora DESC';

    $stmt = $PDO->prepare($sqlChats);
    $stmt->execute($chatParams);
    $infoChat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $PDO->prepare(
        'SELECT COUNT(id_fila_chat) AS qtd, SEC_TO_TIME(AVG(TIME_TO_SEC(ta))) AS tma'
        .' FROM tbl_chat_fila_secondary'
        .' WHERE hora_inicio IS NOT NULL AND DATE_FORMAT(data_hora, \'%Y-%m\') = ? AND bko_resp = ?'
    );
    $stmt->execute([date('Y-m'), $userId]);
    $ddTma = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($ddTma);
    $tma = explode(".", $ddTma['tma']);
    if(!$tma[0]){$tma[0]='--:--:--';}

    $sql="SELECT date_format(data_hora, '%Y-%m-%d') as data, count(*) as qtd from tbl_chat_fila_secondary where hora_inicio is not null and bko_resp=".$userId." and date_format(data_hora, '%Y-%m')='".date('Y-m')."' group by date_format(data_hora, '%Y-%m-%d') asc";

    //echo "<br>".$sql;
    $stmt = $PDO_LOAD->prepare($sql);
    $result = $stmt->execute();
    $ddScore = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($ddScore);

    //echo "<br>".count($infoChat);

?>
<style>
#btn_close_info {
    color: #FFF;
    background-color: #DDDDDD;
    border: none;
    height: 20px;
    line-height: 20px;
    font-size: 10px;
    padding: 0 15px;
    text-transform: uppercase;
    font-weight: bold;
    cursor: pointer;
    border-radius: 10px;
    -webkit-border-radius: 10px;
    -moz-border-radius: 10px;
    -ms-border-radius: 10px;
    -o-border-radius: 10px;
}

.info_dados {
    color: #777777;
    background-color: #EEEEEE;
    text-align: center;
    border: none;
    width: 47%;
    float: left;
    height: 40px;
    line-height: 20px;
    font-size: 12px;
    padding: 10px;
    margin: 5px;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    -ms-border-radius: 5px;
    -o-border-radius: 5px;
}

#chartdiv_<?=$userId?> {
    width: 100%;
    height: 200px;
}

h3 {
    color: #777777;
}
</style>

<div id="div_info" class="dash-fila-user-info">
    <ul class="nav nav-tabs" id="tabChat" role="tablist">
        <?php for($x=0;$x<count($infoChat);$x++){ ?>
        <?php
                if(count($infoChat)==4){
                    $infoChat[$x]['protocolo'] = "...".substr($infoChat[$x]['protocolo'], 6);
                }
            ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php if($x==0){ echo 'active'; } ?>" id="chat-tab_<?=$infoChat[$x]['id_chat'];?>"
                data-bs-toggle="tab" data-bs-target="#chat_ativo_<?=$infoChat[$x]['id_chat'];?>" type="button"
                role="tab" aria-controls="chat_ativo_<?=$infoChat[$x]['id_chat'];?>" aria-selected="false"><i
                    class="fas fa-comment-dots"></i> <?=$infoChat[$x]['protocolo']?> </button>
        </li>

        <?php } ?>


        <?php if(count($infoChat)<1){ ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php if(count($infoChat)<1){ echo 'active'; } ?>" id="indic-tab"
                data-bs-toggle="tab" data-bs-target="#indic" type="button" role="tab" aria-controls="indic"
                aria-selected="true"><i class="fas fa-list"></i> Indicadores</button>
        </li>
        <?php } ?>

    </ul>

    <div class="tab-content" id="myTabContent">
        <?php if(count($infoChat)<1){ ?>
        <div class="tab-pane fade <?php if(count($infoChat)<1){ echo ' show active'; } ?>" id="indic" role="tabpanel"
            aria-labelledby="indic-tab">
            <br>
            <center>
                <h6>Referência: <?php echo date('m/Y'); ?></h6>
            </center>
            <div class="info_dados">
                Atendimentos: <strong><?=$ddTma['qtd']?></strong>
            </div>
            <div class="info_dados">
                TMA: <strong><?=$tma[0]?></strong>
            </div>
            <?php if(count($ddScore)>0){ ?>
            <script>
            am4core.useTheme(am4themes_animated);

            var chart_1 = am4core.create("chartdiv_<?=$userId?>", am4charts.XYChart);

            chart_1.data = [
                <?php
                                    for($y=0;$y<count($ddScore);$y++){
                                        echo '{
                                                    "dia": "'.date('d/m/Y', strtotime(date($ddScore[$y]['data']))).'",
                                                    "qtd": '.$ddScore[$y]['qtd'].'
                                                },';
                                    }
                                ?>
            ];

            chart_1.padding(40, 40, 40, 40);
            chart_1.maskBullets = false; // allow bullets to go out of plot area

            var label = chart_1.plotContainer.createChild(am4core.Label);
            //label.text = "Drag column bullet to change it's value";
            label.y = 92;
            label.x = am4core.percent(100);
            label.horizontalCenter = "right";
            label.zIndex = 100;
            label.fillOpacity = 0.7;

            // category axis
            var categoryAxis = chart_1.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.dataFields.category = "dia";
            categoryAxis.renderer.minGridDistance = 60;
            categoryAxis.renderer.grid.template.disabled = true;
            categoryAxis.renderer.line.disabled = true;

            // value axis
            var valueAxis = chart_1.yAxes.push(new am4charts.ValueAxis());
            // we set fixed min/max and strictMinMax to true, as otherwise value axis will adjust min/max while dragging and it won't look smooth
            valueAxis.min = 0;
            valueAxis.max = <?=$ddTma['qtd']?>;
            valueAxis.strictMinMax = true;
            //valueAxis.renderer.line.disabled = true;
            valueAxis.renderer.minWidth = 40;

            // series
            var series = chart_1.series.push(new am4charts.ColumnSeries());
            series.dataFields.categoryX = "dia";
            series.dataFields.valueY = "qtd";
            series.tooltip.pointerOrientation = "vertical";
            series.tooltip.dy = -8;

            // label bullet
            var labelBullet = new am4charts.LabelBullet();
            series.bullets.push(labelBullet);
            labelBullet.label.text = "{valueY.value.formatNumber('#.')}";
            labelBullet.strokeOpacity = 0;
            labelBullet.stroke = am4core.color("#dadada");
            labelBullet.dy = -20;


            // as by default columns of the same series are of the same color, we add adapter which takes colors from chart_1.colors color set
            //columnTemplate.adapter.add("fill", function (fill, target) {
            //return chart_1.colors.getIndex(target.dataItem.index).saturate(0.3);
            //});
            </script>
            <?php } ?>
            <h6>Quantidade de Atendimentos</h6>
            <div id="chartdiv_<?=$userId?>"></div>
        </div>
        <?php } ?>

        <?php for($x=0;$x<count($infoChat);$x++){ ?>
        <div class="tab-pane fade <?php if($x==0){ echo ' show active'; } ?>"
            id="chat_ativo_<?=$infoChat[$x]['id_chat'];?>" role="tabpanel"
            aria-labelledby="chat-tab_<?=$infoChat[$x]['id_chat'];?>">

            <script>
            userRem = <?=$infoChat[$x]['rem_chat']?>;
            userDem_0 = 0;
            userDem = <?=$infoChat[$x]['dest_chat']?>;


            function loadFileDiv() {}

            function loadPos() {}
            </script>
            <?php

                        $sql="SELECT a.id_msg, a.data_hora, a.chat_id, a.contrato_id, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.dest_id, a.msg, b.fila_chat_id from tbl_chat_msg a, tbl_chat_info b where a.chat_id=b.id_chat and id_chat='".$infoChat[$x]['id_chat']."' ORDER BY a.id_msg ASC";
                        //echo "<br>".$sql;

                        $stmt = $PDO->prepare($sql);
                        $result = $stmt->execute();
                        $infoChatMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );

                        $stmtMotivo = $PDO->prepare('SELECT motivo FROM tbl_chat_fila WHERE id_fila_chat = ? LIMIT 1');
                        $stmtMotivo->execute([(int)$infoChat[$x]['fila_chat_id']]);
                        $motivoChat = $stmtMotivo->fetch(PDO::FETCH_ASSOC) ?: ['motivo' => ''];
                        ?>
            <div class="st-dash-acomp-chat" data-st-bko-id="<?= (int)$userId ?>" data-st-chat-id="<?= (int)$infoChat[$x]['id_chat'] ?>">
            <div class="chat-div">
                <section class="chat-content" id="chat-content_0_<?=$infoChat[$x]['id_chat'];?>" data-st-chat-id="<?= (int)$infoChat[$x]['id_chat'] ?>">
                    <?php if($motivoChat['motivo']!=''){ ?>
                    <div class="st-chat-motivo"><strong>Motivo:</strong>
                        <p><?= $motivoChat['motivo'];?></p>
                    </div>
                    <?php } ?>
                    <?php
                                        for($z=0;$z<count($infoChatMsg);$z++){
                                            $ls=$infoChatMsg[$z];

                                                $class = ($ls['rem_id']==$userId) ? 'me' : 'other';
                                                if($ls['rem_id']==0){
                                                    $h5="";
                                                    $class = 'sys';
                                                } else {
                                                    $h5 = "<h5>".ucwords(strtolower($ls['nome_rem']))."</h5>";
                                                }
                                                $horaMsg = stDashAcompFmtHora($ls['data_hora'] ?? '');
                                                echo "<div class='$class' data-msg-id='".(int)$ls['id_msg']."'>";
                                                if ($class !== 'sys') {
                                                    echo "<img src='".$ls['img']."'>";
                                                }
                                                echo "<div class='text'>
                                                        ".$h5."
                                                        <div class='paragrafo'>".$ls['msg']."</div>
                                                        <div class='dataHora'>".$horaMsg."</div>
                                                    </div>
                                                </div>";
                                        }
                                    ?>
                </section>
                <div id="dig_0_<?=$infoChat[$x]['id_chat'];?>" class="dig">
                    Ao vivo · <?=date('H:i:s')?>
                </div>
            </div>
            </div>
            <?php


                ?>
        </div>
        <?php } ?>
    </div>

    <div class="st-dash-acomp-footer">
        <button id="btn_close_info" type="button" onclick="close_info(<?= (int)$idContrato ?>, <?= (int)$idFila ?>);"><i
                class="fas fa-times"></i> Fechar</button>
    </div>

</div>
<?php if (count($infoChat) > 0) { ?>
<script>
(function () {
    var paneKey = '<?= (int)$idContrato ?>_<?= (int)$idFila ?>';
    var bkoId = <?= (int)$userId ?>;
    var $host = $('#div_info_' + paneKey);
    if (typeof stDashAcomp !== 'undefined') {
        stDashAcomp.initFromPanel($host, paneKey, bkoId);
    }
})();
</script>
<?php } ?>
