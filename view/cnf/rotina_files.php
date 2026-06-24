<?php
include_once("conn.php");

if(date('H:i:s') < '08:00:00'){ 

//ROTINA DE ARQUIVO 90 DIAS
$sql = "SELECT id_file, link_file from tbl_chat_files where date_format(data_hora, '%Y-%m-%d') < SUBDATE(CURDATE(), 90) order by data_hora desc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoFile = $stmt->fetchAll( PDO::FETCH_ASSOC );

if(count($infoFile)>0){
    for($x=0;$x<count($infoFile);$x++){
        $file = "view/".$infoFile[$x]['link_file'];
        $sqlDel="DELETE FROM tbl_chat_files where id_file=".$infoFile[$x]['id_file'];
        if(file_exists($file)){
            if(unlink($file)){
                $stmt = $PDO->prepare( $sqlDel );
                $execDel = $stmt->execute();
            }
        }
    }
}

//ROTINA DE IMGS 90 DIAS
$sql = "DELETE from tbl_img where date_format(data_hora, '%Y-%m-%d') < SUBDATE(CURDATE(), 90) order by data_hora desc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
}
?>
