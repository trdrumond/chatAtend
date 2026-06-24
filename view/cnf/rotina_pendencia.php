<?php
include_once("conn.php");

//echo "<br>";
$sql="SELECT id_fila_chat from tbl_chat_fila where (status_fila=1 or status_fila=2) and date_format(data_hora, '%Y-%m-%d')<curdate()";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$pend_info = $stmt->fetchAll( PDO::FETCH_ASSOC );
//echo "<br>";
//depurador($pend_info);
//echo "<br>";
for($x=0;$x<count($pend_info);$x++){
    $sql="UPDATE tbl_chat_fila SET status_fila=5, ta='00:00:00', te='00:00:00' where id_fila_chat=".$pend_info[$x]['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_fila_secondary SET status_fila=5, ta='00:00:00', te='00:00:00' where id_fila_chat=".$pend_info[$x]['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info_secondary SET status_chat=5 where fila_chat_id=".$pend_info[$x]['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();

    $sql="UPDATE tbl_chat_info_secondary SET status_chat=5 where fila_chat_id=".$pend_info[$x]['id_fila_chat'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();

    $sql="DELETE FROM tbl_tma_atend where fila_chat_id=".$pend_info[$x]['id_fila_chat'];
    //echo "<br>".$sql;
    //$stmt = $PDO->prepare($sql);
    //$result = $stmt->execute();

}
//4965361
//echo "<br>teste";




?>
