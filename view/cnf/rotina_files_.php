<?php
include_once("conn.php");


//ROTINA DE DEL REGISTROS SEM ARQUIVOS

$sql = "SELECT id_file, link_file from tbl_chat_files order by data_hora desc";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoFile = $stmt->fetchAll( PDO::FETCH_ASSOC );
//echo count($infoFile);
    $Y=0;
    for($x=0;$x<count($infoFile);$x++){
        $file = "../".$infoFile[$x]['link_file'];
        if(file_exists($file)){
             //echo "<br>".$file . " - EXISTE";
        } else {
            //$y++;
            $sqlDel_="DELETE FROM tbl_chat_files where id_file=".$infoFile[$x]['id_file'];
            //echo "<br>".$sqlDel_;
            //echo ' - ARQUIVO NÃO EXISTE';
            $stmt = $PDO->prepare( $sqlDel_ );
            $execDel_ = $stmt->execute();
        }
    }


echo "<br><br><br> ---------------------  <br><br><br><br>";

//ROTINA DE ARQUIVO 90 DIAS
$sql = "SELECT * from tbl_chat_files where date_format(data_hora, '%Y-%m-%d') < SUBDATE(CURDATE(), 90) order by data_hora desc";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoFile = $stmt->fetchAll( PDO::FETCH_ASSOC );
echo "<br>".count($infoFile);
if(count($infoFile)>0){
    for($x=0;$x<count($infoFile);$x++){
        $file = "../".$infoFile[$x]['link_file'];
        $sqlDel="DELETE FROM tbl_chat_files where id_file=".$infoFile[$x]['id_file'];
        if(file_exists($file)){
            echo "<br>". $sqlDel;
            if(unlink($file)){
                $stmt = $PDO->prepare( $sqlDel );
                $execDel = $stmt->execute();
            }
        }
    }
}



?>
