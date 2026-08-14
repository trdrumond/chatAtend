<?php
include_once("conn.php");

//echo "<br>";
$sql="SELECT id, fila_chat_id, chat_id, resp_id from tbl_tma_atend where fila_chat_id is not null and date_out is null and date_format(date_disp, '%Y-%m-%d')=curdate()";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$pend_info = $stmt->fetchAll( PDO::FETCH_ASSOC );
//echo "<br>";
//depurador($pend_info);
//echo "<br>";
for($x=0;$x<count($pend_info);$x++){
    $tmaId = (int) ($pend_info[$x]['id'] ?? 0);
    $filaChatId = (int) ($pend_info[$x]['fila_chat_id'] ?? 0);
    if ($tmaId <= 0 || $filaChatId <= 0) {
        continue;
    }
    $sql="SELECT id_fila_chat from tbl_chat_fila_secondary where id_fila_chat=? and status_fila<>2";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$filaChatId]);
    $infoDem = $stmt->fetch( PDO::FETCH_ASSOC );
    if($infoDem){
        //depurador($infoDem);
        $sql="DELETE FROM tbl_tma_atend where id=?";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$tmaId]);

        $sql="DELETE FROM tbl_tma_atend_secondary where id=?";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute([$tmaId]);


    }
}



?>
