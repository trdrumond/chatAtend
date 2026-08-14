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
        $chatIdOcio = (int) ($infoFila[$x]['id_chat'] ?? 0);
        if ($chatIdOcio <= 0) {
            continue;
        }
        //echo "<br>".$chatIdOcio;
        $sqlInsLog="UPDATE tbl_chat_info_secondary SET status_chat=5 where id_chat=?";
        //echo "<br>".$sqlInsLog;
        $stmt = $PDO->prepare( $sqlInsLog );
        $execInsLog = $stmt->execute([$chatIdOcio]);
        //var_dump($execInsLog);
        //echo "<br>";
    }
}

?>
