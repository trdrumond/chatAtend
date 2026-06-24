<?php
include("../cnf/conn.php");


//depurador($_POST);


if($_POST['id_fila']!=0){
    $sql_fila = "and fila_id=".$_POST['id_fila']." and contrato_id=".$_POST['id_contrato'];
} else {
    $sql_fila = '';
}

    $sql="SELECT count(*) as qtd, timediff(curtime(), date_format(data_hora, '%H:%i:%s')) as tempo_decorrido from tbl_chat_fila where status_fila=1 and fila_id=".$_POST['id_fila'];
    $stmt = $PDO_LOAD->prepare($sql);
    $result = $stmt->execute();
    $ddFila = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($ddFila);
    if($ddFila['qtd']==0){
        $ddFila['tempo_decorrido']='--:--:--';
        $class_fila='offline';
    } else {
        $class_fila = 'fila_count';
    }


    echo '<div class="dash-users-grid">';
    echo '<div class="dash-user-tile dash-user-tile--queue div_user">';
    echo '<div class="dash-user-tile__avatar-wrap">';
    echo '<span class="dash-user-tile__avatar img_perfil_online rounded-circle '.$class_fila.' label_count_fila">'.$ddFila['qtd'].'</span>';
    echo '</div>';
    echo '<span class="dash-user-tile__name">T. Espera</span>';
    echo '<span class="dash-user-tile__time" id="tempo_0">'.$ddFila['tempo_decorrido'].'</span>';
    echo '</div>';


$sql = "SELECT id_user, nome, sobrenome, (SELECT img from tbl_user_img_perfil where user_id=id_user) as img, (SELECT IF(acao<>'Logout', data_hora, '') as data_hora from tbl_log_atendimento where user_id=id_user and date_format(data_hora, '%Y-%m-%d')=curdate() order by data_hora desc limit 1) as date_disp, (SELECT IF(acao<>'Logout', acao, '') as acao from tbl_log_atendimento where user_id=id_user and date_format(data_hora, '%Y-%m-%d')=curdate() order by data_hora desc limit 1) as acao from tbl_user where ativo=1 and nivel_id=4 $sql_fila order by acao desc, date_disp desc, nome asc";
//echo "<br><br>".$sql."<br>";
$stmt = $PDO_LOAD->prepare($sql);
$result = $stmt->execute();
$dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($dds);

$starMap = array();
if (count($dds) > 0) {
    $userIds = array();
    for ($i = 0; $i < count($dds); $i++) {
        $userIds[] = (int) $dds[$i]['id_user'];
    }
    $idsIn = implode(',', array_unique($userIds));
    $day = (date('Y-m-d') < '2021-12-06') ? 1 : 5;
    $sqlStarLote = "SELECT ate, FORMAT(AVG(star), 1) AS star"
        . " FROM tbl_classificacao"
        . " WHERE star IS NOT NULL AND star <> ''"
        . " AND data_hora >= '0001-01-01'"
        . " AND data_hora < DATE_SUB(CURDATE(), INTERVAL $day DAY)"
        . " AND ate IN ($idsIn)"
        . " GROUP BY ate";
    $stmt = $PDO_LOAD->prepare($sqlStarLote);
    $stmt->execute();
    $rowsStar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsStar as $row) {
        $starMap[$row['ate']] = $row['star'];
    }
}

