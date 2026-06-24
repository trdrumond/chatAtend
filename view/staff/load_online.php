<?php
include("../cnf/session.php");


//depurador($_POST);

// Sanitiza IDs para evitar casts implícitos desnecessários e ajudar o otimizador
$idFila     = isset($_POST['id_fila']) ? (int)$_POST['id_fila'] : 0;
$idContrato = isset($_POST['id_contrato']) ? (int)$_POST['id_contrato'] : 0;

if($idFila!=0){
    $sql_fila = "and fila_id=".$idFila." and contrato_id=".$idContrato;
} else {
    $sql_fila = '';
}

// Usa filtros por faixa de data para permitir uso de índice em data_hora
$sql = "SELECT id_user,
               nome,
               sobrenome,
               (SELECT img
                  FROM tbl_user_img_perfil
                 WHERE user_id = id_user) AS img,
               (SELECT IF(acao<>'Logout', data_hora, '') AS data_hora
                  FROM tbl_log_atendimento
                 WHERE user_id = id_user
                   AND data_hora >= CURDATE()
                   AND data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                 ORDER BY data_hora DESC
                 LIMIT 1) AS date_disp,
               (SELECT IF(acao<>'Logout', acao, '') AS acao
                  FROM tbl_log_atendimento
                 WHERE user_id = id_user
                   AND data_hora >= CURDATE()
                   AND data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                 ORDER BY data_hora DESC
                 LIMIT 1) AS acao
          FROM tbl_user
         WHERE ativo = 1
           AND nivel_id = 4
           $sql_fila
      ORDER BY nome ASC";
//echo "<br><br>".$sql."<br>";
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($dds);

