<?php
include("../cnf/session.php");
include("../cnf/replace.php");
include('../cnf/rotina_pendencia.php');
//depurador($_POST);

// Sanitiza IDs numéricos usados em tabelas/where (mantém mesmo resultado, evita casts desnecessários)
$idContrato = isset($_POST['contrato']) ? (int)$_POST['contrato'] : 0;
$idFila     = isset($_POST['fila']) ? (int)$_POST['fila'] : 0;
$idEmpresa  = isset($_POST['empresa']) ? (int)$_POST['empresa'] : 0;
$de         = preg_replace('/[^0-9\-]/', '', (string) ($_POST['de'] ?? ''));
$ate        = preg_replace('/[^0-9\-]/', '', (string) ($_POST['ate'] ?? ''));

if (!stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], $idContrato)) {
    echo '<p class="text-danger">Contrato não autorizado.</p>';
    return;
}

if($_POST['rel']=='base'){

    ?>
<style>
#div_graf_1 {
    height: 400px;
    width: 55%;
    float: left;
}

#graf_1 {
    height: 400px;
    width: 100%;
    float: left;
}

#div_graf_2 {
    height: 400px;
    width: 45%;
    float: left;
}

#graf_2 {
    height: 400px;
    width: 100%;
    float: left;
}

#div_graf_3 {
    height: 400px;
    width: 100%;
    float: left;
    margin-top: 30px;
}

#graf_3 {
    height: 400px;
    width: 100%;
    float: left;
}
</style>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados-tab-pane"
            type="button" role="tab" aria-controls="dados-tab-pane" aria-selected="true">Dados</button>
    </li>

    <!--
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#indic-tab-pane" type="button"
            role="tab" aria-controls="indic-tab-pane" aria-selected="false">Indicadores</button>
    </li>
-->