for($y=0;$y<count($dds);$y++){
    $dds[$y]['nome_completo']=ucwords((strtolower($dds[$y]['nome'])).' '.(strtolower($dds[$y]['sobrenome'][0]))).".";
    $sqlOnline = "SELECT user_id, date_out, date_up FROM tbl_log_diario where data_log=curdate() and user_id=".$dds[$y]['id_user'];
    //echo "<br>".$sqlOnline;
    $stmt = $PDO_LOAD->prepare($sqlOnline);
    $result = $stmt->execute();
    $ddOnline = $stmt->fetch( PDO::FETCH_ASSOC );

    $sqlAtendAtivo ="SELECT id, date_in from tbl_tma_atend where resp_id=".$dds[$y]['id_user']." and date_format(date_disp, '%Y-%m-%d')=curdate() and date_format(date_in, '%Y-%m-%d')=curdate() and date_out is null order by date_in asc limit 1";
    //echo "<br>".$sqlAtendAtivo;
    $stmt = $PDO_LOAD->prepare($sqlAtendAtivo);
    $result = $stmt->execute();
    $ddAtendAtivo = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($ddAtendAtivo);
    if($ddAtendAtivo['id']!=''){ $qry = " and acao='Tratamento'"; } else { $qry=""; }

    $sqlAtend ="SELECT acao, data_hora, (timediff(now(), data_hora)) as tempo FROM tbl_log_atendimento where user_id=".$dds[$y]['id_user']." and date_format(data_hora, '%Y-%m-%d')=curdate() and acao IN ('Login', 'Disponivel', 'Indisponivel', 'Tratamento', 'Logout', 'Pausa', 'Pos') $qry order by data_hora desc limit 1";
    //echo "<br>". $sqlAtend;
    $stmt = $PDO_LOAD->prepare($sqlAtend);
    $result = $stmt->execute();
    $ddAtend = $stmt->fetch( PDO::FETCH_ASSOC );

    if($ddAtendAtivo['id']!=''){ $ddAtend['data_hora']=$ddAtendAtivo['date_in']; }

    $sqlAtendimento ="SELECT count(id_fila_chat) as qtd FROM tbl_chat_fila where bko_resp=".$dds[$y]['id_user']." AND ".stFilaSqlAtendimentoAtivo();
    //echo "<br>". $sqlAtend;
    $stmt = $PDO_LOAD->prepare($sqlAtendimento);
    $result = $stmt->execute();
    $qtdAtend = $stmt->fetch( PDO::FETCH_ASSOC );


    if($ddOnline['user_id']!=''){
        if(($ddAtend['acao']=='Login')||($ddAtend['acao']=='Disponivel')){
            $classStatus = 'online';
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

    $dds[$y]['img'] = ($dds[$y]['img']=='') ? 'img/perfil.fw.png' : $dds[$y]['img'];

    $idUser = (int) $dds[$y]['id_user'];
    $starValor = isset($starMap[$idUser]) ? $starMap[$idUser] : '';
    $starDisplay = (date('Y-m-d') < '2021-12-06' && $starValor < '2.5') ? ' -.- ' : $starValor;
    $starDisplay = ($starDisplay === '' || $starDisplay === '0.0') ? ' -.- ' : $starDisplay;

    $load = "loadInfoUser('".$dds[$y]['id_user']."', '".$_POST['id_contrato']."', '".$_POST['id_fila']."')";
    echo "<script>
                var tempo_atend_".$dds[$y]['id_user'].";
                clearInterval(tempo_atend_".$dds[$y]['id_user'].");
          </script>";
    echo '<div class="dash-user-tile div_user">';
    if ($qtdAtend['qtd'] > 0) {
        echo '<span class="dash-user-tile__badge badge-count">'.$qtdAtend['qtd'].'</span>';
    }
    echo '<div class="dash-user-tile__avatar-wrap">';
    echo '<img src="'.$dds[$y]['img'].'" class="dash-user-tile__avatar img_perfil_online rounded-circle '.$classStatus.'" id="user_'.$dds[$y]['id_user'].'">';
    echo '</div>';
    echo '<span class="dash-user-tile__name">'.$dds[$y]['nome_completo'].'</span>';
    echo '<span class="dash-user-tile__time" id="tempo_'.$dds[$y]['id_user'].'">'.$timeDiff.'</span>';
    echo '<span class="dash-user-tile__star" id="star_'.$dds[$y]['id_user'].'"><i class="fas fa-star" aria-hidden="true"></i> '.$starDisplay.'</span>';
    echo '</div>';
          if($ddAtend['data_hora']!=''){
            ?>

            <?php
          }


}
echo '</div>';
?>


