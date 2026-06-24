<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

$sql="SELECT a.id_chat, a.fila_chat_id, timediff(now(), b.hora_inicio) as ta  from tbl_chat_info a, tbl_chat_fila b where a.status_chat=1 and a.fila_chat_id=b.id_fila_chat and a.token_chat='".$_POST['tokenChat']."'";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoChat = $stmt->fetch( PDO::FETCH_ASSOC );


$sql = "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES ('".$infoChat['id_chat']."', '".$_POST['contrato']."', '".$_POST['rem']."', '".$_POST['dest']."', '".$_POST['msg']."')";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result){
    $sql="UPDATE tbl_chat_fila SET status_fila=6, hora_fim=now(), ta='".$infoChat['ta']."' where id_fila_chat=".$infoChat['fila_chat_id'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();

    if($result){
        $sql="UPDATE tbl_chat_info SET status_chat=6 where id_chat=".$infoChat['id_chat'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();

        if($result){
            $sql="SELECT id, timediff(now(), date_in) as sla from tbl_tma_atend where chat_id=".$infoChat['id_chat']." and fila_chat_id=".$infoChat['fila_chat_id'];
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
            $infoAtend = $stmt->fetch( PDO::FETCH_ASSOC );

            if($infoAtend['id']!=''){

                $sql="UPDATE tbl_tma_atend SET date_out=now(), sla='".$infoAtend['sla']."' where id=".$infoAtend['id'];
                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute();
            }

            $sql="SELECT protocolo, ate_resp, bko_resp from tbl_chat_fila where id_fila_chat=".$infoChat['fila_chat_id'];
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
            $infoFilaNew = $stmt->fetch( PDO::FETCH_ASSOC );

            $sql="INSERT INTO tbl_chat_fila (protocolo, contrato_id, fila_id, assunto_id, ate_resp)";
            $sql .=" VALUES ('".$infoFilaNew['protocolo']."', '".$_POST['contrato']."', '".$_POST['fila']."', '".$_POST['assunto']."', '".$infoFilaNew['ate_resp']."')";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
                      
                    
        }
    }

}




?>
