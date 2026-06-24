<?php
include("../cnf/session.php");

//depurador($_POST);



//$protocolo = date('ymd') . rand(1, 99) . time_to_sec(date('H:i:s'));

//echo "<br>".$protocolo;



    $sql="UPDATE tbl_chat_fila SET status_fila=10 where id_fila_chat=".$_POST['id_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info SET status_chat=10 where fila_chat_id=".$_POST['id_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();


    $stm = $PDO->query("SELECT protocolo, contrato_id, fila_id, assunto_id, ate_resp from tbl_chat_fila where id_fila_chat=".$_POST['id_chat']);
    $infoChat = $stm->fetch(PDO::FETCH_ASSOC);

    $sql="INSERT INTO tbl_chat_fila (protocolo, contrato_id, fila_id, assunto_id, ate_resp)";
    $sql .=" VALUES ('".$infoChat['protocolo']."', '".$infoChat['contrato_id']."', '".$infoChat['fila_id']."', '".$infoChat['assunto_id']."', '".$infoChat['ate_resp']."')";

    //echo "<br>".$sql;

    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

if($result==1){
?>
<script>

    actionPage('chat-fila', 'idx');

    function actionPage(action, sec){
        $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
        $.post("action.php",
        {
            action: action, sec: sec
        },
        function (valor) {
            $("#action-page").html(valor);
        });
    }


</script>
<?php
    }

    ?>
