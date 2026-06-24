<?php
include("../cnf/session.php");

    $sql="SELECT bko_resp from tbl_chat_fila where id_fila_chat=".$_POST['id'];
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
    if($infoChat['bko_resp'] != $_POST['resp_id']){
?>
<script>
    document.location.reload(true);
    //console.log(<?=$_POST['resp_id']?>);
    //console.log(<?=$infoChat['bko_resp']?>);

</script>

<?php } ?>
