<?php
include("../cnf/session.php");

//depurador($_POST);

//$secounds = 300;

//$scd = $secounds;

include('../cnf/rotina_ocio.php');



$filaIdLog = (int) ($infoUser['fila_id'] ?? 0);

$sql = "SELECT count(user_id) as qtd_user FROM tbl_log_diario where data_log=curdate() and nivel_id=4 and date_out is null";
$logParams = [];
if ((int) ($infoUser['nivel_id'] ?? 0) === 4) {
    $sql .= ' and fila_id=?';
    $logParams[] = $filaIdLog;
}

//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute($logParams);
$infoGeral = $stmt->fetch( PDO::FETCH_ASSOC );



//echo $infoGeral['qtd_user'] . " / " . $userInfo['qtd_user'];
echo $infoGeral['qtd_user'];



?>
