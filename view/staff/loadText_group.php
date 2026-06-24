<?php
include("../cnf/session.php");


    //echo "executa script de leitura";
    $sqlVisual="UPDATE tbl_com_msg_group_view SET dt_view=now() where group_chat=".$_POST['chatId']." and user_id=".$infoUser['id_user'];
    //echo "<br>".$sqlVisual;
    $stmt = $PDO->prepare( $sqlVisual );
    $result = $stmt->execute();


echo $_POST['msg'];
?>

<script>
loadComList();
</script>





