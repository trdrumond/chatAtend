<?php
include("../cnf/session.php");

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
    $bkoId = (int) $_SESSION['dados']['id_user'];
    $motivoSit = addslashes((string) $_POST['motivo_situacao']);
    $sql = 'UPDATE tbl_chat_fila SET'
        .' status_fila='.(int) $_POST['situacao_dem'].','
        .' hora_fim = IF(hora_fim IS NULL OR hora_fim = \'\' OR hora_fim = \'0000-00-00 00:00:00\', NOW(), hora_fim),'
        .' hora_inicio = IF(hora_inicio IS NULL OR hora_inicio = \'\' OR hora_inicio = \'0000-00-00 00:00:00\', NOW(), hora_inicio),'
        .' ta = IF(ta IS NULL OR ta = \'\' OR ta = \'00:00:00\', TIMEDIFF(NOW(), IF(hora_inicio IS NULL OR hora_inicio = \'\' OR hora_inicio = \'0000-00-00 00:00:00\', NOW(), hora_inicio)), ta),'
        .' te = IF(te IS NULL OR te = \'\' OR te = \'00:00:00\', TIMEDIFF(IF(hora_inicio IS NULL OR hora_inicio = \'\' OR hora_inicio = \'0000-00-00 00:00:00\', NOW(), hora_inicio), data_hora), te),'
        .' bko_resp = IF(bko_resp IS NULL OR bko_resp = 0 OR bko_resp = \'\', '.$bkoId.', bko_resp),'
        .' motivo = \''.$motivoSit.'\''
        .' WHERE id_fila_chat='.(int) $infoChat['fila_chat_id'];
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
clearInterval(time_<?=$infoChat['id_chat']?>);
//$('#close').click();
//location.reload();
//console.log('save pos');
setTimeout(function() {
    fechaAba(<?=$_POST['indice']?>);
}, 100);
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
clearInterval(time_<?=$infoChat['id_chat']?>);
//$('#close').click();
//location.reload();
//console.log('save pos');
setTimeout(function() {
    fechaAba(<?=$_POST['indice']?>);
}, 100);
</script>
<?php

    }


}




?>
