<?php
include("../cnf/session.php");

//depurador($_POST);

$sql="SELECT id_chat, rem_chat, dest_chat, indice, contrato_id, token_chat from tbl_chat_info where fila_chat_id=".$_POST['id_fila'];
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$info = $stmt->fetch( PDO::FETCH_ASSOC );




if($info['id_chat']!=''){

    ?>
    <input type="hidden" id="id_user_remetente_<?=$chatId?>" name="id_user_remetente_<?=$chatId?>" value="<?=$info['rem_chat'];?>">
    <input type="hidden" id="id_user_destinatario_<?=$chatId?>" name="id_user_destinatario_<?=$chatId?>" value="<?=$info['dest_chat'];?>">
    <script>
        var remetente = '<?=$info['rem_chat'];?>';
        var destinatario = '<?=$info['dest_chat'];?>';
        var mensagem = 'Atendimento encerrado pelo Gestor';
        var chatId = '<?=$chatId?>';
        var indice = $('#indice_'+<?=$info['indice'];?>).val();
        chatFim(chatId, destinatario, <?=$info['contrato_id']?>, '<?=$info['token_chat'];?>', mensagem, indice);
        //chatFim(chatId, remetente, <?=$info['contrato_id']?>, '<?=$info['token_chat'];?>', mensagem, indice);
    </script>
    <?php
    $chatId=$info['id_chat'];
    $update="UPDATE tbl_chat_info SET status_chat=9 where fila_chat_id=".$_POST['id_fila'];
    //echo "<br>".$update;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
}


$update="UPDATE tbl_chat_fila SET status_fila=9 where id_fila_chat=".$_POST['id_fila'];
//echo "<br>".$update;
$stmt = $PDO->prepare( $update );
$result = $stmt->execute();

$delete="DELETE FROM tbl_tma_atend where fila_chat_id=".$_POST['id_fila'];
//echo "<br>".$update;
$stmt = $PDO->prepare( $delete );
$result = $stmt->execute();


//echo "<br>".$result;

if($result){
    echo '<i class="fas fa-check-circle fa-2x" style="color: green"></i>';
}


