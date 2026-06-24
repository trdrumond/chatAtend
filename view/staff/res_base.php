<?php
include("../cnf/session.php");

$sql="TRUNCATE TABLE `web_chatlogos`.`tbl_chat_fila`;";
//echo "<br>".$sql;
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
if($result){
    $sql="TRUNCATE TABLE `web_chatlogos`.`tbl_chat_info`;";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    if($result){
        $sql="TRUNCATE TABLE `web_chatlogos`.`tbl_chat_msg`;";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();
        if($result){
            $sql="TRUNCATE TABLE `web_chatlogos`.`tbl_classificacao`;";
            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
            if($result){
                $sql="TRUNCATE TABLE `web_chatlogos`.`tbl_tma_atend`;";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute();
                if($result){
                    if($result){
                        $sql="TRUNCATE TABLE `web_chatlogos`.`tbl_chat_files`;";
                        //echo "<br>".$sql;
                        $stmt = $PDO->prepare( $sql );
                        $result = $stmt->execute();
                        if($result){
                            $sql="TRUNCATE TABLE `web_chatlogos`.`tbl_log_atendimento`;";
                            //echo "<br>".$sql;
                            $stmt = $PDO->prepare( $sql );
                            $result = $stmt->execute();
                            if($result){
                                echo "<br>Uhuul";
                                echo "<script>Swal.fire('Base resetada com sucesso!')</script>";
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