</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="dados-tab-pane" role="tabpanel" aria-labelledby="dados-tab" tabindex="0">

        <?php

                // Intervalo de datas para uso de índice (evita date_format na coluna)
                $deDt = preg_replace('/[^0-9\-]/', '', isset($_POST['de']) ? $_POST['de'] : '');
                $ateDt = preg_replace('/[^0-9\-]/', '', isset($_POST['ate']) ? $_POST['ate'] : '');
                if ($deDt === '' || $ateDt === '') { $deDt = date('Y-m-d'); $ateDt = date('Y-m-d'); }
                $deInicio = $deDt . ' 00:00:00';
                $ateFim   = $ateDt . ' 23:59:59';

                $sql="SELECT a.id_fila_chat, a.protocolo, date_format(a.data_hora, '%d/%m/%Y %H:%i:%s') as hora_registro"
                    .", (sec_to_time(time_to_sec(ta)+time_to_sec(te))) as td"
                    .", d.nome_fila, b.titulo_assunto, a.motivo_cancela, a.fila_id"
                    .", (concat(e.nome, ' ', e.sobrenome)) as nome_ate"
                    .", emp.nome_empresa as empresa_ate"
                    .", reg.nome_regional as regional_ate"
                    .", mun.nome_municipio as municipio_ate"
                    .", ag.nome_agencia as agencia_ate"
                    .", (concat(bko.nome, ' ', bko.sobrenome)) as nome_bko"
                    .", c.nome_situacao, date_format(a.hora_inicio, '%H:%i:%s') as hora_inicio, date_format(a.hora_fim, '%H:%i:%s') as hora_fim, a.ta, a.te"
                    ." FROM tbl_chat_fila_secondary a"
                    ." INNER JOIN tbl_assunto b ON a.assunto_id=b.id_assunto"
                    ." INNER JOIN tbl_situacao_chat c ON a.status_fila=c.id_situacao"
                    ." INNER JOIN tbl_config_fila d ON a.fila_id=d.id_fila"
                    ." INNER JOIN tbl_user e ON a.ate_resp=e.id_user"
                    ." LEFT JOIN tbl_empresa emp ON e.empresa_id=emp.id_empresa"
                    ." LEFT JOIN tbl_regional reg ON e.regional_id=reg.id_regional"
                    ." LEFT JOIN tbl_municipio mun ON e.municipio_id=mun.id_municipio"
                    ." LEFT JOIN tbl_agencia ag ON e.agencia_id=ag.id_agencia"
                    ." LEFT JOIN tbl_user bko ON a.bko_resp=bko.id_user"
                    ." WHERE a.contrato_id=?"
                    ." AND a.data_hora >= ? AND a.data_hora <= ?";

                    $params = [$idContrato, $deInicio, $ateFim];

                    if($idFila!=0){
                        $sql.=" AND a.fila_id=?";
                        $params[] = $idFila;
                    }

                    if($idEmpresa!=0){
                        $sql.=" AND e.empresa_id=?";
                        $params[] = $idEmpresa;
                    }

                    $sql.=" ORDER BY a.data_hora ASC";

                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute( $params );
                $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

                // TMA/TME: filtro por data sem date_format para usar índice
                $sqlTma = "SELECT sec_to_time(avg(time_to_sec(ta))) as tma, sec_to_time(avg(time_to_sec(te))) as tme FROM tbl_chat_fila"
                        ." WHERE data_hora >= ? AND data_hora <= ?";
                        $paramsTma = [$deInicio, $ateFim];
                        if($idFila!=0){
                            $sqlTma.=" AND fila_id=?";
                            $paramsTma[] = $idFila;
                        }
                $stmt = $PDO->prepare( $sqlTma );
                $result = $stmt->execute( $paramsTma );
                $dadosTma = $stmt->fetch( PDO::FETCH_ASSOC );
                $exTma = $dadosTma ? explode(".", $dadosTma['tma'] ?? '') : [''];
                $exTme = $dadosTma ? explode(".", $dadosTma['tme'] ?? '') : [''];

                $sql="SELECT id_campo, desc_campo, nome_campo from tbl_forms_pos_input_campo where fila_id=?";

                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute([$idFila]);
                $dadosTr = $stmt->fetchAll( PDO::FETCH_ASSOC );

                // Carrega em lote TP + campos pos e estrelas (evita N+1). SELECT * funciona com ou sem coluna tp.
                $posByChat = [];
                $starByChat = [];
                if (count($dados) > 0) {
                    try {
                        $idsChat = array_column($dados, 'id_fila_chat');
                        $idsPlaceholders = implode(',', array_fill(0, count($idsChat), '?'));
                        $stmtStar = $PDO->prepare("SELECT chat_fila_id, star FROM tbl_classificacao WHERE chat_fila_id IN ($idsPlaceholders)");
                        $stmtStar->execute(array_map('intval', $idsChat));
                        while ($row = $stmtStar->fetch(PDO::FETCH_ASSOC)) {
                            $starByChat[(int)$row['chat_fila_id']] = $row;
                        }
                        $idsPorFila = [];
                        foreach ($dados as $r) {
                            $fid = (int)$r['fila_id'];
                            if (!isset($idsPorFila[$fid])) $idsPorFila[$fid] = [];
                            $idsPorFila[$fid][] = (int)$r['id_fila_chat'];
                        }
                        foreach ($idsPorFila as $filaId => $chatIds) {
                            $chatIds = array_unique($chatIds);
                            $ph = implode(',', array_fill(0, count($chatIds), '?'));
                            $tablePos = 'tbl_in_pos_' . (int) $filaId . '_' . (int) $idContrato;
                            if (!preg_match('/^tbl_in_pos_\d+_\d+$/', $tablePos)) {
                                continue;
                            }
                            $sqlPos = "SELECT * FROM {$tablePos} WHERE chat_id IN ($ph)";
                            $stmtPos = $PDO->prepare($sqlPos);
                            $stmtPos->execute(array_values($chatIds));
                            while ($row = $stmtPos->fetch(PDO::FETCH_ASSOC)) {
                                $posByChat[$filaId][(int)$row['chat_id']] = $row;
                            }
                        }
                    } catch (Exception $e) {
                        $posByChat = [];
                        $starByChat = [];
                    }
                }

                ?>


        <table id="tbl_rel" style="font-size: 8px !important;">
            <thead>
                <tr>
                    <th>PROTOCOLO</th>
                    <th>
                        <center>SITUAÇÃO</center>
                    </th>
                    <th>
                        <center>MOTIVO</center>
                    </th>
                    <th>
                        <center>HORA REG.</center>
                    </th>
                    <th>
                        <center>FILA</center>
                    </th>
                    <th>
                        <center>ASSUNTO</center>
                    </th>
                    <th>
                        <center>SOLICITANTE</center>
                    </th>
                    <th>
                        <center>EMPRESA</center>
                    </th>
                    <th>
                        <center>REGIONAL</center>
                    </th>
                    <th>
                        <center>AGÊNCIA</center>
                    </th>
                    <th>
                        <center>MUNICÍPIO</center>
                    </th>
                    <th>
                        <center>BACKOFFICE</center>
                    </th>
                    <th>
                        <center>INICIO</center>
                    </th>
                    <th>
                        <center>FIM</center>
                    </th>
                    <th>
                        <center>TA</center>
                    </th>
                    <th>
                        <center>TE</center>
                    </th>
                    <th>
                        <center>TD</center>
                    </th>
                    <th>
                        <center>TP</center>
                    </th>
                    <th>
                        <center><i class="fas fa-star" style="color: #D2D200"></i></center>
                    </th>
                    <?php
                                    if(count($dadosTr)>0){
                                        for($xTr=0;$xTr<count($dadosTr);$xTr++){
                                            echo '<th><center>'.stHtml(strtoupper((string) $dadosTr[$xTr]['desc_campo'])).'</center></th>';
                                        }
                                    }
                                ?>
                </tr>
            </thead>
            <tbody>
                <?php
                                $tmTp=0;
                                $qtdTp=0;
                                $tmTd=0;
                                $qtdTd=0;
                                for($x=0;$x<count($dados);$x++){
                                    $filaId = (int)$dados[$x]['fila_id'];
                                    $idFilaChat = (int)$dados[$x]['id_fila_chat'];
                                    $dadosTd = isset($posByChat[$filaId][$idFilaChat]) ? $posByChat[$filaId][$idFilaChat] : ['tp' => ''];
                                    if (count($dadosTr) > 0) {
                                        foreach ($dadosTr as $c) {
                                            if (!isset($dadosTd[$c['nome_campo']])) $dadosTd[$c['nome_campo']] = '';
                                        }
                                    }
                                    $star = isset($starByChat[$idFilaChat]) ? $starByChat[$idFilaChat] : ['star' => ''];

                                    $ex = explode(".", $dados[$x]['td']);
                                    $dados[$x]['td'] = $ex[0];

                                    if( $dados[$x]['td']!=''){
                                        $tmTd = $tmTd + time_to_sec( $dados[$x]['td']);
                                        $qtdTd++;
                                    }
                                    if(!empty($dadosTd['tp'])){
                                        $tmTp = $tmTp + time_to_sec($dadosTd['tp']);
                                        $qtdTp++;
                                    }

                                    echo '<tr>';
                                        echo '<td>'.stHtml($dados[$x]['protocolo']).'</td>';
                                        echo '<td><center>'.stHtml($dados[$x]['nome_situacao']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['motivo_cancela']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['hora_registro']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['nome_fila']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['titulo_assunto']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['nome_ate']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['empresa_ate']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['regional_ate']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['agencia_ate']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['municipio_ate']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['nome_bko']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['hora_inicio']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['hora_fim']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['ta']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['te']).'</center></td>';
                                        echo '<td><center>'.stHtml($dados[$x]['td']).'</center></td>';
                                        echo '<td><center>'.stHtml($dadosTd['tp'] ?? '').'</center></td>';
                                        echo '<td><center>'.stHtml($star['star']).'</center></td>';
                                        if(count($dadosTr)>0){
                                            for($xTr=0;$xTr<count($dadosTr);$xTr++){
                                                echo '<td><center>'.stHtml(ucfirst((string) ($dadosTd[$dadosTr[$xTr]['nome_campo']] ?? ''))).'</center></td>';
                                            }
                                        }
                                    echo '</tr>';
                                }

                                $tMedioPos = ($qtdTp > 0) ? sec_to_time($tmTp / $qtdTp) : '00:00:00';
                                $tMedioD   = ($qtdTd > 0) ? sec_to_time($tmTd / $qtdTd) : '00:00:00';
                            ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan=13></th>
                    <th><?=$exTma[0]?></th>
                    <th><?=$exTme[0]?></th>
                    <th><?=$tMedioD?></th>
                    <th><?=$tMedioPos?></th>
                    <th></th>
                    <?php if(count($dadosTr)>0){ ?>
                    <th colspan="<?=count($dadosTr)?>"></th>
                    <?php } ?>
                </tr>
            </tfoot>

        </table>



    </div>
















    <?php

}

