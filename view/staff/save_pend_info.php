<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($infoUser);
//depurador($_POST);



    $sql="SELECT ate_resp, bko_resp from tbl_chat_fila where id_fila_chat=".$_POST['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );

    if(count($infoChat)==0){
        $sql="SELECT ate_resp, bko_resp from tbl_chat_fila_secondary where id_fila_chat=".$_POST['id_fila_chat'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
    }

    $sql = "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES ('".$_POST['id_chat']."', '".$infoUser['contrato_id']."', '".$infoChat['bko_resp']."', '".$infoChat['ate_resp']."', '".$_POST['txt_pend']."')";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_pend_info SET info_fim='".$_POST['txt_pend']."', data_hora_fim=now() where chat_id=".$_POST['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();



    $sql="UPDATE tbl_chat_fila SET status_fila=4 where id_fila_chat=".$_POST['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_fila_secondary SET status_fila=4 where id_fila_chat=".$_POST['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info SET status_chat=4 where fila_chat_id=".$_POST['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info_secondary SET status_chat=4 where fila_chat_id=".$_POST['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();



?>