// Se não houver usuários, nada a fazer
if(count($dds) > 0){
    // Monta lista de IDs para buscas em lote
    $userIds = array();
    for($i=0;$i<count($dds);$i++){
        $userIds[] = (int)$dds[$i]['id_user'];
    }
    $userIds = array_unique($userIds);
    $idsIn   = implode(',', $userIds);

    // 1) Log diário (online/offline)
    $onlineMap = array();
    $sqlOnlineLote = "SELECT user_id, date_out, date_up
                        FROM tbl_log_diario
                       WHERE data_log = CURDATE()
                         AND user_id IN (".$idsIn.")";
    $stmt = $PDO_LOAD->prepare($sqlOnlineLote);
    $stmt->execute();
    $rowsOnline = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rowsOnline as $row){
        // se houver mais de um registro por usuário, considera o último lido
        $onlineMap[$row['user_id']] = $row;
    }

    // 2) Quantidade de atendimentos ativos por backoffice
    $qtdAtendMap = array();
    $sqlAtendimentoLote ="SELECT bko_resp, COUNT(id_fila_chat) AS qtd
                            FROM tbl_chat_fila
                           WHERE ".stFilaSqlAtendimentoAtivo()."
                             AND bko_resp IN (".$idsIn.")
                        GROUP BY bko_resp";
    $stmt = $PDO_LOAD->prepare($sqlAtendimentoLote);
    $stmt->execute();
    $rowsQtd = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rowsQtd as $row){
        $qtdAtendMap[$row['bko_resp']] = $row['qtd'];
    }

    // 3) Classificação média (estrela) por atendente
    $starMap = array();
    $day = (date('Y-m-d')<'2021-12-06') ? 1 : 5;
    $sqlStarLote = "SELECT ate, FORMAT(AVG(star), 1) AS star "
                 . "FROM tbl_classificacao "
                 . "WHERE star IS NOT NULL AND star <> '' "
                 . "AND data_hora >= '0001-01-01' "
                 . "AND data_hora < DATE_SUB(CURDATE(), INTERVAL $day DAY) "
                 . "AND ate IN (".$idsIn.") "
                 . "GROUP BY ate";
    $stmt = $PDO->prepare($sqlStarLote);
    $stmt->execute();
    $rowsStar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rowsStar as $row){
        $starMap[$row['ate']] = $row['star'];
    }

    // 4) Último log de atendimento por usuário (tbl_log_atendimento) em lote
    $logAtendMap = array();
    $sqlLogLote = "SELECT t.user_id, t.acao, t.data_hora
                     FROM tbl_log_atendimento t
                     JOIN (
                             SELECT user_id, MAX(data_hora) AS data_hora
                               FROM tbl_log_atendimento
                              WHERE user_id IN (".$idsIn.")
                                AND data_hora >= CURDATE()
                                AND data_hora < DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                                AND acao IN ('Login', 'Disponivel', 'Indisponivel', 'Tratamento', 'Logout', 'Pausa', 'Pos')
                          GROUP BY user_id
                          ) ult
                       ON ult.user_id = t.user_id
                      AND ult.data_hora = t.data_hora";
    $stmt = $PDO_LOAD->prepare($sqlLogLote);
    $stmt->execute();
    $rowsLogLote = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rowsLogLote as $row){
        $logAtendMap[$row['user_id']] = $row;
    }

    // Loop final usando os mapas em memória
    $tiles = array();
    for($y=0;$y<count($dds);$y++){
        $idUser = (int)$dds[$y]['id_user'];
        $dds[$y]['nome_completo']=ucwords((strtolower($dds[$y]['nome'])).' '.(strtolower($dds[$y]['sobrenome'][0]))).".";

        // Dados de log diário
        $ddOnline = isset($onlineMap[$idUser]) ? $onlineMap[$idUser] : array();

        // Último log de atendimento (já carregado em lote)
        $ddAtend = isset($logAtendMap[$idUser]) ? $logAtendMap[$idUser] : array('acao' => '', 'data_hora' => '');

        // Quantidade de atendimentos ativos
        $qtdAtendValor = isset($qtdAtendMap[$idUser]) ? (int)$qtdAtendMap[$idUser] : 0;
        $qtdAtend = array('qtd' => $qtdAtendValor);

        // Classificação média (estrela)
        $starValor = isset($starMap[$idUser]) ? $starMap[$idUser] : '';
        $star = array('star' => $starValor);
        $star['star'] = (date('Y-m-d')<'2021-12-06' && $star['star'] < '2.5') ? ' -.- ' : $star['star'];
        $star['star'] = ($star['star']=='' || $star['star']=='0.0') ? ' -.- ' : $star['star'];

//echo '<i class="fas fa-star" style="color: yellow"></i> '.$star['star'];
        //depurador($ddAtendAtivo);
        //depurador($ddAtend);




    if(isset($ddOnline['user_id']) && $ddOnline['user_id']!=''){
        if(($ddAtend['acao']=='Login')||($ddAtend['acao']=='Disponivel')){
            $classStatus = 'online';
            if($qtdAtend['qtd']!=0){
                $classStatus = 'atendimento';
            }
        } else
        if(($ddAtend['acao']=='Tratamento')){
            $classStatus = 'atendimento';
            if($ddAtend['acao']=='Tratamento' && $qtdAtend['qtd']==0){
                $classStatus = 'indisp';
                 $ddAtend['data_hora']='';
            }
        } else
        if(($ddAtend['acao']=='Pausa')){
            $classStatus = 'pausa';
        } else
        if(($ddAtend['acao']=='Pos')){
            $classStatus = 'pos';
            if($qtdAtend['qtd']>0){
                $classStatus = 'atendimento';
            }
        } else
        if(($ddAtend['acao']=='Logout')){
            $classStatus = 'logout';
            $ddAtend['data_hora']='';
        } else
        if(($ddAtend['acao']=='Indisponivel')){
            $classStatus = 'indisp';
            //$ddAtend['data_hora']='';
        }
    } else {
        $classStatus = 'offline';
        $ddAtend['data_hora']='';
    }







    $timeDiff='';
    if($ddAtend['data_hora']!=''){
        $timeDiff = (time_to_sec(date('H:i:s')))-(time_to_sec(date('H:i:s', strtotime($ddAtend['data_hora']))));
        $timeDiff = sec_to_time($timeDiff);
    } else {
        $timeDiff='--:--:--';
    }

    $img = ($dds[$y]['img']=='') ? 'img/perfil.fw.png' : $dds[$y]['img'];
    $tiles[] = array(
        'id_user' => $dds[$y]['id_user'],
        'nome_completo' => $dds[$y]['nome_completo'],
        'status' => $classStatus,
        'img' => $img,
        'qtd' => $qtdAtend['qtd'],
        'timeDiff' => $timeDiff,
        'star' => $star['star'],
    );
    }

    $tiles = stDashSortBkoTiles($tiles, 'status', 'nome_completo');

    echo '<div class="dash-users-grid">';
    foreach ($tiles as $tile) {
        $load = "loadInfoUser('".$tile['id_user']."', '".$_POST['id_contrato']."', '".$_POST['id_fila']."')";
        echo '<div class="dash-user-tile div_user pointer" onClick="'.$load.'">';
        if ($tile['qtd'] > 0) {
            echo '<span class="dash-user-tile__badge badge-count">'.$tile['qtd'].'</span>';
        }
        echo '<div class="dash-user-tile__avatar-wrap">';
        echo '<img src="'.$tile['img'].'" class="dash-user-tile__avatar img_perfil_online rounded-circle '.$tile['status'].'" id="user_'.$tile['id_user'].'">';
        echo '</div>';
        echo '<span class="dash-user-tile__name">'.$tile['nome_completo'].'</span>';
        echo '<span class="dash-user-tile__time" id="tempo_'.$tile['id_user'].'">'.$tile['timeDiff'].'</span>';
        echo '<span class="dash-user-tile__star" id="star_'.$tile['id_user'].'"><i class="fas fa-star" aria-hidden="true"></i> '.$tile['star'].'</span>';
        echo '</div>';
    }
    echo '</div>';
}
?>