if($_POST['rel']=='horario'){

    $sql="SELECT a.user_id, concat(b.nome, ' ', b.sobrenome) as nome_user, a.data_log, date_format(data_log, '%d/%m/%Y') as data_rel, date_format(date_in, '%H:%i:%s') as login, date_format(date_out, '%H:%i:%s') as logout"
    ." from tbl_log_diario a, tbl_user b"
    ." where a.user_id=b.id_user"
    ." and date_format(a.data_log, '%Y-%m-%d') BETWEEN ? AND ?"
    ." and b.nivel_id=4";


    $paramsHor = [$de, $ate];
    if($idFila!=0){
        $sql.=" and b.fila_id=?";
        $paramsHor[] = $idFila;
    }

    $sql.=" order by a.data_log asc";
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute($paramsHor);
    $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($dados);

    $sql_2="SELECT count(a.user_id) as qtd from tbl_pause a, tbl_user b where a.user_id=b.id_user";
    $paramsHor2 = [];
    if($idFila!=0){
        $sql_2.=" and b.fila_id=?";
        $paramsHor2[] = $idFila;
    }
    $sql_2.=" and date_format(a.hora_in, '%Y-%m-%d') BETWEEN ? AND ? group by a.user_id, date_format(a.hora_in, '%Y-%m-%d') order by date_format(a.hora_in, '%Y-%m-%d')";
    $paramsHor2[] = $de;
    $paramsHor2[] = $ate;

    //echo "<br>".$sql_2;

    $stmt = $PDO->prepare( $sql_2 );
    $result = $stmt->execute($paramsHor2);
    $dadosQtd = $stmt->fetchAll( PDO::FETCH_ASSOC );

    $ind = array_search(max($dadosQtd),$dadosQtd);
    $qtd_horario = $dadosQtd[$ind]['qtd'];

    //echo "<br>".$qtd;

    ?>

    <table id="tbl_rel_horarios" class="table">
        <thead>
            <tr>
                <th>NOME</th>
                <th>
                    <center>DATA</center>
                </th>
                <th>
                    <center>LOGIN</center>
                </th>
                <th>
                    <center>LOGOUT</center>
                </th>
                <th>
                    <center>PRODUTIVIDADE</center>
                </th>
                <?php
                    if($qtd_horario>0){
                        for($y=1;$y<=$qtd_horario;$y++){
                            echo '<th><center>PAUSA '.$y.' IN</center></th>';
                            echo '<th><center>PAUSA '.$y.' OUT</center></th>';
                        }
                    }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
                    for($x=0;$x<count($dados);$x++){
                        $userIdLoop = (int) $dados[$x]['user_id'];
                        $dataLogLoop = preg_replace('/[^0-9\-]/', '', (string) ($dados[$x]['data_log'] ?? ''));

                        $sql="SELECT date_format(hora_in, '%H:%i:%s') as hora_in, date_format(hora_out, '%H:%i:%s') as hora_out, date_format(hora_out, '%Y-%m-%d') as data_out from tbl_pause where date_format(hora_in, '%Y-%m-%d')=? and user_id=?";

                        //echo "<br>".$sql;

                        $stmt = $PDO->prepare( $sql );
                        $result = $stmt->execute([$dataLogLoop, $userIdLoop]);
                        $dadosTd = $stmt->fetchAll( PDO::FETCH_ASSOC );

                        $sql_3="SELECT date_format(data_hora, '%H:%i:%s') as hora_disp from tbl_log_atendimento where acao='Disponivel' and user_id=? and date_format(data_hora, '%Y-%m-%d')=? order by data_hora asc limit 1";
                        $stmt = $PDO->prepare( $sql_3 );
                        $result = $stmt->execute([$userIdLoop, $dataLogLoop]);
                        $dadosDisp = $stmt->fetch( PDO::FETCH_ASSOC );

                        $sql_4="SELECT sec_to_time(sum(time_to_sec(sla))) as prod from tbl_tma_atend where resp_id=? and date_format(date_disp, '%Y-%m-%d')=? and date_format(date_in, '%Y-%m-%d')=?";
                        $stmt = $PDO->prepare( $sql_4 );
                        $result = $stmt->execute([$userIdLoop, $dataLogLoop, $dataLogLoop]);
                        $dadosProd = $stmt->fetch( PDO::FETCH_ASSOC );

                        $ex=explode('.', $dadosProd['prod']);
                        $prod=$ex[0];

                        if($dadosDisp['hora_disp']!=''){

                            if($dados[$x]['login'] != $dadosDisp['hora_disp']){
                                $dados[$x]['login'] = $dadosDisp['hora_disp'];
                            }




                            if($dados[$x]['logout']==''){
                                $dados[$x]['logout']='--:--:--';
                            }

                            echo '<tr>';
                                echo '<td>'.stHtml($dados[$x]['nome_user']).'</td>';
                                echo '<td><center>'.stHtml($dados[$x]['data_rel']).'</center></td>';
                                echo '<td><center>'.stHtml($dados[$x]['login']).'</center></td>';
                                echo '<td><center>'.stHtml($dados[$x]['logout']).'</center></td>';
                                echo '<td><center>'.stHtml($prod).'</center></td>';

                                if($qtd_horario>0){
                                    for($y=0;$y<$qtd_horario;$y++){
                                        //depurador($dadosTd[$y]);
                                        if($dadosTd[$y]['hora_in']==''){
                                            $dadosTd[$y]['hora_in']='';
                                        }
                                        if($dadosTd[$y]['hora_out']==''){
                                            $dadosTd[$y]['hora_out']='';
                                        }
                                        if($dadosTd[$y]['data_out']!=''){
                                            if($dadosTd[$y]['data_out']!=$dados[$x]['data_log']){
                                                if($dados[$x]['logout']!='--:--:--'){
                                                $dadosTd[$y]['hora_out']=$dados[$x]['logout'];
                                                } else {
                                                    $dadosTd[$y]['hora_out']='--:--:--';

                                                }
                                            }
                                        }



                                        echo '<td><center>'.stHtml($dadosTd[$y]['hora_in']).'</center></td>';
                                        echo '<td><center>'.stHtml($dadosTd[$y]['hora_out']).'</center></td>';
                                    }
                                }

                            echo '</tr>';
                        }
                    }
                ?>
        </tbody>
    </table>
    <?php

}

