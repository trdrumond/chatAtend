<?php
include_once("conn.php");
include_once("func.php");


//echo "<br>Rotina Ocio";

$sql = "SELECT id_chat from tbl_chat_info_secondary where date_format(data_hora, '%Y-%m-%d') < curdate() limit 5";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoFila = $stmt->fetchAll( PDO::FETCH_ASSOC );
//depurador($infoFila);

if(count($infoFila)>0){
    for($x=0;$x<count($infoFila);$x++){
        //echo "<br>".$infoFila[$x]['id_chat'];
        $sqlInsLog="UPDATE tbl_chat_info_secondary SET status_chat=5 where id_chat=".$infoFila[$x]['id_chat'];
        //echo "<br>".$sqlInsLog;
        $stmt = $PDO->prepare( $sqlInsLog );
        $execInsLog = $stmt->execute();
        //var_dump($execInsLog);
        //echo "<br>";

        $sqlInsLog="UPDATE tbl_chat_info_secondary SET status_chat=5 where id_chat=".$infoFila[$x]['id_chat'];
        //echo "<br>".$sqlInsLog;
        $stmt = $PDO->prepare( $sqlInsLog );
        $execInsLog = $stmt->execute();
        //var_dump($execInsLog);
        //echo "<br>";
    }
}

?>
