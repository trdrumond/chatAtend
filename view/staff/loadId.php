<?php
include("../cnf/session.php");

$sql="SELECT id_fila_chat, (SELECT id_chat from tbl_chat_info where fila_chat_id=id_fila_chat) as chat_id from tbl_chat_fila where protocolo='".$_POST['protocolo']."' order by id_fila_chat desc";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoId = $stmt->fetch( PDO::FETCH_ASSOC );
    $chatId = $infoId['chat_id'];
?>
<script>
    var chat_id = '<?=$chatId?>';
    //console.log(chat_id);
</script>