if($_POST['rel']=='indicadores'){
    $sqlTma = "SELECT bko_resp, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as nome_bko"
            .", count(protocolo) as qtd_atend"
            .", sec_to_time(avg(time_to_sec(ta))) as tma"
            .", sec_to_time(avg(time_to_sec(te))) as tme"
            .", sec_to_time(sum(time_to_sec(ta))) as prod"
            .", sec_to_time(avg(time_to_sec(ta) + time_to_sec(te))) as tmd"
            ." from tbl_chat_fila"
            ." WHERE (bko_resp<>0 and bko_resp is not null)"
            ." and date_format(data_hora, '%Y-%m-%d') BETWEEN ? AND ?";
            $paramsTma = [$de, $ate];
            if($idFila!=0){
                $sqlTma.=" and fila_id=?";
                $paramsTma[] = $idFila;
            }
            $sqlTma.=" group by bko_resp";

    $stmt = $PDO->prepare( $sqlTma );
    $result = $stmt->execute($paramsTma);
    $dadosTma = $stmt->fetchAll( PDO::FETCH_ASSOC );




   ?>
    <table id="tbl_rel_ind" class="table table-hover">
        <thead>
            <tr>
                <th>BACKOFFICE</th>
                <th title="Quantidade de Atendimentos Realizados">
                    <center>QTD</center>
                </th>
                <th title="Tempo Médio de Espera">
                    <center>TME</center>
                </th>
                <th title="Tempo Médio de Atendimento">
                    <center>TMA</center>
                </th>
                <th title="Tempo Médio de Duração (TE + TA)">
                    <center>TMD</center>
                </th>
                <th title="Produtividade">
                    <center>PROD</center>
                </th>
                <?php if($_POST['fila']!=''){ ?>
                <th title="Tempo Médio de Pós Atendimento">
                    <center>TMP</center>
                </th>
                <?php } ?>
                <th title="Classificação de Atendimento">
                    <center><i class="fas fa-star" style="color: #D2D200"></i></center>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                    for($x=0;$x<count($dadosTma);$x++){
                        $ls=$dadosTma[$x];
                        //depurador($dadosTma);
                        $exTma = explode(".", $ls['tma']);
                        $exTme = explode(".", $ls['tme']);
                        $exTmd = explode(".", $ls['tmd']);
                        $exProd = explode(".", $ls['prod']);

                        if($idFila!=0){
                            $tablePos = 'tbl_in_pos_' . $idFila . '_' . $idContrato;
                            if (preg_match('/^tbl_in_pos_\d+_\d+$/', $tablePos)) {
                                $sql_tp="SELECT sec_to_time(avg(time_to_sec(a.tp))) as tmp"
                                    ." from {$tablePos} a, tbl_chat_info_secondary b, tbl_chat_fila_secondary c"
                                    ." where date_format(a.data_hora, '%Y-%m-%d') BETWEEN ? AND ?"
                                    ." and a.chat_id=c.id_fila_chat"
                                    ." and b.fila_chat_id=c.id_fila_chat"
                                    ." and c.bko_resp=?";
                                $stmt = $PDO->prepare( $sql_tp );
                                $stmt->execute([$de, $ate, (int) $ls['bko_resp']]);
                                $dadosTp = $stmt->fetch( PDO::FETCH_ASSOC );
                                $exTmp = explode(".", (string) ($dadosTp['tmp'] ?? ''));
                            } else {
                                $exTmp = [''];
                            }
                        }

                        $sqlStar="SELECT format(avg(star), 1) as star from tbl_classificacao where ate=? and star is not null and star<>'' and date_format(data_hora, '%Y-%m-%d') BETWEEN ? AND ?";
                        $stmt = $PDO->prepare( $sqlStar );
                        $stmt->execute([(int) $ls['bko_resp'], $de, $ate]);
                        $dadosTs = $stmt->fetch( PDO::FETCH_ASSOC );

                        echo "<tr>";
                            echo "<td>".stHtml($ls['nome_bko'])."</td>";
                            echo "<td align='center'>".stHtml($ls['qtd_atend'])."</td>";
                            echo "<td align='center'>".stHtml($exTme[0])."</td>";
                            echo "<td align='center'>".stHtml($exTma[0])."</td>";
                            echo "<td align='center'>".stHtml($exTmd[0])."</td>";
                            echo "<td align='center'>".stHtml($exProd[0])."</td>";
                            if($_POST['fila']!=''){
                                echo "<td align='center'>".stHtml($exTmp[0] ?? '')."</td>";
                            }
                            echo "<td align='center'>".stHtml($dadosTs['star'] ?? '')."</td>";
                        echo "</tr>";
                    }
                ?>
        </tbody>
    </table>
    <?php
} ?>


    <?php
