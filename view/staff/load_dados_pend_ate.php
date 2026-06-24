<?php
include("../cnf/session.php");

//depurador($_POST);



    $qeryPend = " and ate_resp='".$idu."' and data_hora_fim is not null and data_hora_visualizacao is null";

    $sql="SELECT id_pend, chat_id, (SELECT protocolo from tbl_chat_fila where id_fila_chat=chat_id) as protocolo, (SELECT id_chat from tbl_chat_info where fila_chat_id=chat_id) as id_chat FROM tbl_pend_info where situacao_id=3 $qeryPend";
    //echo "<br>".$sql;
    $stmt = $PDO_LOAD->prepare($sql);
    $result = $stmt->execute();
    $dadosContratos = $stmt->fetchAll( PDO::FETCH_ASSOC );
    $count=0;
    for($z=0;$z<count($dadosContratos);$z++){
        if($dadosContratos[$z]['id_pend']!=''){
            $dadosContratos[$z]['protocolo'];
            echo "<script>notPend('".$dadosContratos[$z]['protocolo']."', '".$dadosContratos[$z]['id_chat']."');</script>";
        }
    }




?>





