<?php
include("../cnf/session.php");

//depurador($_POST);

//$secounds = 300;

//$scd = $secounds;

include('../cnf/rotina_ocio.php');



if($_POST['contrato_id']==0){
    $contratos=$infoUserConfig['contrato_id'];
} else {
    $contratos=$infoUser['contrato_id'];
}

if($infoUser['nivel_id']==4){
    $sql_fila = "and fila_id='".$infoUser['fila_id']."'";
} else {
    $sql_fila="";
}

/*
$sql = "SELECT count(id_user) as qtd_user from tbl_user where ativo=1 and nivel_id=4 and contrato_id in (".$contratos.")";
echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$userInfo = $stmt->fetch( PDO::FETCH_ASSOC );
*/

//$sql = "SELECT count(user_id) as qtd_user FROM tbl_log_diario where data_log=curdate() and nivel_id=4 and ((date_out is not null) or (time_to_sec(date_format(date_up, '%H:%i:%s'))>=(time_to_sec(curtime())-".$secounds."))) and  contrato_id in (".$contratos.") $sql_fila";

$sql = "SELECT count(user_id) as qtd_user FROM tbl_log_diario where data_log=curdate() and nivel_id=4 and date_out is null $sql_fila";

//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );



//echo $infoGeral['qtd_user'] . " / " . $userInfo['qtd_user'];
echo $infoGeral['qtd_user'];



?>