if($_POST['rel']=='monitoria'){
        if($idFila!=0){

            $sql_campos="SELECT a.desc_campo, a.nome_campo from tbl_forms_mon_input_campo a, tbl_forms_mon_input_campo_cnf b where a.id_campo=b.campo_id and b.ativo=1 and a.fila_id=? order by b.ordem";
            $stmt = $PDO->prepare( $sql_campos );
            $stmt->execute([$idFila]);
            $dadosCampos = $stmt->fetchAll( PDO::FETCH_ASSOC );

            $sql="SELECT a.id_mon, date_format(a.data_hora, '%d/%m/%Y') as data_mon, a.avaliacao";
                for($y=0;$y<count($dadosCampos);$y++){
                    $nomeCampo = (string) ($dadosCampos[$y]['nome_campo'] ?? '');
                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $nomeCampo)) {
                        continue;
                    }
                    $sql.=", ".$nomeCampo;
                }
            $tableMon = 'tbl_in_mon_' . $idFila . '_' . $idContrato;
            $dados = [];
            if (!preg_match('/^tbl_in_mon_\d+_\d+$/', $tableMon)) {
                $dados = [];
            } else {
            $sql.=", concat(b.nome, ' ', b.sobrenome) as resp_monitoria"
            .", d.protocolo, d.ate_resp as ate_resp, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=ate_resp) as solicitante, d.bko_resp as bko_resp, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as backoffice"
            .", e.nome_fila"
            ." from {$tableMon} a, tbl_user b, tbl_chat_info_secondary c, tbl_chat_fila_secondary d, tbl_config_fila e"
            ." where a.fila_id=?"
            ." and a.resp_mon=b.id_user"
            ." and a.chat_id=c.id_chat"
            ." and a.fila_id=e.id_fila"
            ." and c.fila_chat_id=d.id_fila_chat"
            ." and a.data_hora BETWEEN ? AND ?";

            $stmt = $PDO->prepare( $sql );
            $stmt->execute([$idFila, $de, $ate]);
            $dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
            }
            if(count($dados)>0){
            ?>
    <table id="tbl_rel_mon" class="table table-hover">
        <thead>
            <tr>
                <th>Protocolo</th>
                <th>
                    <center>Data</center>
                </th>
                <th>
                    <center>Resp. Monitoria</center>
                </th>
                <th>
                    <center>Pontuação</center>
                </th>
                <th>
                    <center>Solicitante</center>
                </th>
                <th>
                    <center>BackOffice</center>
                </th>
                <th>
                    <center>Fila</center>
                </th>
                <?php
                                    for($y=0;$y<count($dadosCampos);$y++){
                                        echo "<th><center>".stHtml($dadosCampos[$y]['desc_campo'])."</center></th>";
                                    }
                                ?>
            </tr>
        </thead>
        <tbody>
            <?php
                                for($x=0;$x<count($dados);$x++){
                                    $ls=$dados[$x];
                                    echo '<tr>
                                            <td>'.stHtml($ls['protocolo']).'</td>
                                            <td align="center">'.stHtml($ls['data_mon']).'</td>
                                            <td align="center">'.stHtml($ls['resp_monitoria']).'</td>
                                            <td align="center">'.stHtml($ls['avaliacao']).'</td>
                                            <td align="center">'.stHtml($ls['solicitante']).'</td>
                                            <td align="center">'.stHtml($ls['backoffice']).'</td>
                                            <td align="center">'.stHtml($ls['nome_fila']).'</td>';
                                            for($y=0;$y<count($dadosCampos);$y++){
                                                $resp = str_replace("_"," ",(string) ($ls[$dadosCampos[$y]['nome_campo']] ?? ''));
                                                echo '<td align="center">'.stHtml(ucfirst(strtolower($resp))).'</td>';
                                            }
                                    echo '</tr>';
                                }
                            ?>
        </tbody>
    </table>
    <?php
            } else {
                echo "<br><br><br><center><h4>SEM DADOS PARA A PESQUISA REALIZADA</h4></center>";
            }
        } else {
            echo "<br><br><br><center><h4>NECESSÁRIO SELECIONAR UMA FILA</h4></center>";
        }

}

