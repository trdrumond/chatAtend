<?php
include_once("conexao.php");
include_once("func.php");


function getDivMsg($count){
    $div = $count / 100;
    $param = round($div) + 1;
    $div = number_format($div, 0, '.', '');
    return $div;
}
if(date('H:i:s') < '08:00:00'){ 
//echo "<br>Teste de query";
$sql1="SELECT id_msg, data_hora, chat_id, contrato_id, rem_id, dest_id, msg, flag from tbl_chat_msg";
//echo "<br>".$sql1;
$stmt = $PDO->prepare($sql1);
//var_dump($stmt->execute());

if ($stmt->execute()) {
    //echo "<br>Fazer consulta!";
    $info1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //echo "<br>Deu certo!";
} else {
    ////echo "<br>deu ruim com a consulta.";
    $errorInfo = $stmt->errorInfo();
    //echo "Erro: " . $errorInfo[2]; // Imprime a mensagem de erro SQL
}
//echo "<br>Teste 1.3";
//echo "<br>".count($info1);

$param = getDivMsg(count($info5));

for($i = 0; $i < $param; $i++){
    $start = $i * 100;
    $end = ($i + 1) * 100;

    $sqlNew5='';
    $sqlNew5="REPLACE INTO tbl_chat_msg_secondary (id_msg, data_hora, chat_id, contrato_id, rem_id, dest_id, msg, flag) VALUES ";
    for($x=$start;$x<$end;$x++){
        if($x < count($info5)){
            $ls=$info5[$x];
            $ls['id_msg'] = ($ls['id_msg'] =='') ? 'NULL' : "'".nomeCampo($ls['id_msg'])."'";
            $ls['data_hora'] = ($ls['data_hora'] =='') ? 'NULL' : "'".nomeCampo($ls['data_hora'])."'";
            $ls['chat_id'] = ($ls['chat_id'] =='') ? 'NULL' : "'".nomeCampo($ls['chat_id'])."'";
            $ls['contrato_id'] = ($ls['contrato_id'] =='') ? 'NULL' : "'".nomeCampo($ls['contrato_id'])."'";
            $ls['rem_id'] = ($ls['rem_id'] =='') ? 'NULL' : "'".nomeCampo($ls['rem_id'])."'";
            $ls['dest_id'] = ($ls['dest_id'] =='') ? 'NULL' : "'".nomeCampo($ls['dest_id'])."'";
            $ls['msg'] = ($ls['msg'] =='') ? 'NULL' : "'".nomeCampo($ls['msg'])."'";
            $ls['flag'] = ($ls['flag'] =='') ? 'NULL' : "'".nomeCampo($ls['flag'])."'";

            $sqlNew5 .= "(".$ls['id_msg'].",".$ls['data_hora'].", ".$ls['chat_id'].",".$ls['contrato_id'].",".$ls['rem_id'].",".$ls['dest_id'].",".$ls['msg'].",".$ls['flag'].")";

            if(($x!=$end-1) && ($x!=(count($info5)-1))){$sqlNew5.=',';}
        }

    }


    if($infoUser['id_user']==1){
        //echo "<br>".$sqlNew5;
    }

    $stmt6 = $PDO->prepare($sqlNew5);
    $result6 = $stmt6->execute();

    if($result6){
        if($infoUser['id_user']==1){
            //echo "<br>OK ".$end;
        }
    }else{
        if($infoUser['id_user']==1){
            //echo "<br>Erro ".$end;
            //echo "<br>".$sqlNew1;
        }
    }

}
}

?>