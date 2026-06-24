<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

//depurador($_SESSION);
//depurador($infoUser);
//depurador($_POST);

if($_POST['pausa']==1){
    $sqlPause = "INSERT INTO tbl_pause (user_id, hora_in, pause_id) VALUES ('".$_SESSION['dados']['id_user']."', now(), 1)";
    //echo "<br>".$sqlPause;
    $stmt = $PDO->prepare( $sqlPause );
    $resultDem = $stmt->execute();
    logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Pausa');
}




$sql="SELECT id_chat, fila_chat_id, rem_chat, dest_chat, contrato_id  from tbl_chat_info a where token_chat='".$_POST['tokenChat']."'";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoChat = $stmt->fetch( PDO::FETCH_ASSOC );



$stm = $PDO->query("SELECT timediff(now(), hora_fim) as tp from tbl_chat_fila where id_fila_chat=".$infoChat['fila_chat_id']);
$tp = $stm->fetch(PDO::FETCH_ASSOC);


if($_POST['situacao_dem']==3 || $_POST['situacao_dem']==7){

    $infoChat['fila_chat_id'];
    $sql="UPDATE tbl_chat_fila SET status_fila=".$_POST['situacao_dem']." where id_fila_chat=".$infoChat['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info SET status_chat=".$_POST['situacao_dem']." where fila_chat_id=".$infoChat['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $stm = $PDO->query("SELECT nome_situacao from tbl_situacao_chat where id_situacao=".$_POST['situacao_dem']);
    $sit = $stm->fetch(PDO::FETCH_ASSOC);

    $msg = $sit['nome_situacao'] . ' - '. $_POST['motivo_situacao'];

    $sqlMsg = "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES ('".$infoChat['id_chat']."', '".$infoChat['contrato_id']."', 0, 0, '".$msg."')";
    //echo "<br>".$sqlMsg;
    $stmt = $PDO->prepare( $sqlMsg );
    $resultMeg = $stmt->execute();
}

if($_POST['situacao_dem']==3){
        $sqlPend = "INSERT INTO tbl_pend_info (fila_id, chat_id, ate_resp, bko_resp, situacao_id, motivo) VALUES ('".$_POST['fila_id']."', '".$infoChat['fila_chat_id']."', '".$infoChat['dest_chat']."', '".$_SESSION['dados']['id_user']."', '".$_POST['situacao_dem']."', '".$_POST['motivo_situacao']."')";
        //echo "<br>".$sqlPend;
        $stmt = $PDO->prepare( $sqlPend );
        $resultPend = $stmt->execute();
    }

if($_POST['situacao_dem']!=3 && $_POST['situacao_dem']!=7){
    $stmt = $PDO->prepare('UPDATE tbl_chat_fila SET status_fila=? WHERE id_fila_chat=? AND '.stFilaSqlNaoEncerrado('status_fila'));
    $stmt->execute([ST_FILA_CONCLUIDO, (int)$infoChat['fila_chat_id']]);
    $stmt = $PDO->prepare('UPDATE tbl_chat_info SET status_chat=?, indice=0 WHERE fila_chat_id=? AND status_chat=1');
    $stmt->execute([ST_FILA_CONCLUIDO, (int)$infoChat['fila_chat_id']]);
}

$stm = $PDO->query("SELECT count(*) as qtd from tbl_forms_pos_input_campo where fila_id=".$_POST['fila_id']);
$fila = $stm->fetch(PDO::FETCH_ASSOC);
if($fila['qtd']<1){
    $sql="UPDATE tbl_chat_fila SET assunto_id=".$_POST['assunto']." where id_fila_chat=".$infoChat['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sqlAlterDados ="INSERT INTO tbl_in_pos_".$_POST['fila_id']."_".$infoUser['contrato_id'];
    $sqlAlterDados .=" (data_hora, situacao_id, fila_id, chat_id, tp";
    $sqlAlterDados .=") VALUES (now(), 4, '".$_POST['fila_id']."', '".$infoChat['fila_chat_id']."', '".$tp['tp']."'";
    $sqlAlterDados .=")";


    //echo "<br><br>".$sqlAlterDados;
    $stmt = $PDO->prepare( $sqlAlterDados );
    $resultDados = $stmt->execute();

    if($_POST['pausa']!=1){logAtendimento($PDO, $_SESSION['dados']['id_user'], 'Disponivel');}

    if($resultDados){
        ?>
<script>
if (typeof stBkoStopTa === 'function') {
    stBkoStopTa(<?= (int)$infoChat['id_chat'] ?>);
} else if (typeof window['time_<?= (int)$infoChat['id_chat'] ?>'] !== 'undefined') {
    clearInterval(window['time_<?= (int)$infoChat['id_chat'] ?>']);
}
var indiceAba = <?=(int)$_POST['indice']?>;
if (typeof stChatMarkEnded === 'function') {
    stChatMarkEnded(<?= (int)$infoChat['id_chat'] ?>, <?= json_encode((string)($_POST['tokenChat'] ?? ''), JSON_UNESCAPED_UNICODE) ?>);
}
$("#div-" + indiceAba).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"><br>ORGANIZANDO FILA PARA UM NOVO ATENDIMENTO...</div>');
setTimeout(function() {
    var qtdTabs = $('.tab').length;
    if (qtdTabs <= 1 && typeof window.stBkoReturnToQueue === 'function') {
        if (typeof window.stBkoCloseTab === 'function') {
            window.stBkoCloseTab(indiceAba);
        }
        $('#title-' + indiceAba).remove();
        $('#div-' + indiceAba).remove();
        window.stBkoReturnToQueue(1);
        return;
    }
    fechaAba(indiceAba);
}, 600);
</script>
<?php
    }
} else {
    //echo 'Grava personalizado';
    $sql = "SELECT nome_campo FROM tbl_forms_pos_input_campo where fila_id=".$_POST['fila_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $campo = $stmt->fetchAll( PDO::FETCH_ASSOC );
    //depurador($campo);

    $sql="UPDATE tbl_chat_fila SET assunto_id=".$_POST['confirma_assunto']." where id_fila_chat=".$infoChat['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info SET assunto_id=".$_POST['confirma_assunto']." where fila_chat_id=".$infoChat['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();



    $sqlAlterDados ="INSERT INTO tbl_in_pos_".$_POST['fila_id']."_".$infoUser['contrato_id'];
    $sqlAlterDados .=" (data_hora, situacao_id, fila_id, chat_id, tp";
    for($x=0;$x<count($campo);$x++){ $sqlAlterDados.= ", ".$campo[$x]['nome_campo'].""; }
    $sqlAlterDados .=") VALUES (now(), 4, '".$_POST['fila_id']."', '".$infoChat['fila_chat_id']."', '".$tp['tp']."'";
    for($x=0;$x<count($campo);$x++){ $sqlAlterDados.= ",'".$_POST[$campo[$x]['nome_campo']]."'"; }
    $sqlAlterDados .=")";


    //echo "<br><br>".$sqlAlterDados;
    $stmt = $PDO->prepare( $sqlAlterDados );
    $resultDados = $stmt->execute();
    //echo $resultDados;

    if($resultDados==1){
        ?>
<script>
if (typeof stBkoStopTa === 'function') {
    stBkoStopTa(<?= (int)$infoChat['id_chat'] ?>);
} else if (typeof window['time_<?= (int)$infoChat['id_chat'] ?>'] !== 'undefined') {
    clearInterval(window['time_<?= (int)$infoChat['id_chat'] ?>']);
}
var indiceAba = <?=(int)$_POST['indice']?>;
if (typeof stChatMarkEnded === 'function') {
    stChatMarkEnded(<?= (int)$infoChat['id_chat'] ?>, <?= json_encode((string)($_POST['tokenChat'] ?? ''), JSON_UNESCAPED_UNICODE) ?>);
}
$("#div-" + indiceAba).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="200"><br>ORGANIZANDO FILA PARA UM NOVO ATENDIMENTO...</div>');
setTimeout(function() {
    var qtdTabs = $('.tab').length;
    if (qtdTabs <= 1 && typeof window.stBkoReturnToQueue === 'function') {
        if (typeof window.stBkoCloseTab === 'function') {
            window.stBkoCloseTab(indiceAba);
        }
        $('#title-' + indiceAba).remove();
        $('#div-' + indiceAba).remove();
        window.stBkoReturnToQueue(1);
        return;
    }
    fechaAba(indiceAba);
}, 600);
</script>
<?php

    }


}




?>