?>
    <script>
    $(document).ready(function() {
        $('#tbl_rel').DataTable({
            "order": [
                [3, "asc"]
            ],
            dom: 'Bfrtip',
            "scrollY": "300px",
            "scrollX": true,
            "paging": false,
            "ordering": false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
            },
            buttons: {
                buttons: ['copy', 'excel',
                    /*
                    {
                        extend: 'pdfHtml5',
                        filename: 'Solvetask',
                        download: 'open',
                        text: 'PDF',
                        orientation: 'landscape',
                        pageSize: 'TABLOID'

                    }
                    */
                ]

            },

        });


        var extraOptionsHorarios = {};
        <?php if($qtd_horario>14){ ?>
        extraOptionsHorarios.scrollX = true;
        <?php } ?>

        $('#tbl_rel_horarios').DataTable(Object.assign({
            "order": [
                [2, "asc"]
            ],
            dom: 'Bfrtip',
            "scrollY": "300px",
            //"scrollCollapse": true,
            "paging": false,
            "ordering": false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
            },
            buttons: {
                buttons: ['copy', 'excel',
                    /*
                    {
                        extend: 'pdfHtml5',
                        filename: 'Solvetask',
                        download: 'open',
                        text: 'PDF',
                        orientation: 'landscape',
                        pageSize: 'TABLOID'

                    }
                    */
                ]

            },
        }, extraOptionsHorarios));

        $('#tbl_rel_mon').DataTable({
            "order": [
                [1, "asc"]
            ],
            dom: 'Bfrtip',
            "scrollY": "300px",
            "scrollX": true,
            //"scrollCollapse": true,
            "paging": false,
            "ordering": false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
            },
            buttons: {
                buttons: ['copy', 'excel',
                    /*
                    {
                        extend: 'pdfHtml5',
                        filename: 'Solvetask',
                        download: 'open',
                        text: 'PDF',
                        orientation: 'landscape',
                        pageSize: 'TABLOID'

                    }
                    */
                ]

            },
        });

        $('#tbl_rel_ind').DataTable({
            "order": [
                [0, "asc"]
            ],
            dom: 'Bfrtip',
            "paging": false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json'
            },
            buttons: {
                buttons: ['copy', 'excel',
                    /*
                    {
                        extend: 'pdfHtml5',
                        filename: 'Solvetask',
                        download: 'open',
                        text: 'PDF',
                        pageSize: 'A4'

                    }
                    */
                ]

            },
        });
    });
    </script>
    <?php //include("../cnf/replace_msg.php");?>