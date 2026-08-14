<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

//depurador($_POST);

$sql="SELECT resp_id, contrato_id, date_disp from tbl_tma_atend where date_out is null and resp_id=? and (date_format(date_disp, '%Y-%m-%d')=curdate() or date_disp is null) order by id desc limit 1";
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([(int) $infoUser['id_user']]);
$infoAtend = $stmt->fetch( PDO::FETCH_ASSOC );

if(($infoAtend['resp_id'] ?? '')==''){
    $sql="INSERT INTO tbl_tma_atend (resp_id, contrato_id, date_disp) VALUES (?, ?, now())";
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute([(int) $infoUser['id_user'], $infoUser['id_contrato']]);
    $infoAtend['date_disp'] = date('Y-m-d H:i:s');
} else if (empty($infoAtend['date_disp'])) {
    $sql="UPDATE tbl_tma_atend SET date_disp=now() where resp_id=? and date_out is null and fila_chat_id is null order by id desc limit 1";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([(int) $infoUser['id_user']]);
    $infoAtend['date_disp'] = date('Y-m-d H:i:s');
}

$sql="SELECT user_id, contrato_id, agencia_id, fila_id, data_hora, acao from tbl_log_atendimento where user_id=? order by data_hora desc limit 1";
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute([(int) $infoUser['id_user']]);
$infoLog = $stmt->fetch( PDO::FETCH_ASSOC );

$sqlVer="SELECT user_id, pause_id, hora_in from tbl_pause where date_format(hora_in, '%Y-%m-%d')=curdate() and hora_out is null and user_id=?";
$stmt = $PDO->prepare($sqlVer);
$result = $stmt->execute([(int) $_SESSION['dados']['id_user']]);
$ver = $stmt->fetch( PDO::FETCH_ASSOC );
//echo "<br>Tabela Pausa<br>";
//depurador($ver);
if(!empty($ver['user_id'])){

    //echo "<br>ETAPA 1";
    //echo "<script>setTimeout(function(){ actionPage('dash-pause', 'idx'); }, 0);</script>";
} else {
    if(!empty($infoLog['acao']) && $infoLog['acao']!='Disponivel' && $infoLog['acao']!='Tratamento'){
        logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Disponivel');
    }
}


?>


<div id="load-<?=(int)$_POST['indice'];?>" class="st-bko-wait load"
     data-date-disp="<?= htmlspecialchars($infoAtend['date_disp'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8') ?>"
     data-fila-id="<?= (int)$infoUser['fila_id'] ?>">
    <div class="st-bko-wait__panel">
        <img src="img/loading.gif" alt="Carregando..." class="st-bko-wait__spinner" width="72">
        <p class="st-bko-wait__text">Buscando atendimento...</p>
        <button type="button" id="btn_pausa_<?=(int)$_POST['indice'];?>" class="btn btn-outline-danger st-bko-wait__pause" style="display: none;" onclick="stBkoBtnPause(<?=(int)$_POST['indice'];?>, 'pause')">
            <i class="fas fa-pause-circle" aria-hidden="true"></i> Realizar pausa
        </button>
        <div id="div_feed_pausa_<?=(int)$_POST['indice'];?>" class="st-bko-wait__feed"></div>
    </div>
</div>

<div id="feed-<?=(int)$_POST['indice'];?>"></div>
